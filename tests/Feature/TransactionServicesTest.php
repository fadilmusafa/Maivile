<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\DocumentAvailability;
use App\Enums\ProcessStatus;
use App\Enums\ServiceCode;
use App\Models\Category;
use App\Models\Document;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\TransactionFile;
use App\Models\User;
use App\Services\TransactionWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionServicesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private User $other;
    private Document $rapor;
    private Document $ijazah;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        foreach ([
            ['pinjam_rapor', 'Peminjaman Rapor'],
            ['legalisir', 'Legalisir Dokumen'],
            ['pengambilan_ijazah', 'Pengambilan Ijazah'],
        ] as [$code, $name]) {
            ServiceType::create(['code' => $code, 'name' => $name, 'is_active' => true]);
        }

        $catRapor = Category::create(['name' => 'Rapor', 'type' => 'document']);
        $catIjazah = Category::create(['name' => 'Ijazah', 'type' => 'document']);

        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->create(['name' => 'Alya Siswa']);
        $this->other = User::factory()->create(['name' => 'Bima Lain']);

        $this->rapor = Document::create([
            'category_id' => $catRapor->id, 'owner_user_id' => $this->student->id, 'name' => 'Rapor Semester 5',
            'document_kind' => 'physical', 'availability' => 'tersedia',
        ]);
        $this->ijazah = Document::create([
            'category_id' => $catIjazah->id, 'owner_user_id' => $this->student->id, 'name' => 'Ijazah Asli',
            'document_kind' => 'physical', 'availability' => 'tersedia',
        ]);
    }

    // ---------- helper ----------

    private function pdf(string $name = 'berkas.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 50, 'application/pdf');
    }

    private function weekday(int $minDaysAhead = 2): string
    {
        $d = now()->addDays($minDaysAhead);
        while ($d->isWeekend()) {
            $d = $d->addDay();
        }

        return $d->toDateString();
    }

    private function loanPayload(array $override = []): array
    {
        return array_merge([
            'document_id' => $this->rapor->id,
            'planned_date' => now()->addDay()->toDateString(),
            'planned_return_date' => now()->addDays(4)->toDateString(),
            'description' => 'Mengurus beasiswa',
        ], $override);
    }

    private function submitLoan(): Transaction
    {
        return app(TransactionWorkflowService::class)->submit($this->student, ServiceCode::PinjamRapor, $this->loanPayload());
    }

    private function submitLegalization(): Transaction
    {
        return app(TransactionWorkflowService::class)->submit($this->student, ServiceCode::Legalisir, [
            'legalization_doc_type' => 'Ijazah', 'copies' => 3, 'description' => 'Melamar kerja',
        ]);
    }

    private function submitDiploma(array $extra = []): Transaction
    {
        return app(TransactionWorkflowService::class)->submit($this->student, ServiceCode::PengambilanIjazah, array_merge([
            'planned_date' => $this->weekday(), 'contact_phone' => '081234567890',
        ], $extra));
    }

    private function review(Transaction $t, string $decision, ?string $note = null, array $extra = [])
    {
        return $this->actingAs($this->admin)->post(route('admin.transactions.review', $t), array_merge([
            'decision' => $decision, 'note' => $note,
        ], $extra));
    }

    // ---------- Peminjaman rapor ----------

    public function test_student_submits_loan_and_it_starts_pending(): void
    {
        $response = $this->actingAs($this->student)->post(route('student.loan.store'), $this->loanPayload([
            'attachments' => [$this->pdf('surat.pdf')],
        ]));

        $t = Transaction::firstOrFail();
        $response->assertRedirect(route('student.transactions.show', $t))->assertSessionHasNoErrors();

        $this->assertMatchesRegularExpression('/^MVL-\d{8}-\d{4}$/', $t->transaction_code);
        $this->assertSame(ApprovalStatus::Pending, $t->approval_status);
        $this->assertSame(ProcessStatus::MenungguVerifikasi, $t->process_status);
        $this->assertSame($this->student->id, $t->user_id);
        $this->assertDatabaseCount('transaction_files', 1);
        $this->assertDatabaseHas('transaction_status_histories', ['transaction_id' => $t->id, 'new_status' => 'menunggu_verifikasi']);

        $file = TransactionFile::firstOrFail();
        $this->assertStringStartsWith('transaction-files/', $file->file_path);
        $this->assertStringNotContainsString('surat', $file->file_path);
        Storage::disk('local')->assertExists($file->file_path);
    }

    public function test_student_cannot_set_status_through_the_form(): void
    {
        $this->actingAs($this->student)->post(route('student.loan.store'), $this->loanPayload([
            'approval_status' => 'approved', 'process_status' => 'selesai', 'user_id' => $this->other->id,
        ]));

        $t = Transaction::firstOrFail();
        $this->assertSame(ApprovalStatus::Pending, $t->approval_status);
        $this->assertSame(ProcessStatus::MenungguVerifikasi, $t->process_status);
        $this->assertSame($this->student->id, $t->user_id);
    }

    public function test_loan_validates_dates_and_document_ownership(): void
    {
        $this->actingAs($this->student)->post(route('student.loan.store'), $this->loanPayload([
            'planned_return_date' => now()->toDateString(),
        ]))->assertSessionHasErrors('planned_return_date');

        $this->actingAs($this->student)->post(route('student.loan.store'), $this->loanPayload([
            'planned_return_date' => now()->addDays(20)->toDateString(),
        ]))->assertSessionHasErrors('planned_return_date');

        $this->actingAs($this->other)->post(route('student.loan.store'), $this->loanPayload())
            ->assertSessionHasErrors('document_id');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_same_report_cannot_be_requested_twice_while_active(): void
    {
        $this->submitLoan();

        $this->actingAs($this->student)->post(route('student.loan.store'), $this->loanPayload())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_loan_full_flow_updates_document_availability(): void
    {
        $t = $this->submitLoan();

        $this->review($t, 'approve', 'Silakan diambil.')->assertSessionHasNoErrors();
        $t->refresh();
        $this->assertSame(ApprovalStatus::Approved, $t->approval_status);
        $this->assertSame(ProcessStatus::Disetujui, $t->process_status);
        $this->assertSame($this->admin->id, $t->processed_by);

        // Persetujuan saja tidak membuat rapor dipinjam
        $this->assertSame(DocumentAvailability::Tersedia, $this->rapor->fresh()->availability);

        // Bukti serah terima wajib
        $this->actingAs($this->admin)->post(route('admin.transactions.handover', $t), [
            'handover_date' => now()->toDateString(), 'receiver_is_self' => '1',
        ])->assertSessionHasErrors('proof');

        $this->actingAs($this->admin)->post(route('admin.transactions.handover', $t), [
            'handover_date' => now()->toDateString(), 'receiver_is_self' => '1', 'proof' => $this->pdf('tanda-terima.pdf'),
        ])->assertSessionHasNoErrors();

        $t->refresh();
        $this->assertSame(ProcessStatus::Dipinjam, $t->process_status);
        $this->assertNotNull($t->actual_handover_date);
        $this->assertSame(DocumentAvailability::Dipinjam, $this->rapor->fresh()->availability);
        $this->assertNotNull($t->detail->handover_proof_file_id);

        // Tidak bisa dilompati ke selesai
        $this->actingAs($this->admin)->post(route('admin.transactions.advance', $t), ['status' => 'selesai'])
            ->assertSessionHas('error');
        $this->assertSame(ProcessStatus::Dipinjam, $t->fresh()->process_status);

        // Pengembalian
        $this->actingAs($this->admin)->post(route('admin.transactions.return', $t), [
            'return_date' => now()->toDateString(), 'return_condition_note' => 'Baik',
        ])->assertSessionHasNoErrors();

        $t->refresh();
        $this->assertSame(ProcessStatus::Dikembalikan, $t->process_status);
        $this->assertNotNull($t->actual_return_date);
        $this->assertSame(DocumentAvailability::Tersedia, $this->rapor->fresh()->availability);

        $this->actingAs($this->admin)->post(route('admin.transactions.advance', $t), ['status' => 'selesai'])
            ->assertSessionHasNoErrors();

        $t->refresh();
        $this->assertSame(ProcessStatus::Selesai, $t->process_status);
        $this->assertNotNull($t->completed_at);
        $this->assertSame(5, $t->histories()->count()); // diajukan, disetujui, dipinjam, dikembalikan, selesai
    }

    public function test_return_date_cannot_be_before_handover_date(): void
    {
        $t = $this->submitLoan();
        $this->review($t, 'approve');
        $this->actingAs($this->admin)->post(route('admin.transactions.handover', $t), [
            'handover_date' => now()->toDateString(), 'receiver_is_self' => '1', 'proof' => $this->pdf(),
        ]);

        $this->actingAs($this->admin)->post(route('admin.transactions.return', $t), [
            'return_date' => now()->subDays(2)->toDateString(),
        ])->assertSessionHas('error');

        $this->assertSame(ProcessStatus::Dipinjam, $t->fresh()->process_status);
    }

    // ---------- Legalisir ----------

    public function test_legalization_requires_attachment_and_positive_integer_copies(): void
    {
        $payload = ['legalization_doc_type' => 'Ijazah', 'copies' => 2, 'description' => 'Melamar kerja'];

        $this->actingAs($this->student)->post(route('student.legalization.store'), $payload)
            ->assertSessionHasErrors('attachments');

        foreach ([0, -1, 2.5, 'abc', 21] as $bad) {
            $this->actingAs($this->student)->post(route('student.legalization.store'), array_merge($payload, [
                'copies' => $bad, 'attachments' => [$this->pdf()],
            ]))->assertSessionHasErrors('copies');
        }

        $this->assertDatabaseCount('transactions', 0);

        $this->actingAs($this->student)->post(route('student.legalization.store'), array_merge($payload, [
            'attachments' => [$this->pdf()],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(2, Transaction::firstOrFail()->detail->copies);
    }

    public function test_legalization_approval_does_not_mean_finished(): void
    {
        $t = $this->submitLegalization();
        $this->review($t, 'approve');

        $this->assertNotSame(ProcessStatus::Selesai, $t->fresh()->process_status);
        $this->assertNull($t->fresh()->completed_at);

        // Tidak boleh melompat
        foreach (['selesai', 'siap_diambil'] as $skip) {
            $this->actingAs($this->admin)->post(route('admin.transactions.advance', $t), ['status' => $skip])
                ->assertSessionHas('error');
        }
        $this->assertSame(ProcessStatus::Disetujui, $t->fresh()->process_status);

        $this->actingAs($this->admin)->post(route('admin.transactions.advance', $t), ['status' => 'sedang_diproses']);
        $this->actingAs($this->admin)->post(route('admin.transactions.advance', $t), ['status' => 'siap_diambil']);
        $this->actingAs($this->admin)->post(route('admin.transactions.handover', $t), [
            'handover_date' => now()->toDateString(), 'receiver_is_self' => '1', 'proof' => $this->pdf(),
        ])->assertSessionHasNoErrors();
        $this->assertSame(ProcessStatus::Diserahkan, $t->fresh()->process_status);

        $this->actingAs($this->admin)->post(route('admin.transactions.advance', $t), ['status' => 'selesai']);
        $this->assertSame(ProcessStatus::Selesai, $t->fresh()->process_status);
    }

    // ---------- Pengambilan ijazah ----------

    public function test_diploma_schedule_must_be_a_future_weekday(): void
    {
        $saturday = now()->next('Saturday')->toDateString();

        $this->actingAs($this->student)->post(route('student.diploma.store'), [
            'planned_date' => $saturday, 'contact_phone' => '081234567890',
        ])->assertSessionHasErrors('planned_date');

        $this->actingAs($this->student)->post(route('student.diploma.store'), [
            'planned_date' => now()->subDay()->toDateString(), 'contact_phone' => '081234567890',
        ])->assertSessionHasErrors('planned_date');

        $this->actingAs($this->student)->post(route('student.diploma.store'), [
            'planned_date' => $this->weekday(), 'contact_phone' => '081234567890',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_diploma_by_proxy_requires_power_of_attorney(): void
    {
        $payload = [
            'planned_date' => $this->weekday(), 'contact_phone' => '081234567890',
            'represented' => '1', 'receiver_name' => 'Ibu Alya', 'receiver_relation' => 'Ibu',
        ];

        $this->actingAs($this->student)->post(route('student.diploma.store'), $payload)
            ->assertSessionHasErrors('power_of_attorney');

        $this->actingAs($this->student)->post(route('student.diploma.store'), array_merge($payload, [
            'power_of_attorney' => $this->pdf('kuasa.pdf'),
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('transaction_files', ['type' => 'power_of_attorney']);
    }

    public function test_only_one_active_diploma_request_per_student(): void
    {
        $this->submitDiploma();

        $this->actingAs($this->student)->post(route('student.diploma.store'), [
            'planned_date' => $this->weekday(3), 'contact_phone' => '081234567890',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_reject_and_revise_require_a_note(): void
    {
        $t = $this->submitDiploma();

        $this->review($t, 'reject', null)->assertSessionHasErrors('note');
        $this->review($t, 'revise', '')->assertSessionHasErrors('note');
        $this->assertSame(ProcessStatus::MenungguVerifikasi, $t->fresh()->process_status);

        $this->review($t, 'reject', 'Data tidak sesuai.')->assertSessionHasNoErrors();
        $t->refresh();
        $this->assertSame(ApprovalStatus::Rejected, $t->approval_status);
        $this->assertSame(ProcessStatus::Ditolak, $t->process_status);
        $this->assertSame('Data tidak sesuai.', $t->admin_note);
    }

    public function test_revision_cycle_then_handover_closes_the_pickup(): void
    {
        $t = $this->submitDiploma();

        $this->review($t, 'revise', 'Lengkapi kontak.')->assertSessionHasNoErrors();
        $t->refresh();
        $this->assertSame(ProcessStatus::PerluPerbaikan, $t->process_status);
        $this->assertSame(ApprovalStatus::Pending, $t->approval_status);

        // Admin tidak bisa memverifikasi sebelum siswa mengirim ulang
        $this->review($t, 'approve')->assertSessionHas('error');

        // Siswa lain tidak boleh memperbaiki
        $this->actingAs($this->other)->put(route('student.diploma.resubmit', $t), [
            'planned_date' => $this->weekday(), 'contact_phone' => '081234567890',
        ])->assertForbidden();

        $this->actingAs($this->student)->put(route('student.diploma.resubmit', $t), [
            'planned_date' => $this->weekday(3), 'contact_phone' => '081299998888',
        ])->assertSessionHasNoErrors();
        $this->assertSame(ProcessStatus::MenungguVerifikasi, $t->fresh()->process_status);

        $this->review($t, 'approve', 'Jadwal dikonfirmasi.')->assertSessionHasNoErrors();
        $t->refresh();
        $this->assertSame(ProcessStatus::JadwalDikonfirmasi, $t->process_status);
        $this->assertSame(ApprovalStatus::Approved, $t->approval_status);

        // Belum diserahkan hanya karena jadwal disetujui
        $this->assertNull($t->actual_handover_date);
        $this->assertSame(DocumentAvailability::Tersedia, $this->ijazah->fresh()->availability);

        // Nomor identitas wajib untuk ijazah
        $this->actingAs($this->admin)->post(route('admin.transactions.handover', $t), [
            'handover_date' => now()->toDateString(), 'receiver_is_self' => '1', 'proof' => $this->pdf(),
        ])->assertSessionHas('error');
        $this->assertSame(ProcessStatus::JadwalDikonfirmasi, $t->fresh()->process_status);

        $this->actingAs($this->admin)->post(route('admin.transactions.handover', $t), [
            'handover_date' => now()->toDateString(), 'receiver_is_self' => '1',
            'receiver_id_number' => '3300112233445566', 'proof' => $this->pdf('tanda-terima.pdf'),
        ])->assertSessionHasNoErrors();

        $t->refresh();
        $this->assertSame(ProcessStatus::Diserahkan, $t->process_status);
        $this->assertNotNull($t->actual_handover_date);

        $this->actingAs($this->admin)->post(route('admin.transactions.advance', $t), ['status' => 'selesai'])
            ->assertSessionHasNoErrors();
        $this->assertSame(ProcessStatus::Selesai, $t->fresh()->process_status);
    }

    public function test_handover_to_someone_else_requires_power_of_attorney_on_file(): void
    {
        $t = $this->submitDiploma();
        $this->review($t, 'approve');

        $this->actingAs($this->admin)->post(route('admin.transactions.handover', $t), [
            'handover_date' => now()->toDateString(), 'receiver_is_self' => '0',
            'receiver_name' => 'Pak Budi', 'receiver_relation' => 'Paman',
            'receiver_id_number' => '3300112233445566', 'proof' => $this->pdf(),
        ])->assertSessionHas('error');

        $this->assertSame(ProcessStatus::JadwalDikonfirmasi, $t->fresh()->process_status);
        $this->assertDatabaseMissing('transaction_files', ['type' => 'handover_proof']);
    }

    // ---------- Hak akses ----------

    public function test_student_cannot_open_or_resubmit_other_students_transactions(): void
    {
        $t = $this->submitLoan();

        $this->actingAs($this->other)->get(route('student.transactions.show', $t))->assertForbidden();
        $this->actingAs($this->student)->get(route('student.transactions.show', $t))->assertOk();
    }

    public function test_student_list_only_contains_own_transactions(): void
    {
        $mine = $this->submitLoan();
        $theirs = app(TransactionWorkflowService::class)->submit($this->other, ServiceCode::Legalisir, [
            'legalization_doc_type' => 'Rapor', 'copies' => 1, 'description' => 'Daftar kuliah',
        ]);

        $this->actingAs($this->student)->get(route('student.transactions.index'))
            ->assertOk()->assertSee($mine->transaction_code)->assertDontSee($theirs->transaction_code);
    }

    public function test_student_cannot_reach_admin_workflow_endpoints(): void
    {
        $t = $this->submitLoan();

        $this->actingAs($this->student)->get(route('admin.transactions.index'))->assertForbidden();
        $this->actingAs($this->student)->get(route('admin.transactions.show', $t))->assertForbidden();
        $this->actingAs($this->student)->post(route('admin.transactions.review', $t), ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($this->student)->post(route('admin.transactions.advance', $t), ['status' => 'selesai'])->assertForbidden();
        $this->actingAs($this->student)->post(route('admin.transactions.handover', $t), [])->assertForbidden();
        $this->actingAs($this->student)->post(route('admin.transactions.return', $t), [])->assertForbidden();

        $this->assertSame(ProcessStatus::MenungguVerifikasi, $t->fresh()->process_status);
    }

    public function test_admin_cannot_use_student_service_forms(): void
    {
        $this->actingAs($this->admin)->get(route('student.loan.create'))->assertForbidden();
        $this->actingAs($this->admin)->post(route('student.loan.store'), $this->loanPayload())->assertForbidden();
    }

    public function test_transaction_files_are_private_and_authorized(): void
    {
        $this->actingAs($this->student)->post(route('student.loan.store'), $this->loanPayload([
            'attachments' => [$this->pdf('rahasia.pdf')],
        ]));
        $file = TransactionFile::firstOrFail();

        $this->actingAs($this->student)->get(route('transaction-files.download', $file))->assertOk();
        $this->actingAs($this->admin)->get(route('transaction-files.download', $file))->assertOk();
        $this->actingAs($this->other)->get(route('transaction-files.download', $file))->assertForbidden();
    }

    // ---------- Antrean & notifikasi ----------

    public function test_admin_queue_filters_by_service_and_search(): void
    {
        $loan = $this->submitLoan();
        $legal = $this->submitLegalization();
        $serviceId = ServiceType::where('code', 'legalisir')->value('id');

        $this->actingAs($this->admin)->get(route('admin.transactions.index', ['service_type_id' => $serviceId]))
            ->assertOk()->assertSee($legal->transaction_code);

        // Dicek lewat data tabel, bukan seluruh HTML: lonceng notifikasi di header juga memuat kode pengajuan.
        $codes = $this->actingAs($this->admin)
            ->get(route('admin.transactions.index', ['service_type_id' => $serviceId]))
            ->viewData('transactions')->pluck('transaction_code')->all();
        $this->assertSame([$legal->transaction_code], $codes);

        $this->actingAs($this->admin)->get(route('admin.transactions.index', ['q' => $this->student->nis, 'tab' => 'all']))
            ->assertOk()->assertSee($loan->transaction_code);

        $this->actingAs($this->admin)->get(route('admin.transactions.show', $loan))
            ->assertOk()->assertSee($loan->transaction_code);
    }

    public function test_status_changes_create_in_app_notifications(): void
    {
        $t = $this->submitLoan();
        $this->assertSame(1, $this->admin->notifications()->count()); // pengajuan baru

        $this->review($t, 'approve', 'Disetujui.');
        $this->assertSame(1, $this->student->notifications()->count());
    }

    public function test_transaction_codes_are_unique_and_sequential(): void
    {
        $a = $this->submitLegalization();
        $b = $this->submitLegalization();

        $this->assertNotSame($a->transaction_code, $b->transaction_code);
        $this->assertSame(
            (int) substr($a->transaction_code, -4) + 1,
            (int) substr($b->transaction_code, -4),
        );
    }
}
