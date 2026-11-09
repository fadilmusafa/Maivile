<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;

/** Notifikasi dalam aplikasi untuk Siswa: status pengajuan berubah. */
class TransactionStatusChanged extends Notification
{
    public function __construct(private Transaction $transaction, private ?string $note = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->transaction->process_status->label();

        return [
            'transaction_id' => $this->transaction->id,
            'transaction_code' => $this->transaction->transaction_code,
            'title' => 'Status pengajuan diperbarui',
            'message' => "Pengajuan {$this->transaction->transaction_code}: {$status}.",
            'note' => $this->note,
        ];
    }
}
