<?php

namespace App\Services;

use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Data tambahan untuk dashboard Admin dan Siswa (ringkasan per layanan, grafik, daftar tindakan).
 * Hanya membaca data; tidak mengubah status apa pun.
 */
class DashboardService
{
    /**
     * Status yang menunggu tindakan petugas.
     * Sama dengan daftar di Admin\TransactionController (antrean); ada test yang menjaga keduanya tetap sama.
     */
    public const ACTIONABLE = [
        'menunggu_verifikasi', 'disetujui', 'jadwal_dikonfirmasi',
        'sedang_diproses', 'siap_diambil', 'dipinjam', 'dikembalikan', 'diserahkan',
    ];

    /** Tampilan per layanan (warna sesuai spesifikasi UI). */
    public const SERVICES = [
        'pinjam_rapor' => [
            'color' => 'var(--mv-green)', 'text' => '#000', 'icon' => 'bi-journal-bookmark-fill',
            'route' => 'student.loan.create', 'cta' => 'Pinjam Rapor',
        ],
        'legalisir' => [
            'color' => 'var(--mv-purple)', 'text' => '#fff', 'icon' => 'bi-patch-check-fill',
            'route' => 'student.legalization.create', 'cta' => 'Ajukan Legalisir',
        ],
        'pengambilan_ijazah' => [
            'color' => 'var(--mv-orange)', 'text' => '#000', 'icon' => 'bi-mortarboard-fill',
            'route' => 'student.diploma.create', 'cta' => 'Ambil Ijazah',
        ],
    ];

    private const FALLBACK = [
        'color' => 'var(--mv-blue)', 'text' => '#fff', 'icon' => 'bi-file-earmark-text-fill',
        'route' => null, 'cta' => 'Ajukan',
    ];

    public function style(string $code): array
    {
        return self::SERVICES[$code] ?? self::FALLBACK;
    }

    /** Ringkasan seluruh transaksi per jenis layanan (Admin). */
    public function adminServiceSummary(): Collection
    {
        $rows = Transaction::query()
            ->selectRaw("service_type_id,
                count(*) as total,
                sum(process_status = 'menunggu_verifikasi') as waiting,
                sum(approval_status = 'approved' and process_status <> 'selesai') as active,
                sum(process_status = 'selesai') as done")
            ->groupBy('service_type_id')
            ->get()
            ->keyBy('service_type_id');

        return ServiceType::query()->orderBy('id')->get()->map(function (ServiceType $s) use ($rows) {
            $r = $rows->get($s->id);

            return [
                'service' => $s,
                'style' => $this->style($s->code),
                'total' => (int) ($r->total ?? 0),
                'waiting' => (int) ($r->waiting ?? 0),
                'active' => (int) ($r->active ?? 0),
                'done' => (int) ($r->done ?? 0),
            ];
        });
    }

    /** Pengajuan yang paling lama menunggu tindakan petugas. */
    public function needsAction(int $limit = 5): Collection
    {
        return Transaction::query()
            ->with(['user:id,name,nis', 'serviceType:id,name,code'])
            ->whereIn('process_status', self::ACTIONABLE)
            ->orderByRaw("case when process_status = 'menunggu_verifikasi' then 0 else 1 end")
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    public function needsActionCount(): int
    {
        return Transaction::whereIn('process_status', self::ACTIONABLE)->count();
    }

    /**
     * Jumlah pengajuan per bulan (berdasarkan tanggal pengajuan), $months bulan terakhir.
     * Pengelompokan dilakukan di PHP supaya sama di MySQL dan SQLite.
     *
     * @return array<int, array{label: string, count: int, pct: int}>
     */
    public function monthlyTransactions(int $months = 6, ?User $student = null): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $dates = Transaction::query()
            ->when($student, fn ($q) => $q->where('user_id', $student->id))
            ->where('request_date', '>=', $start->toDateString())
            ->pluck('request_date');

        $counts = [];
        foreach ($dates as $d) {
            $key = Carbon::parse($d)->format('Y-m');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $series = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $series[] = [
                'label' => $m->translatedFormat('M Y'),
                'count' => $counts[$m->format('Y-m')] ?? 0,
            ];
        }

        $max = max(array_column($series, 'count')) ?: 0;

        return array_map(function (array $row) use ($max) {
            $row['pct'] = $max > 0 && $row['count'] > 0 ? max(6, (int) round($row['count'] / $max * 100)) : 0;

            return $row;
        }, $series);
    }

    /** Ringkasan transaksi milik satu siswa per layanan, dipakai kartu layanan di dashboard siswa. */
    public function studentServiceSummary(User $student): Collection
    {
        $rows = Transaction::query()
            ->where('user_id', $student->id)
            ->selectRaw("service_type_id,
                count(*) as total,
                sum(process_status not in ('selesai', 'ditolak')) as active")
            ->groupBy('service_type_id')
            ->get()
            ->keyBy('service_type_id');

        return ServiceType::query()->orderBy('id')->get()->map(function (ServiceType $s) use ($rows) {
            $r = $rows->get($s->id);

            return [
                'service' => $s,
                'style' => $this->style($s->code),
                'total' => (int) ($r->total ?? 0),
                'active' => (int) ($r->active ?? 0),
            ];
        });
    }

    /** Catatan petugas terbaru pada pengajuan milik siswa. */
    public function studentLatestNotes(User $student, int $limit = 3): Collection
    {
        return Transaction::query()
            ->where('user_id', $student->id)
            ->whereNotNull('admin_note')
            ->where('admin_note', '<>', '')
            ->with('serviceType:id,name,code')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }
}
