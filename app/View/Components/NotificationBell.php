<?php

namespace App\View\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

/** Ikon lonceng di header: jumlah belum dibaca dan 5 notifikasi terbaru milik pengguna yang login. */
class NotificationBell extends Component
{
    public int $unread = 0;

    public Collection $latest;

    public function __construct()
    {
        $user = auth()->user();

        $this->latest = collect();

        if ($user) {
            $this->unread = $user->unreadNotifications()->count();
            $this->latest = $user->notifications()->limit(5)->get();
        }
    }

    public function render(): View
    {
        return view('components.notification-bell');
    }
}
