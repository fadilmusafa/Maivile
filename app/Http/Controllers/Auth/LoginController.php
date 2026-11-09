<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $login = trim($request->validated('login'));
        $throttleKey = Str::lower($login).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Login boleh memakai email, NIS (siswa), atau NIP (admin).
        $user = User::query()
            ->where(fn ($q) => $q->where('email', $login)
                ->orWhere('nis', $login)
                ->orWhere('nip', $login))
            ->first();

        // Pesan sama untuk akun tidak ada / password salah agar tidak membocorkan data.
        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'login' => 'Email/NIS/NIP atau kata sandi salah.',
            ]);
        }

        // Status pendaftaran hanya diberitahukan setelah kata sandi benar.
        if ($user->registration_status === User::REG_PENDING) {
            throw ValidationException::withMessages([
                'login' => 'Pendaftaran Anda masih menunggu persetujuan petugas Tata Usaha. Silakan coba lagi nanti.',
            ]);
        }

        if ($user->registration_status === User::REG_REJECTED) {
            $note = $user->registration_note ? ' Catatan petugas: '.$user->registration_note : '';

            throw ValidationException::withMessages([
                'login' => 'Pendaftaran Anda ditolak.'.$note.' Hubungi petugas Tata Usaha untuk bantuan.',
            ]);
        }

        if (! $user->is_active) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'login' => 'Akun Anda tidak aktif. Hubungi petugas sekolah.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate(); // cegah session fixation

        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        ActivityLogger::log('login', $user, 'Login berhasil');

        if ($user->must_change_password) {
            return redirect()->route('password.edit')
                ->with('warning', 'Demi keamanan, silakan ganti kata sandi awal Anda.');
        }

        return redirect()->route('home');
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLogger::log('logout', $request->user(), 'Logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }
}
