<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'maivile:create-admin
                            {--name= : Nama lengkap}
                            {--email= : Email (dipakai untuk login)}
                            {--nip= : NIP (opsional, juga bisa dipakai login)}';

    protected $description = 'Buat akun Admin/Petugas (untuk produksi, tanpa seeder demo)';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama lengkap');
        $email = $this->option('email') ?: $this->ask('Email');
        $nip = $this->option('nip') ?? $this->ask('NIP (boleh kosong)');
        $password = $this->secret('Kata sandi');
        $confirm = $this->secret('Ulangi kata sandi');

        $validator = Validator::make(
            [
                'name' => $name, 'email' => $email, 'nip' => $nip ?: null,
                'password' => $password, 'password_confirmation' => $confirm,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'nip' => ['nullable', 'string', 'max:30', 'unique:users,nip'],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            ],
            [],
            ['name' => 'Nama', 'email' => 'Email', 'nip' => 'NIP', 'password' => 'Kata sandi']
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        // role dan status diatur eksplisit (tidak ada di $fillable).
        $user = new User(['name' => $name, 'email' => $email, 'nip' => $nip ?: null, 'password' => $password]);
        $user->role = User::ROLE_ADMIN;
        $user->is_active = true;
        $user->must_change_password = false;
        $user->save();

        $this->info("Admin dibuat: {$user->name} ({$user->email}).");

        return self::SUCCESS;
    }
}
