<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\UserService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserService $users)
    {
    }

    public function index(Request $request): View
    {
        if ($request->query('role') === 'pending') {
            return $this->pendingRegistrations($request);
        }

        $role = $request->query('role') === User::ROLE_ADMIN ? User::ROLE_ADMIN : User::ROLE_STUDENT;
        $search = trim((string) $request->query('q'));
        $status = $request->query('status');

        $users = User::query()
            ->where('role', $role)
            // Pendaftar mandiri (menunggu/ditolak) hanya tampil di tab "Menunggu persetujuan".
            ->when($role === User::ROLE_STUDENT, fn ($q) => $q->where('registration_status', User::REG_APPROVED))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('nis', 'like', $like)
                    ->orWhere('nip', 'like', $like)
                    ->orWhere('email', 'like', $like));
            })
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $counts = $this->counts();

        return view('admin.users.index', compact('users', 'role', 'counts', 'search', 'status'));
    }

    /** Tab "Menunggu persetujuan": pendaftar mandiri (menunggu atau ditolak). */
    private function pendingRegistrations(Request $request): View
    {
        $role = 'pending';
        $search = trim((string) $request->query('q'));
        $reg = $request->query('reg') === User::REG_REJECTED ? User::REG_REJECTED : User::REG_PENDING;

        $users = User::query()
            ->registrants()
            ->where('registration_status', $reg)
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('nis', 'like', $like)
                    ->orWhere('email', 'like', $like));
            })
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        $counts = $this->counts();
        $status = null;

        return view('admin.users.index', compact('users', 'role', 'counts', 'search', 'status', 'reg'));
    }

    private function counts(): array
    {
        return [
            User::ROLE_STUDENT => User::students()->count(),
            User::ROLE_ADMIN => User::admins()->count(),
            'pending' => User::registrants()->where('registration_status', User::REG_PENDING)->count(),
        ];
    }

    public function create(Request $request): View
    {
        $user = new User();
        $user->role = $request->query('role') === User::ROLE_ADMIN ? User::ROLE_ADMIN : User::ROLE_STUDENT;
        $user->is_active = true;

        return view('admin.users.form', compact('user'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        [$user, $generated] = $this->users->create($request->validated(), $request->boolean('is_active'));

        ActivityLogger::log('user_created', $user, "Akun {$user->role} {$user->name} dibuat");

        $redirect = redirect()
            ->route('admin.users.index', ['role' => $user->role])
            ->with('success', "Akun {$user->name} berhasil dibuat. Pemilik wajib mengganti kata sandi saat login pertama.");

        // Kata sandi awal hanya ditampilkan sekali.
        return $generated
            ? $redirect->with('generated_password', $generated)->with('generated_for', $user->name)
            : $redirect;
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        try {
            $this->users->update($user, $request->validated(), $request->boolean('is_active'), $request->user());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLogger::log('user_updated', $user, "Akun {$user->name} diperbarui");

        return redirect()->route('admin.users.index', ['role' => $user->role])
            ->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        try {
            $this->users->delete($user, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLogger::log('user_deleted', $user, "Akun {$user->name} dihapus");

        return redirect()->route('admin.users.index', ['role' => $user->role])
            ->with('success', "Akun {$user->name} berhasil dihapus.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        try {
            $this->users->toggleActive($user, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $state = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLogger::log('user_toggled', $user, "Akun {$user->name} {$state}");

        return back()->with('success', "Akun {$user->name} berhasil {$state}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        try {
            $plain = $this->users->resetPassword($user, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLogger::log('user_password_reset', $user, "Kata sandi {$user->name} direset");

        return back()
            ->with('success', "Kata sandi {$user->name} berhasil direset.")
            ->with('generated_password', $plain)
            ->with('generated_for', $user->name);
    }
}
