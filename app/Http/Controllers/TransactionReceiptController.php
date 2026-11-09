<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Bukti pengajuan (PDF). Hanya Admin atau siswa pemilik transaksi
 * (diperiksa lewat TransactionPolicy::view).
 */
class TransactionReceiptController extends Controller
{
    public function __invoke(Transaction $transaction)
    {
        Gate::authorize('view', $transaction);

        $transaction->load([
            'user:id,name,nis,class_name',
            'serviceType:id,name,code',
            'document:id,name',
            'detail',
            'processor:id,name',
            'histories.changer:id,name',
        ]);

        ActivityLogger::log('transaction_receipt_downloaded', $transaction, "Bukti pengajuan {$transaction->transaction_code}");

        return Pdf::loadView('receipts.transaction', [
            'transaction' => $transaction,
            'printedBy' => auth()->user()->name,
            'printedAt' => now(),
        ])->setPaper('a4', 'portrait')
          ->download('bukti-'.Str::slug($transaction->transaction_code).'.pdf');
    }
}
