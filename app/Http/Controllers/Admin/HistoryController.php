<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProcessStatus;
use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\TransactionStatusHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Riwayat perubahan status seluruh transaksi (siapa, kapan, catatan). */
class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $serviceId = $request->integer('service_type_id') ?: null;
        $status = ProcessStatus::tryFrom((string) $request->query('status'));
        $from = $this->dateOrNull($request->query('from'));
        $to = $this->dateOrNull($request->query('to'));

        $histories = TransactionStatusHistory::query()
            ->has('transaction')
            ->with(['transaction.user:id,name,nis', 'transaction.serviceType:id,name,code', 'changer:id,name'])
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->whereHas('transaction', fn ($t) => $t->where(fn ($w) => $w
                    ->where('transaction_code', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('nis', 'like', $like))));
            })
            ->when($serviceId, fn ($q) => $q->whereHas('transaction', fn ($t) => $t->where('service_type_id', $serviceId)))
            ->when($status, fn ($q) => $q->where('new_status', $status->value))
            ->when($from, fn ($q) => $q->whereDate('changed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('changed_at', '<=', $to))
            ->orderByDesc('changed_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.histories.index', [
            'histories' => $histories,
            'serviceTypes' => ServiceType::orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'serviceId' => $serviceId,
            'status' => $status?->value,
            'from' => $from,
            'to' => $to,
        ]);
    }

    private function dateOrNull(mixed $value): ?string
    {
        $value = (string) $value;

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }
}
