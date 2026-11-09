<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(private Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function title(): string
    {
        return 'Laporan Pengajuan';
    }

    public function headings(): array
    {
        return [
            'Kode', 'Tanggal Pengajuan', 'NIS', 'Nama Siswa', 'Kelas', 'Layanan', 'Dokumen',
            'Status Persetujuan', 'Status Proses', 'Tanggal Rencana', 'Rencana Kembali',
            'Tanggal Serah Terima', 'Tanggal Pengembalian', 'Tanggal Selesai',
            'Diproses Oleh', 'Catatan Petugas',
        ];
    }

    /** @param  Transaction  $t */
    public function map($t): array
    {
        $row = [
            $t->transaction_code,
            $t->request_date?->format('d/m/Y'),
            $t->user?->nis,
            $t->user?->name,
            $t->user?->class_name,
            $t->serviceType?->name,
            $t->document?->name,
            $t->approval_status->label(),
            $t->process_status->label(),
            $t->planned_date?->format('d/m/Y'),
            $t->planned_return_date?->format('d/m/Y'),
            $t->actual_handover_date?->format('d/m/Y'),
            $t->actual_return_date?->format('d/m/Y'),
            $t->completed_at?->format('d/m/Y H:i'),
            $t->processor?->name,
            $t->admin_note,
        ];

        return array_map(fn ($v) => self::safe($v), $row);
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    /**
     * Cegah formula injection: sel teks yang diawali = + - @ (atau tab/CR)
     * diberi apostrof agar Excel tidak menjalankannya sebagai rumus.
     */
    public static function safe(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
