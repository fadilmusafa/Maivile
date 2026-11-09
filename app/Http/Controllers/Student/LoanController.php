<?php

namespace App\Http\Controllers\Student;

use App\Enums\ServiceCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequest;
use App\Services\ActivityLogger;
use App\Services\TransactionWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(private TransactionWorkflowService $workflow)
    {
    }

    public function create(Request $request): View
    {
        return view('student.services.loan', [
            'documents' => $this->workflow->loanableDocuments($request->user())->with('category:id,name')->get(),
            'maxDays' => TransactionWorkflowService::MAX_LOAN_DAYS,
        ]);
    }

    public function store(LoanRequest $request): RedirectResponse
    {
        try {
            $transaction = $this->workflow->submit(
                $request->user(),
                ServiceCode::PinjamRapor,
                $request->safe()->except(['attachments']),
                $request->uploads(),
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('transaction_submitted', $transaction, "Pengajuan {$transaction->transaction_code} (peminjaman rapor) dikirim");

        return redirect()->route('student.transactions.show', $transaction)
            ->with('success', "Pengajuan peminjaman rapor berhasil dikirim dengan kode {$transaction->transaction_code}.");
    }
}
