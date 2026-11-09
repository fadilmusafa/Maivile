<?php

namespace App\Services;

use App\Models\Transaction;

/**
 * Format kode: MVL-YYYYMMDD-0001 (nomor urut per hari).
 * Dipanggil di dalam DB transaction; kolom transaction_code bersifat unik sebagai pengaman terakhir.
 */
class TransactionCodeGenerator
{
    public function next(): string
    {
        $prefix = 'MVL-'.now()->format('Ymd').'-';

        $last = Transaction::withTrashed()
            ->where('transaction_code', 'like', $prefix.'%')
            ->orderByDesc('transaction_code')
            ->value('transaction_code');

        $number = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}
