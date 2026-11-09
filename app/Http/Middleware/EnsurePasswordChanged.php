<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            return redirect()->route('password.edit')
                ->with('warning', 'Demi keamanan, silakan ganti kata sandi awal Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
