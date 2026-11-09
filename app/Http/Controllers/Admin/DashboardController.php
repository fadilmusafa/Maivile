<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboard): View
    {
        $t = Transaction::query()->selectRaw("
            count(*) as total,
            sum(approval_status = 'pending') as pending,
            sum(approval_status = 'approved') as approved,
            sum(approval_status = 'rejected') as rejected,
            sum(approval_status = 'approved' and process_status <> 'selesai') as diproses,
            sum(process_status = 'selesai') as selesai
        ")->first();

        $cards = [
            ['label' => 'Total Siswa', 'value' => User::students()->count(), 'icon' => 'bi-people-fill', 'color' => 'var(--mv-blue)'],
            ['label' => 'Total Dokumen', 'value' => Document::count(), 'icon' => 'bi-folder-fill', 'color' => 'var(--mv-purple)'],
            ['label' => 'Total Transaksi', 'value' => (int) $t->total, 'icon' => 'bi-receipt', 'color' => 'var(--mv-pink)'],
            ['label' => 'Pending', 'value' => (int) $t->pending, 'icon' => 'bi-hourglass-split', 'color' => '#facc15'],
            ['label' => 'Approved', 'value' => (int) $t->approved, 'icon' => 'bi-check-circle-fill', 'color' => 'var(--mv-green)'],
            ['label' => 'Rejected', 'value' => (int) $t->rejected, 'icon' => 'bi-x-circle-fill', 'color' => '#ef4444'],
            ['label' => 'Sedang Diproses', 'value' => (int) $t->diproses, 'icon' => 'bi-gear-fill', 'color' => 'var(--mv-orange)'],
            ['label' => 'Selesai', 'value' => (int) $t->selesai, 'icon' => 'bi-flag-fill', 'color' => '#b3b3b3'],
        ];

        $recent = Transaction::query()
            ->with(['user:id,name,nis', 'serviceType:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'cards' => $cards,
            'recent' => $recent,
            'serviceSummary' => $dashboard->adminServiceSummary(),
            'needsAction' => $dashboard->needsAction(5),
            'needsActionCount' => $dashboard->needsActionCount(),
            'monthly' => $dashboard->monthlyTransactions(6),
        ]);
    }
}
