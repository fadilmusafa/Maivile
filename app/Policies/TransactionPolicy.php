<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // daftar selalu difilter per pemilik di controller
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin() || $transaction->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    /** Verifikasi, perubahan status, serah-terima: hanya Admin. */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return false; // riwayat tidak dihapus
    }
}
