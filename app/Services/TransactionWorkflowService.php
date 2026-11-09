<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\DocumentAvailability;
use App\Enums\DocumentKind;
use App\Enums\ProcessStatus;
use App\Enums\ServiceCode;
use App\Models\Document;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionFile;
use App\Models\TransactionStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Satu-satunya tempat yang boleh mengubah status transaksi.
 * Setiap operasi berjalan dalam DB transaction dan memakai lockForUpdate()
 * agar dua petugas (atau dua klik) tidak bisa memproses transaksi yang sama bersamaan.
 */
class TransactionWorkflowService
{
    public const MAX_LOAN_DAYS = 7;
    public const MAX_COPIES = 20;
    private const MAX_SUBMIT_ATTEMPTS = 3;

    public const DECISION_APPROVE = 'approve';
    public const DECISION_REJECT = 'reject';
    public const DECISION_REVISE = 'revise';

    /** Transisi process_status yang valid untuk tiap layanan: [dari => [tujuan, ...]] */
    private const TRANSITIONS = [
        'pinjam_rapor' => [
            'menunggu_verifikasi' => ['disetujui', 'ditolak'],
            'disetujui' => ['dipinjam'],
            'dipinjam' => ['dikembalikan'],
            'dikembalikan' => ['selesai'],
        ],
        'legalisir' => [
            'menunggu_verifikasi' => ['disetujui', 'ditolak'],
            'disetujui' => ['sedang_diproses'],
            'sedang_diproses' => ['siap_diambil'],
            'siap_diambil' => ['diserahkan'],
            'diserahkan' => ['selesai'],
        ],
        'pengambilan_ijazah' => [
            'menunggu_verifikasi' => ['jadwal_dikonfirmasi', 'perlu_perbaikan', 'ditolak'],
            'perlu_perbaikan' => ['menunggu_verifikasi'],
            'jadwal_dikonfirmasi' => ['diserahkan'],
            'diserahkan' => ['selesai'],
        ],
    ];

    /** Status yang boleh dipilih lewat tombol "perbarui tahap" (serah terima punya form sendiri). */
    private const ADVANCE_ONLY = ['sedang_diproses', 'siap_diambil', 'selesai'];

    public function __construct(
        private TransactionCodeGenerator $codes,
        private TransactionFileStorage $files,
    ) {
    }

    // ------------------------------------------------------------------
    // Query bantu
    // ------------------------------------------------------------------

    /** Rapor fisik milik siswa yang boleh dipinjam (kategori "Rapor"). */
    public function loanableDocuments(User $student): Builder
    {
        return Document::query()
            ->where('owner_user_id', $student->id)
            ->where('document_kind', DocumentKind::Physical->value)
            ->whereHas('category', fn ($q) => $q->where('name', 'Rapor'))
            ->orderBy('name');
    }

    /** Dokumen milik siswa untuk kategori tertentu (dipakai dropdown form). */
    public function ownedDocuments(User $student, string $categoryName): Builder
    {
        return Document::query()
            ->where('owner_user_id', $student->id)
            ->whereHas('category', fn ($q) => $q->where('name', $categoryName))
            ->orderBy('name');
    }

    /** @return array<int, ProcessStatus> */
    public function nextStatuses(Transaction $t): array
    {
        $t->loadMissing('serviceType');
        $map = self::TRANSITIONS[$t->serviceType->code] ?? [];
        $targets = $map[$t->process_status->value] ?? [];

        return array_map(fn (string $v) => ProcessStatus::from($v), $targets);
    }

    public function canMove(Transaction $t, ProcessStatus $to): bool
    {
        return in_array($to, $this->nextStatuses($t), true);
    }

