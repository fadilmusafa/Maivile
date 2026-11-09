<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

/** Notifikasi dalam aplikasi untuk Admin: ada pendaftar baru yang menunggu persetujuan. */
class StudentRegistered extends Notification
{
    public function __construct(private User $student)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'registration',
            'user_id' => $this->student->id,
            'title' => 'Pendaftar baru',
            'message' => "{$this->student->name} (NIS {$this->student->nis}) menunggu persetujuan akun.",
        ];
    }
}
