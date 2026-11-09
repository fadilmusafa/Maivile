<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Notifikasi dalam aplikasi. Setiap pengguna hanya bisa mengakses notifikasi miliknya sendiri. */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('filter') === 'unread' ? 'unread' : 'all';

        $query = $filter === 'unread' ? $user->unreadNotifications() : $user->notifications();

        return view('notifications.index', [
            'notifications' => $query->paginate(15)->withQueryString(),
            'filter' => $filter,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    /** Tandai dibaca lalu buka detail pengajuan yang terkait. */
    public function open(Request $request, string $notification): RedirectResponse
    {
        $user = $request->user();

        // Dicari lewat relasi milik pengguna: notifikasi orang lain menghasilkan 404.
        $item = $user->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        // Notifikasi pendaftar baru: Admin langsung dibawa ke daftar menunggu persetujuan.
        if (($item->data['type'] ?? null) === 'registration' && $user->isAdmin()) {
            return redirect()->route('admin.users.index', ['role' => 'pending']);
        }

        $transactionId = $item->data['transaction_id'] ?? null;

        if ($transactionId) {
            $exists = Transaction::query()
                ->whereKey($transactionId)
                ->when($user->isStudent(), fn ($q) => $q->where('user_id', $user->id))
                ->exists();

            if ($exists) {
                return redirect()->route(
                    $user->isAdmin() ? 'admin.transactions.show' : 'student.transactions.show',
                    $transactionId
                );
            }
        }

        return redirect()->route('notifications.index');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
