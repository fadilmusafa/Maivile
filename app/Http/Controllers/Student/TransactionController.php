<?php

namespace App\Http\Controllers\Student;

use App\Enums\ApprovalStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->query('q'));
        $serviceId = $request->integer('service_type_id') ?: null;
        $approval = ApprovalStatus::tryFrom((string) $request->query('approval'));
        $process = ProcessStatus::tryFrom((string) $request->query('process'));

        // Selalu dibatasi pada transaksi milik siswa yang sedang login.
        $transactions = Transaction::query()
            ->where('user_id', $user->id)
            ->with(['serviceType:id,name,code'])
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where('transaction_code', 'like', $like);
            })
            ->when($serviceId, fn ($q) => $q->where('service_type_id', $serviceId))
            ->when($approval, fn ($q) => $q->where('approval_status', $approval->value))
            ->when($process, fn ($q) => $q->where('process_status', $process->value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('student.transactions.index', [
            'transactions' => $transactions,
            'serviceTypes' => ServiceType::orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'serviceId' => $serviceId,
            'approval' => $approval?->value,
            'process' => $process?->value,
        ]);
    }

    public function show(Request $request, Transaction $transaction): View
    {
        Gate::authorize('view', $transaction); // 403 bila bukan milik siswa ini

        $transaction->load([
            'serviceType', 'document.category', 'detail', 'processor:id,name',
            'files.uploader:id,name',
            'histories.changer:id,name',
        ]);

        return view('student.transactions.show', compact('transaction'));
    }
}
