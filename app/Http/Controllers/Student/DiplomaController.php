<?php

namespace App\Http\Controllers\Student;

use App\Enums\ServiceCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\DiplomaRequest;
use App\Models\Transaction;
use App\Services\ActivityLogger;
use App\Services\TransactionWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DiplomaController extends Controller
{
    public function __construct(private TransactionWorkflowService $workflow)
    {
    }

    public function create(Request $request): View
    {
        return view('student.services.diploma', [
            'documents' => $this->workflow->ownedDocuments($request->user(), 'Ijazah')->get(),
        ]);
    }

    public function store(DiplomaRequest $request): RedirectResponse
    {
        try {
            $transaction = $this->workflow->submit(
                $request->user(),
                ServiceCode::PengambilanIjazah,
                $this->payload($request),
                $request->uploads(),
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('transaction_submitted', $transaction, "Pengajuan {$transaction->transaction_code} (pengambilan ijazah) dikirim");

        return redirect()->route('student.transactions.show', $transaction)
            ->with('success', "Jadwal pengambilan ijazah berhasil diajukan dengan kode {$transaction->transaction_code}.");
    }

    /** Siswa memperbaiki pengajuan yang diminta perbaikan oleh petugas, lalu mengirim ulang. */
    public function resubmit(DiplomaRequest $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('view', $transaction);

        try {
            $this->workflow->resubmit($transaction, $request->user(), $this->payload($request), $request->uploads());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('transaction_resubmitted', $transaction, "Pengajuan {$transaction->transaction_code} diperbaiki dan dikirim ulang");

        return redirect()->route('student.transactions.show', $transaction)
            ->with('success', 'Perbaikan berhasil dikirim. Pengajuan Anda kembali menunggu verifikasi petugas.');
    }

    /** @return array<string, mixed> */
    private function payload(DiplomaRequest $request): array
    {
        $data = $request->safe()->except(['attachments', 'power_of_attorney', 'represented']);

        if (! $request->boolean('represented')) {
            $data['receiver_name'] = null;
            $data['receiver_relation'] = null;
        }

        return $data;
    }
}
