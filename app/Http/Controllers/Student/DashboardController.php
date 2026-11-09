<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): View
    {
        $user = $request->user();

        // Semua query dibatasi pada transaksi milik siswa yang sedang login.
        $t = $user->transactions()->selectRaw("
            count(*) as total,
            sum(approval_status = 'pending') as pending,
            sum(approval_status = 'approved') as approved,
            sum(approval_status = 'rejected') as rejected,
            sum(process_status = 'selesai') as selesai
        ")->first();

        $cards = [
            ['label' => 'Total Pengajuan', 'value' => (int) $t->total, 'icon' => 'bi-receipt', 'color' => 'var(--mv-blue)'],
            ['label' => 'Menunggu', 'value' => (int) $t->pending, 'icon' => 'bi-hourglass-split', 'color' => '#facc15'],
            ['label' => 'Disetujui', 'value' => (int) $t->approved, 'icon' => 'bi-check-circle-fill', 'color' => 'var(--mv-green)'],
            ['label' => 'Ditolak', 'value' => (int) $t->rejected, 'icon' => 'bi-x-circle-fill', 'color' => '#ef4444'],
            ['label' => 'Selesai', 'value' => (int) $t->selesai, 'icon' => 'bi-flag-fill', 'color' => '#b3b3b3'],
        ];

        $recent = $user->transactions()
            ->with('serviceType:id,name')
            ->latest()
            ->limit(5)
            ->get();

        return view('student.dashboard', [
            'user' => $user,
            'cards' => $cards,
            'recent' => $recent,
            'serviceSummary' => $dashboard->studentServiceSummary($user),
            'latestNotes' => $dashboard->studentLatestNotes($user, 3),
            'notifications' => $user->notifications()->limit(5)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
