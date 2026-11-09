<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Notifications\StudentRegistered;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $done = redirect()->route('login')->with(
            'success',
            'Pendaftaran terkirim. Akun Anda aktif setelah disetujui petugas Tata Usaha. Coba masuk nanti untuk melihat statusnya.'
        );

        // Honeypot: bot mengisi kolom tersembunyi. Pura-pura berhasil, tanpa membuat akun.
        if (filled($request->input('website'))) {
            return $done;
        }

        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = new User();
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'nis' => $data['nis'],
                'class_name' => $data['class_name'],
                'major' => $data['major'],
                'phone' => $data['phone'],
            ]);
            // Dikunci oleh server, tidak pernah dari form.
            $user->forceFill([
                'role' => User::ROLE_STUDENT,
                'password' => $data['password'], // di-hash oleh cast 'hashed'
                'is_active' => false,
                'must_change_password' => false,
                'registration_status' => User::REG_PENDING,
            ])->save();

            return $user;
        });

        ActivityLogger::log('registration_submitted', $user, "Pendaftaran akun siswa {$user->name} (NIS {$user->nis})");

        Notification::send(
            User::admins()->where('is_active', true)->get(),
            new StudentRegistered($user)
        );

        return $done;
    }
}
