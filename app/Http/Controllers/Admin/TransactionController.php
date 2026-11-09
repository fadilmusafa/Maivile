<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Services\TransactionWorkflowService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /** Status yang menunggu tindakan petugas (perlu_perbaikan menunggu siswa, jadi tidak termasuk). */
    private const ACTIONABLE = [
        'menunggu_verifikasi', 'disetujui', 'jadwal_dikonfirmasi',
        'sedang_diproses', 'siap_diambil', 'dipinjam', 'dikembalikan', 'diserahkan',
    ];

    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'all' ? 'all' : 'queue';
        $search = trim((string) $request->query('q'));
        $serviceId = $request->integer('service_type_id') ?: null;
        $approval = ApprovalStatus::tryFrom((string) $request->query('approval'));
        $process = ProcessStatus::tryFrom((string) $request->query('process'));
        $from = $this->dateOrNull($request->query('from'));
        $to = $this->dateOrNull($request->query('to'));

        $transactions = Transaction::query()
            ->with(['user:id,name,nis,class_name', 'serviceType:id,name,code'])
            ->when($tab === 'queue', fn ($q) => $q->whereIn('process_status', self::ACTIONABLE))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($w) => $w->where('transaction_code', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('nis', 'like', $like)));
            })
            ->when($serviceId, fn ($q) => $q->where('service_type_id', $serviceId))
            ->when($approval, fn ($q) => $q->where('approval_status', $approval->value))
            ->when($process, fn ($q) => $q->where('process_status', $process->value))
            ->when($from, fn ($q) => $q->whereDate('request_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('request_date', '<=', $to))
            ->orderByRaw("case when process_status = 'menunggu_verifikasi' then 0 else 1 end")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $queueCount = Transaction::whereIn('process_status', self::ACTIONABLE)->count();

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'serviceTypes' => ServiceType::orderBy('name')->get(['id', 'name']),
            'tab' => $tab,
            'search' => $search,
            'serviceId' => $serviceId,
            'approval' => $approval?->value,
            'process' => $process?->value,
            'from' => $from,
            'to' => $to,
            'queueCount' => $queueCount,
        ]);
    }

    public function show(Transaction $transaction, TransactionWorkflowService $workflow): View
    {
        $transaction->load([
            'user', 'serviceType', 'document.category', 'detail', 'processor:id,name',
            'files.uploader:id,name',
            'histories.changer:id,name',
        ]);

        return view('admin.transactions.show', [
            'transaction' => $transaction,
            'next' => collect($workflow->nextStatuses($transaction))->map(fn ($s) => $s->value)->all(),
        ]);
    }

    private function dateOrNull(mixed $value): ?string
    {
        $value = (string) $value;

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }
}
