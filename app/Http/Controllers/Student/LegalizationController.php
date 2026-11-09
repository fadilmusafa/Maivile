<?php

namespace App\Http\Controllers\Student;

use App\Enums\ServiceCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalizationRequest;
use App\Services\ActivityLogger;
use App\Services\TransactionWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalizationController extends Controller
{
    public function __construct(private TransactionWorkflowService $workflow)
    {
    }

    public function create(Request $request): View
    {
        return view('student.services.legalization', [
            'documents' => $request->user()->documents()->with('category:id,name')->orderBy('name')->get(),
            'docTypes' => LegalizationRequest::DOC_TYPES,
            'maxCopies' => TransactionWorkflowService::MAX_COPIES,
        ]);
    }

    public function store(LegalizationRequest $request): RedirectResponse
    {
        try {
            $transaction = $this->workflow->submit(
                $request->user(),
                ServiceCode::Legalisir,
                $request->safe()->except(['attachments']),
                $request->uploads(),
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('transaction_submitted', $transaction, "Pengajuan {$transaction->transaction_code} (legalisir) dikirim");

        return redirect()->route('student.transactions.show', $transaction)
            ->with('success', "Pengajuan legalisir berhasil dikirim dengan kode {$transaction->transaction_code}.");
    }
}
