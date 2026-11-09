<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\ProcessStatus;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /** Batas baris untuk cetak dan PDF agar memori aman. Excel tidak dibatasi (dibaca bertahap). */
    public const DOCUMENT_LIMIT = 1000;

    /** Query dasar tanpa eager loading dan tanpa urutan (dipakai untuk ringkasan). */
    public function baseQuery(array $f): Builder
    {
        return Transaction::query()
            ->when($f['from'] ?? null, fn ($q, $v) => $q->whereDate('request_date', '>=', $v))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->whereDate('request_date', '<=', $v))
            ->when($f['service_type_id'] ?? null, fn ($q, $v) => $q->where('service_type_id', $v))
            ->when($f['approval'] ?? null, fn ($q, $v) => $q->where('approval_status', $v))
            ->when($f['process'] ?? null, fn ($q, $v) => $q->where('process_status', $v));
    }

    /** Query daftar lengkap dengan relasi dan urutan tetap (terbaru dulu). */
    public function listQuery(array $f): Builder
    {
        return $this->baseQuery($f)
            ->with([
                'user:id,name,nis,class_name',
                'serviceType:id,name,code',
                'document:id,name',
                'processor:id,name',
            ])
            ->orderByDesc('request_date')
            ->orderByDesc('id');
    }

    /**
     * Ringkasan: total, rekap per layanan (pending/approved/rejected/selesai),
     * dan rekap per status proses.
     */
    public function summary(array $f): array
    {
        $rows = $this->baseQuery($f)->toBase()
            ->select('service_type_id', 'approval_status', 'process_status')
            ->selectRaw('count(*) as total')
            ->groupBy('service_type_id', 'approval_status', 'process_status')
            ->get();

        $services = ServiceType::orderBy('name')->pluck('name', 'id');

        $byService = [];
        foreach ($services as $id => $name) {
            $byService[$id] = [
                'name' => $name, 'total' => 0,
                'pending' => 0, 'approved' => 0, 'rejected' => 0, 'selesai' => 0,
            ];
        }

        $byProcess = [];
        $total = 0;

        foreach ($rows as $row) {
            $n = (int) $row->total;
            $total += $n;

            if (isset($byService[$row->service_type_id])) {
                $byService[$row->service_type_id]['total'] += $n;
                if (isset($byService[$row->service_type_id][$row->approval_status])) {
                    $byService[$row->service_type_id][$row->approval_status] += $n;
                }
                if ($row->process_status === ProcessStatus::Selesai->value) {
                    $byService[$row->service_type_id]['selesai'] += $n;
                }
            }

            $byProcess[$row->process_status] = ($byProcess[$row->process_status] ?? 0) + $n;
        }

        // Urutkan status proses sesuai urutan enum, lewati yang nol.
        $processRows = [];
        foreach (ProcessStatus::cases() as $case) {
            if (! empty($byProcess[$case->value])) {
                $processRows[] = ['label' => $case->label(), 'total' => $byProcess[$case->value]];
            }
        }

        return [
            'total' => $total,
            'by_service' => array_values($byService),
            'by_process' => $processRows,
        ];
    }

    /** Teks ringkas filter yang aktif, untuk kop laporan cetak/PDF. */
    public function describeFilters(array $f): array
    {
        $period = 'Semua periode';
        if (! empty($f['from']) || ! empty($f['to'])) {
            $from = ! empty($f['from']) ? \Carbon\Carbon::parse($f['from'])->translatedFormat('d M Y') : 'awal';
            $to = ! empty($f['to']) ? \Carbon\Carbon::parse($f['to'])->translatedFormat('d M Y') : 'sekarang';
            $period = "{$from} s.d. {$to}";
        }

        return [
            'Periode' => $period,
            'Layanan' => ! empty($f['service_type_id'])
                ? (ServiceType::whereKey($f['service_type_id'])->value('name') ?? '-')
                : 'Semua layanan',
            'Persetujuan' => ! empty($f['approval']) ? ApprovalStatus::from($f['approval'])->label() : 'Semua',
            'Proses' => ! empty($f['process']) ? ProcessStatus::from($f['process'])->label() : 'Semua',
        ];
    }

    /** Hanya filter yang terisi, untuk dipakai sebagai query string tautan. */
    public function queryString(array $f): array
    {
        return array_filter($f, fn ($v) => $v !== null && $v !== '');
    }
}
