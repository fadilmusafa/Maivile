<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandoverRequest;
use App\Http\Requests\ReturnRequest;
use App\Models\Transaction;
use App\Services\ActivityLogger;
use App\Services\TransactionWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class HandoverController extends Controller
{
    public function __construct(private TransactionWorkflowService $workflow)
    {
    }

    /** Mencatat penyerahan dokumen (rapor dipinjam, legalisir atau ijazah diserahkan). */
    public function store(HandoverRequest $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);

        try {
            $t = $this->workflow->recordHandover(
                $transaction,
                $request->user(),
                $request->safe()->except(['proof']),
                $request->file('proof'),
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('transaction_handover', $t, "Penyerahan {$t->transaction_code} dicatat");

        return redirect()->route('admin.transactions.show', $t)->with('success', 'Penyerahan berhasil dicatat. Status sekarang: '.$t->process_status->label().'.');
    }

    /** Mencatat pengembalian rapor. */
    public function returned(ReturnRequest $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);

        try {
            $t = $this->workflow->recordReturn($transaction, $request->user(), $request->validated());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('transaction_returned', $t, "Pengembalian {$t->transaction_code} dicatat");

        return redirect()->route('admin.transactions.show', $t)->with('success', 'Pengembalian rapor berhasil dicatat.');
    }
}
