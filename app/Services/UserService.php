<?php

namespace App\Services;

use App\Models\User;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserService
{
    /** Kata sandi acak yang pasti memuat huruf dan angka. */
    public function generatePassword(): string
    {
        return Str::random(8).random_int(10, 99).chr(random_int(65, 90));
    }

    /**
     * @return array{0: User, 1: string|null} [user, kata sandi awal bila dibuat otomatis]
     */
    public function create(array $data, bool $isActive): array
    {
        $generated = null;
        $plain = $data['password'] ?? null;

        if (blank($plain)) {
            $plain = $generated = $this->generatePassword();
        }

        $user = new User();
        $user->fill(Arr::only($data, ['name', 'email', 'nis', 'nip', 'class_name', 'major', 'phone']));
        $user->forceFill([
            'role' => $data['role'],
            'password' => $plain,            // di-hash oleh cast 'hashed'
            'is_active' => $isActive,
            'must_change_password' => true,  // password awal wajib diganti pemilik akun
        ])->save();

        return [$user, $generated];
    }

    public function update(User $user, array $data, bool $isActive, User $actor): User
    {
        $user->fill(Arr::only($data, ['name', 'email', 'nis', 'nip', 'class_name', 'major', 'phone']));

        if ($isActive !== $user->is_active) {
            if (! $isActive) {
                $this->assertCanDeactivate($user, $actor);
            }
            $user->is_active = $isActive;
        }

        $user->save();

        return $user;
    }

    public function resetPassword(User $user, User $actor): string
    {
        if ($user->id === $actor->id) {
            throw new DomainException('Untuk mengganti kata sandi Anda sendiri, gunakan menu Profil.');
        }

        $plain = $this->generatePassword();
        $user->forceFill(['password' => $plain, 'must_change_password' => true])->save();

        return $plain;
    }

    public function toggleActive(User $user, User $actor): User
    {
        if ($user->is_active) {
            $this->assertCanDeactivate($user, $actor);
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return $user;
    }

    public function delete(User $user, User $actor): void
    {
        $this->assertCanDeactivate($user, $actor, 'dihapus');

        if ($user->transactions()->withTrashed()->exists() || $user->documents()->withTrashed()->exists()) {
            throw new DomainException('Pengguna ini sudah memiliki transaksi atau dokumen sehingga tidak dapat dihapus. Nonaktifkan akunnya saja.');
        }

        DB::transaction(fn () => $user->delete()); // soft delete
    }

    private function assertCanDeactivate(User $user, User $actor, string $verb = 'dinonaktifkan'): void
    {
        if ($user->id === $actor->id) {
            throw new DomainException("Akun Anda sendiri tidak dapat {$verb}.");
        }

        if ($user->isAdmin() && $user->is_active
            && User::admins()->where('is_active', true)->count() <= 1) {
            throw new DomainException("Admin aktif terakhir tidak dapat {$verb}.");
        }
    }
}
