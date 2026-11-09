<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Cast 'hashed' pada model User otomatis melakukan hashing.
        $user->forceFill([
            'password' => $request->validated('password'),
            'must_change_password' => false,
        ])->save();

        $request->session()->regenerate();
        ActivityLogger::log('password_changed', $user, 'Kata sandi diganti');

        return redirect()->route('home')->with('success', 'Kata sandi berhasil diganti.');
    }
}
