<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProcessStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdvanceTransactionRequest;
use App\Http\Requests\ReviewTransactionRequest;
use App\Models\Transaction;
use App\Services\ActivityLogger;
use App\Services\TransactionWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class VerificationController extends Controller
{
    public function __construct(private TransactionWorkflowService $workflow)
    {
    }

    /** Menyetujui, menolak, atau meminta perbaikan pengajuan. */
    public function review(ReviewTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);

        try {
            $t = $this->workflow->review(
                $transaction,
                $request->user(),
                $request->validated('decision'),
                $request->validated('note'),
                $request->validated('planned_date'),
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('transaction_reviewed', $t, "Pengajuan {$t->transaction_code}: {$request->validated('decision')}");

        return redirect()->route('admin.transactions.show', $t)->with('success', 'Verifikasi tersimpan. Status sekarang: '.$t->process_status->label().'.');
    }

    /** Memindahkan tahap proses layanan (sedang diproses, siap diambil, selesai). */
    public function advance(AdvanceTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);

        try {
            $t = $this->workflow->advance(
                $transaction,
                $request->user(),
                ProcessStatus::from($request->validated('status')),
                $request->validated('advance_note'),
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('transaction_advanced', $t, "Pengajuan {$t->transaction_code} menjadi {$t->process_status->value}");

        return redirect()->route('admin.transactions.show', $t)->with('success', 'Tahap diperbarui menjadi: '.$t->process_status->label().'.');
    }
}
