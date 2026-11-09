<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Persetujuan pendaftaran akun siswa (hanya Admin; dibatasi middleware role:admin). */
class RegistrationController extends Controller
{
    public function approve(Request $request, User $user): RedirectResponse
    {
        if (! $this->isRegistrant($user)) {
            return back()->with('error', 'Akun ini bukan pendaftar yang menunggu keputusan.');
        }

        $user->forceFill([
            'registration_status' => User::REG_APPROVED,
            'registration_note' => null,
            'registration_reviewed_at' => now(),
            'registration_reviewed_by' => $request->user()->id,
            'is_active' => true,
        ])->save();

        ActivityLogger::log('registration_approved', $user, "Pendaftaran {$user->name} disetujui");

        return redirect()->route('admin.users.index', ['role' => 'pending'])
            ->with('success', "Akun {$user->name} disetujui. Siswa sudah bisa masuk.");
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(
            ['note' => ['required', 'string', 'max:500']],
            ['note.required' => 'Catatan wajib diisi saat menolak pendaftaran.', 'note.max' => 'Catatan maksimal 500 karakter.']
        );

        if (! $this->isRegistrant($user)) {
            return back()->with('error', 'Akun ini bukan pendaftar yang menunggu keputusan.');
        }

        $user->forceFill([
            'registration_status' => User::REG_REJECTED,
            'registration_note' => $data['note'],
            'registration_reviewed_at' => now(),
            'registration_reviewed_by' => $request->user()->id,
            'is_active' => false,
        ])->save();

        ActivityLogger::log('registration_rejected', $user, "Pendaftaran {$user->name} ditolak");

        return redirect()->route('admin.users.index', ['role' => 'pending'])
            ->with('success', "Pendaftaran {$user->name} ditolak.");
    }

    private function isRegistrant(User $user): bool
    {
        return $user->role === User::ROLE_STUDENT
            && in_array($user->registration_status, [User::REG_PENDING, User::REG_REJECTED], true);
    }
}
