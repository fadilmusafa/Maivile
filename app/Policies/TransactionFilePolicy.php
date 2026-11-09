<?php

namespace App\Policies;

use App\Models\TransactionFile;
use App\Models\User;

class TransactionFilePolicy
{
    public function download(User $user, TransactionFile $file): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $file->loadMissing('transaction:id,user_id');

        return $file->transaction?->user_id === $user->id;
    }
}
