<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;

/** Notifikasi dalam aplikasi untuk Admin: ada pengajuan baru. */
class TransactionSubmitted extends Notification
{
    public function __construct(private Transaction $transaction)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'transaction_code' => $this->transaction->transaction_code,
            'title' => 'Pengajuan baru',
            'message' => "Pengajuan {$this->transaction->transaction_code} menunggu verifikasi.",
        ];
    }
}