    // ------------------------------------------------------------------
    // Pengajuan oleh siswa
    // ------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $data     data tervalidasi
     * @param  array<int, array{file: UploadedFile, type: string}>  $uploads
     */
    public function submit(User $student, ServiceCode $service, array $data, array $uploads = []): Transaction
    {
        // Dua pengajuan yang masuk bersamaan bisa mendapat nomor kode yang sama; kolom
        // transaction_code unik akan menolak salah satunya. Ulangi dengan nomor baru.
        for ($attempt = 1; ; $attempt++) {
            try {
                return $this->submitOnce($student, $service, $data, $uploads);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= self::MAX_SUBMIT_ATTEMPTS) {
                    throw $e;
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{file: UploadedFile, type: string}>  $uploads
     */
    private function submitOnce(User $student, ServiceCode $service, array $data, array $uploads = []): Transaction
    {
        $stored = [];

        try {
            $transaction = DB::transaction(function () use ($student, $service, $data, $uploads, &$stored) {
                $serviceType = ServiceType::where('code', $service->value)->where('is_active', true)->first();

                if (! $serviceType) {
                    throw new DomainException('Layanan ini sedang tidak tersedia.');
                }

                $document = null;
                if (! empty($data['document_id'])) {
                    $document = Document::whereKey($data['document_id'])->lockForUpdate()->first();

                    if (! $document || $document->owner_user_id !== $student->id) {
                        throw new DomainException('Dokumen yang dipilih tidak valid.');
                    }
                }

                $this->guardSubmission($student, $service, $document, $data);

                $transaction = new Transaction();
                $transaction->fill([
                    'transaction_code' => $this->codes->next(),
                    'user_id' => $student->id,
                    'service_type_id' => $serviceType->id,
                    'document_id' => $document?->id,
                    'request_date' => now()->toDateString(),
                    'planned_date' => $data['planned_date'] ?? null,
                    'planned_return_date' => $data['planned_return_date'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);
                $transaction->forceFill([
                    'approval_status' => ApprovalStatus::Pending->value,
                    'process_status' => ProcessStatus::MenungguVerifikasi->value,
                ]);
                $transaction->save();

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'legalization_doc_type' => $data['legalization_doc_type'] ?? null,
                    'copies' => isset($data['copies']) ? (int) $data['copies'] : null,
                    'pickup_time_start' => $data['pickup_time_start'] ?? null,
                    'pickup_time_end' => $data['pickup_time_end'] ?? null,
                    'contact_phone' => $data['contact_phone'] ?? null,
                    'receiver_name' => $data['receiver_name'] ?? null,
                    'receiver_relation' => $data['receiver_relation'] ?? null,
                ]);

                $this->attachFiles($transaction, $student, $uploads, $stored);

                $this->writeHistory($transaction, null, ProcessStatus::MenungguVerifikasi, 'Pengajuan dikirim.', $student);

                return $transaction;
            });
        } catch (Throwable $e) {
            $this->files->deletePaths($stored); // jangan tinggalkan file yatim

            throw $e;
        }

        $this->notifyAdmins($transaction);

        return $transaction;
    }

    /**
     * Siswa memperbaiki pengajuan pengambilan ijazah yang berstatus "perlu perbaikan",
     * lalu mengirimnya kembali untuk diverifikasi.
     *
     * @param  array<int, array{file: UploadedFile, type: string}>  $uploads
     */
    public function resubmit(Transaction $transaction, User $student, array $data, array $uploads = []): Transaction
    {
        $stored = [];

        try {
            $fresh = DB::transaction(function () use ($transaction, $student, $data, $uploads, &$stored) {
                $t = $this->lock($transaction->id);

                if ($t->user_id !== $student->id) {
                    throw new DomainException('Pengajuan ini bukan milik Anda.');
                }

                if ($t->serviceType->code !== ServiceCode::PengambilanIjazah->value) {
                    throw new DomainException('Hanya pengajuan pengambilan ijazah yang dapat diperbaiki.');
                }

                $this->assertTransition($t, ProcessStatus::MenungguVerifikasi);

                $t->fill([
                    'planned_date' => $data['planned_date'] ?? $t->planned_date,
                    'description' => $data['description'] ?? $t->description,
                ]);

                TransactionDetail::updateOrCreate(['transaction_id' => $t->id], [
                    'pickup_time_start' => $data['pickup_time_start'] ?? null,
                    'pickup_time_end' => $data['pickup_time_end'] ?? null,
                    'contact_phone' => $data['contact_phone'] ?? null,
                    'receiver_name' => $data['receiver_name'] ?? null,
                    'receiver_relation' => $data['receiver_relation'] ?? null,
                ]);

                $this->attachFiles($t, $student, $uploads, $stored);

                $this->applyTransition($t, ProcessStatus::MenungguVerifikasi, $student, 'Siswa memperbaiki dan mengirim ulang pengajuan.');

                return $t;
            });
        } catch (Throwable $e) {
            $this->files->deletePaths($stored);

            throw $e;
        }

        $this->notifyAdmins($fresh);

        return $fresh;
    }

    // ------------------------------------------------------------------
    // Verifikasi dan tahap proses oleh Admin
    // ------------------------------------------------------------------

    public function review(Transaction $transaction, User $admin, string $decision, ?string $note = null, ?string $plannedDate = null): Transaction
    {
        $this->assertAdmin($admin);

        $fresh = DB::transaction(function () use ($transaction, $admin, $decision, $note, $plannedDate) {
            $t = $this->lock($transaction->id);

            if ($t->process_status !== ProcessStatus::MenungguVerifikasi) {
                throw new DomainException('Pengajuan ini tidak sedang menunggu verifikasi.');
            }

            $isDiploma = $t->serviceType->code === ServiceCode::PengambilanIjazah->value;

            [$to, $approval] = match ($decision) {
                self::DECISION_APPROVE => [
                    $isDiploma ? ProcessStatus::JadwalDikonfirmasi : ProcessStatus::Disetujui,
                    ApprovalStatus::Approved,
                ],
                self::DECISION_REJECT => [ProcessStatus::Ditolak, ApprovalStatus::Rejected],
                self::DECISION_REVISE => [ProcessStatus::PerluPerbaikan, ApprovalStatus::Pending],
                default => throw new DomainException('Keputusan verifikasi tidak dikenal.'),
            };

            if (in_array($decision, [self::DECISION_REJECT, self::DECISION_REVISE], true) && blank($note)) {
                throw new DomainException('Catatan wajib diisi saat menolak atau meminta perbaikan.');
            }

            $this->assertTransition($t, $to);

            $attrs = ['approval_status' => $approval->value];

            if ($decision === self::DECISION_APPROVE && $isDiploma && filled($plannedDate)) {
                $attrs['planned_date'] = Carbon::parse($plannedDate)->toDateString();
            }

            $this->applyTransition($t, $to, $admin, $note, $attrs);

            return $t;
        });

        $this->notifyStudent($fresh, $note);

        return $fresh;
    }

    /** Memindahkan tahap proses: sedang_diproses, siap_diambil, atau selesai. */
    public function advance(Transaction $transaction, User $admin, ProcessStatus $to, ?string $note = null): Transaction
    {
        $this->assertAdmin($admin);

        if (! in_array($to->value, self::ADVANCE_ONLY, true)) {
            throw new DomainException('Tahap ini tidak dapat dipilih langsung. Gunakan form verifikasi atau serah terima.');
        }

        $fresh = DB::transaction(function () use ($transaction, $admin, $to, $note) {
            $t = $this->lock($transaction->id);
            $this->assertTransition($t, $to);
            $this->applyTransition($t, $to, $admin, $note);

            return $t;
        });

        $this->notifyStudent($fresh, $note);

        return $fresh;
    }

    /**
     * Mencatat penyerahan: rapor dipinjam, legalisir diserahkan, atau ijazah diserahkan.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordHandover(Transaction $transaction, User $admin, array $data, ?UploadedFile $proof): Transaction
    {
        $this->assertAdmin($admin);
        $stored = [];

        try {
            $fresh = DB::transaction(function () use ($transaction, $admin, $data, $proof, &$stored) {
                $t = $this->lock($transaction->id);
                $service = ServiceCode::from($t->serviceType->code);
                $to = $service === ServiceCode::PinjamRapor ? ProcessStatus::Dipinjam : ProcessStatus::Diserahkan;

                $this->assertTransition($t, $to);

                if (! $proof) {
                    throw new DomainException('Bukti serah terima wajib diunggah.');
                }

                $date = Carbon::parse($data['handover_date'])->startOfDay();
                if ($date->lt($t->request_date->copy()->startOfDay())) {
                    throw new DomainException('Tanggal penyerahan tidak boleh lebih awal dari tanggal pengajuan.');
                }
                if ($date->gt(now()->startOfDay())) {
                    throw new DomainException('Tanggal penyerahan tidak boleh di masa depan.');
                }

                $isSelf = (bool) ($data['receiver_is_self'] ?? true);
                $receiverName = $isSelf ? $t->user->name : trim((string) ($data['receiver_name'] ?? ''));
                $relation = $isSelf ? 'Siswa sendiri' : trim((string) ($data['receiver_relation'] ?? ''));

                if ($receiverName === '') {
                    throw new DomainException('Nama penerima wajib diisi.');
                }

                if (! $isSelf) {
                    if ($relation === '') {
                        throw new DomainException('Hubungan penerima dengan siswa wajib diisi.');
                    }
                    $hasProxy = $t->files()->where('type', TransactionFile::TYPE_POWER_OF_ATTORNEY)->exists();
                    if (! $hasProxy) {
                        throw new DomainException('Penerima bukan siswa yang bersangkutan, tetapi surat kuasa belum diunggah.');
                    }
                }

                $idNumber = trim((string) ($data['receiver_id_number'] ?? ''));
                if ($service === ServiceCode::PengambilanIjazah && $idNumber === '') {
                    throw new DomainException('Nomor identitas penerima wajib dicatat untuk pengambilan ijazah.');
                }

                // Efek ke dokumen fisik
                if ($service === ServiceCode::PinjamRapor) {
                    $doc = $t->document_id ? Document::whereKey($t->document_id)->lockForUpdate()->first() : null;

                    if (! $doc) {
                        throw new DomainException('Dokumen rapor untuk peminjaman ini tidak ditemukan.');
                    }
                    if ($doc->availability !== DocumentAvailability::Tersedia) {
                        throw new DomainException('Rapor ini sedang dipinjam atau tidak tersedia, penyerahan tidak dapat dicatat.');
                    }

                    $doc->availability = DocumentAvailability::Dipinjam;
                    $doc->save();
                } elseif ($service === ServiceCode::PengambilanIjazah && $t->document_id) {
                    Document::whereKey($t->document_id)->update(['availability' => DocumentAvailability::TidakTersedia->value]);
                }

                $file = $this->files->store($t, $proof, TransactionFile::TYPE_HANDOVER_PROOF, $admin);
                $stored[] = $file->file_path;

                TransactionDetail::updateOrCreate(['transaction_id' => $t->id], [
                    'receiver_name' => $receiverName,
                    'receiver_relation' => $relation,
                    'receiver_id_number' => $idNumber !== '' ? $idNumber : null,
                    'handover_note' => $data['handover_note'] ?? null,
                    'handover_proof_file_id' => $file->id,
                ]);

                $this->applyTransition($t, $to, $admin, $data['handover_note'] ?? null, [
                    'actual_handover_date' => $date->toDateString(),
                ]);

                return $t;
            });
        } catch (Throwable $e) {
            $this->files->deletePaths($stored);

            throw $e;
        }

        $this->notifyStudent($fresh);

        return $fresh;
    }

    /**
     * Mencatat pengembalian rapor.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordReturn(Transaction $transaction, User $admin, array $data): Transaction
    {
        $this->assertAdmin($admin);

        $fresh = DB::transaction(function () use ($transaction, $admin, $data) {
            $t = $this->lock($transaction->id);

            if ($t->serviceType->code !== ServiceCode::PinjamRapor->value) {
                throw new DomainException('Pengembalian hanya berlaku untuk peminjaman rapor.');
            }

            $this->assertTransition($t, ProcessStatus::Dikembalikan);

            $date = Carbon::parse($data['return_date'])->startOfDay();
            if ($t->actual_handover_date && $date->lt($t->actual_handover_date->copy()->startOfDay())) {
                throw new DomainException('Tanggal pengembalian tidak boleh lebih awal dari tanggal peminjaman.');
            }
            if ($date->gt(now()->startOfDay())) {
                throw new DomainException('Tanggal pengembalian tidak boleh di masa depan.');
            }

            if ($t->document_id) {
                Document::whereKey($t->document_id)->update(['availability' => DocumentAvailability::Tersedia->value]);
            }

            TransactionDetail::updateOrCreate(['transaction_id' => $t->id], [
                'return_condition_note' => $data['return_condition_note'] ?? null,
            ]);

            $this->applyTransition($t, ProcessStatus::Dikembalikan, $admin, $data['return_condition_note'] ?? null, [
                'actual_return_date' => $date->toDateString(),
            ]);

            return $t;
        });

        $this->notifyStudent($fresh);

        return $fresh;
    }

    // ------------------------------------------------------------------
    // Internal
    // ------------------------------------------------------------------

    private function lock(int $id): Transaction
    {
        return Transaction::query()
            ->with(['serviceType', 'user', 'detail'])
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function assertAdmin(User $user): void
    {
        if (! $user->isAdmin()) {
            throw new DomainException('Hanya Admin yang dapat melakukan tindakan ini.');
        }
    }

    private function assertTransition(Transaction $t, ProcessStatus $to): void
    {
        if (! $this->canMove($t, $to)) {
            throw new DomainException(sprintf(
                'Perubahan status dari "%s" ke "%s" tidak diizinkan untuk layanan ini.',
                $t->process_status->label(),
                $to->label(),
            ));
        }
    }

    /**
     * Menerapkan perubahan status + mencatat riwayat. Dipanggil di dalam DB transaction.
     *
     * @param  array<string, mixed>  $attrs
     */
    private function applyTransition(Transaction $t, ProcessStatus $to, User $actor, ?string $note = null, array $attrs = []): void
    {
        $from = $t->process_status;

        // Siswa hanya boleh mengirim ulang perbaikan; semua perubahan lain khusus Admin.
        $isStudentMove = $from === ProcessStatus::PerluPerbaikan && $to === ProcessStatus::MenungguVerifikasi;
        if ($actor->isStudent() !== $isStudentMove) {
            throw new DomainException('Anda tidak berhak melakukan perubahan status ini.');
        }

        $this->assertTransition($t, $to);

        $t->forceFill($attrs);
        $t->forceFill(['process_status' => $to->value]);

        if ($actor->isAdmin()) {
            $t->forceFill(['processed_by' => $actor->id]);
            if (filled($note)) {
                $t->forceFill(['admin_note' => $note]);
            }
        }

        if ($to === ProcessStatus::Selesai) {
            $t->forceFill(['completed_at' => now()]);
        }

        $t->save();

        $this->writeHistory($t, $from, $to, $note, $actor);
    }

    private function writeHistory(Transaction $t, ?ProcessStatus $from, ProcessStatus $to, ?string $note, User $actor): void
    {
        TransactionStatusHistory::create([
            'transaction_id' => $t->id,
            'old_status' => $from?->value,
            'new_status' => $to->value,
            'note' => $note,
            'changed_by' => $actor->id,
            'changed_at' => now(),
        ]);
    }

    /**
     * @param  array<int, array{file: UploadedFile, type: string}>  $uploads
     * @param  array<int, string>  $stored  diisi path file yang tersimpan (untuk pembersihan jika gagal)
     */
    private function attachFiles(Transaction $t, User $uploader, array $uploads, array &$stored): void
    {
        foreach ($uploads as $item) {
            $record = $this->files->store($t, $item['file'], $item['type'], $uploader);
            $stored[] = $record->file_path;
        }
    }

    /** Aturan bisnis per layanan yang tidak cukup dicek oleh Form Request. */
    private function guardSubmission(User $student, ServiceCode $service, ?Document $document, array $data): void
    {
        $activeStatuses = [ProcessStatus::Selesai->value, ProcessStatus::Ditolak->value];

        if ($service === ServiceCode::PinjamRapor) {
            if (! $document || ! $this->loanableDocuments($student)->whereKey($document->id)->exists()) {
                throw new DomainException('Pilih rapor fisik milik Anda yang akan dipinjam.');
            }

            if ($document->availability !== DocumentAvailability::Tersedia) {
                throw new DomainException('Rapor ini sedang dipinjam atau tidak tersedia.');
            }

            $hasActive = Transaction::query()
                ->where('document_id', $document->id)
                ->whereHas('serviceType', fn ($q) => $q->where('code', ServiceCode::PinjamRapor->value))
                ->whereNotIn('process_status', $activeStatuses)
                ->exists();

            if ($hasActive) {
                throw new DomainException('Sudah ada pengajuan peminjaman yang masih aktif untuk rapor ini.');
            }

            $start = Carbon::parse($data['planned_date']);
            $end = Carbon::parse($data['planned_return_date']);

            if ($end->lt($start)) {
                throw new DomainException('Tanggal pengembalian tidak boleh lebih awal dari tanggal peminjaman.');
            }
            if ($start->diffInDays($end) > self::MAX_LOAN_DAYS) {
                throw new DomainException('Lama peminjaman maksimal '.self::MAX_LOAN_DAYS.' hari.');
            }
        }

        if ($service === ServiceCode::Legalisir) {
            $copies = (int) ($data['copies'] ?? 0);

            if ($copies < 1 || $copies > self::MAX_COPIES) {
                throw new DomainException('Jumlah salinan harus antara 1 dan '.self::MAX_COPIES.'.');
            }
        }

        if ($service === ServiceCode::PengambilanIjazah) {
            $base = Transaction::query()
                ->where('user_id', $student->id)
                ->whereHas('serviceType', fn ($q) => $q->where('code', ServiceCode::PengambilanIjazah->value));

            if ((clone $base)->whereNotIn('process_status', $activeStatuses)->exists()) {
                throw new DomainException('Anda masih memiliki pengajuan pengambilan ijazah yang aktif.');
            }

            if ((clone $base)->whereIn('process_status', [ProcessStatus::Diserahkan->value, ProcessStatus::Selesai->value])->exists()) {
                throw new DomainException('Ijazah Anda sudah tercatat telah diserahkan.');
            }

            if ($document && $document->availability !== DocumentAvailability::Tersedia) {
                throw new DomainException('Ijazah ini tidak berstatus tersedia untuk diambil.');
            }
        }
    }

    private function notifyAdmins(Transaction $t): void
    {
        try {
            $admins = User::admins()->where('is_active', true)->get();
            Notification::send($admins, new \App\Notifications\TransactionSubmitted($t));
        } catch (Throwable $e) {
            report($e); // notifikasi tidak boleh menggagalkan transaksi
        }
    }

    private function notifyStudent(Transaction $t, ?string $note = null): void
    {
        try {
            $t->user->notify(new \App\Notifications\TransactionStatusChanged($t, $note));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
