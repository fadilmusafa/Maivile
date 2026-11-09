<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Tamu melihat halaman pembuka; pengguna yang sudah login diarahkan ke dashboard sesuai role. */
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return view('landing.index');
        }

        return redirect()->route(
            $user->isAdmin() ? 'admin.dashboard' : 'student.dashboard'
        );
    }
}
