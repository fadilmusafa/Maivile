<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\StudentRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Dina Pratiwi',
            'nis' => '2025100',
            'class_name' => 'XII-IPA-1',
            'major' => 'IPA',
            'email' => 'dina@example.test',
            'phone' => '081234567890',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ], $override);
    }

    private function pendingUser(array $override = []): User
    {
        return User::factory()->create(array_merge([
            'is_active' => false,
            'registration_status' => 'pending',
        ], $override));
    }

    public function test_register_page_is_displayed(): void
    {
        $this->get('/register')->assertOk()->assertSee('Daftar')->assertSee('Ulangi kata sandi');
    }

    public function test_registration_creates_inactive_pending_student_and_does_not_log_in(): void
    {
        $this->post('/register', $this->payload())->assertRedirect(route('login'))->assertSessionHas('success');

        $user = User::where('nis', '2025100')->firstOrFail();
        $this->assertSame('student', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertSame('pending', $user->registration_status);
        $this->assertTrue(Hash::check('Rahasia123', $user->password));
        $this->assertNotSame('Rahasia123', $user->password);
        $this->assertGuest();
        $this->assertTrue(ActivityLog::where('action', 'registration_submitted')->exists());
    }

    public function test_form_cannot_set_role_or_status(): void
    {
        $this->post('/register', $this->payload([
            'role' => 'admin', 'is_active' => 1, 'registration_status' => 'approved', 'must_change_password' => 1,
        ]))->assertRedirect(route('login'));

        $user = User::where('nis', '2025100')->firstOrFail();
        $this->assertSame('student', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertSame('pending', $user->registration_status);
    }

    public function test_active_admins_are_notified_of_new_registrant(): void
    {
        $admin = User::factory()->admin()->create();
        $inactiveAdmin = User::factory()->admin()->inactive()->create();

        $this->post('/register', $this->payload());

        $this->assertCount(1, $admin->fresh()->notifications);
        $this->assertSame('registration', $admin->notifications->first()->data['type']);
        $this->assertCount(0, $inactiveAdmin->fresh()->notifications);
    }

    public function test_registration_validation_errors(): void
    {
        User::factory()->create(['nis' => '2025100', 'email' => 'dina@example.test']);

        $this->from('/register')->post('/register', $this->payload())
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['nis', 'email']);

        $this->post('/register', $this->payload(['nis' => '2025101', 'email' => 'baru@example.test', 'password' => 'passwordkecil1', 'password_confirmation' => 'passwordkecil1']))
            ->assertSessionHasErrors('password');

        $this->post('/register', $this->payload(['nis' => '2025102', 'email' => 'b2@example.test', 'password_confirmation' => 'Beda12345']))
            ->assertSessionHasErrors('password');

        $this->post('/register', $this->payload(['nis' => '2025103', 'email' => 'b3@example.test', 'phone' => 'abc']))
            ->assertSessionHasErrors('phone');

        $this->post('/register', [])->assertSessionHasErrors(['name', 'nis', 'class_name', 'major', 'email', 'phone', 'password']);

        $this->assertSame(1, User::count());
    }

    public function test_honeypot_blocks_bots_silently(): void
    {
        $this->post('/register', $this->payload(['website' => 'http://spam.example']))
            ->assertRedirect(route('login'));

        $this->assertSame(0, User::count());
    }

    public function test_register_route_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/register', [])->assertStatus(302);
        }

        $this->post('/register', [])->assertStatus(429);
    }

    public function test_pending_user_cannot_login_and_gets_clear_message_only_with_correct_password(): void
    {
        $this->pendingUser(['nis' => '2025200']);

        $this->from('/login')->post('/login', ['login' => '2025200', 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'Pendaftaran Anda masih menunggu persetujuan petugas Tata Usaha. Silakan coba lagi nanti.']);
        $this->assertGuest();

        // Sandi salah: pesan generik, tidak membocorkan status akun.
        $this->post('/login', ['login' => '2025200', 'password' => 'salah'])
            ->assertSessionHasErrors(['login' => 'Email/NIS/NIP atau kata sandi salah.']);
        $this->assertGuest();
    }

    public function test_rejected_user_sees_admin_note_on_login(): void
    {
        $this->pendingUser(['nis' => '2025201', 'registration_status' => 'rejected', 'registration_note' => 'NIS tidak cocok dengan data sekolah.']);

        $this->post('/login', ['login' => '2025201', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertStringContainsString('NIS tidak cocok dengan data sekolah.', session('errors')->first('login'));
        $this->assertGuest();
    }

    public function test_existing_inactive_approved_account_still_gets_inactive_message(): void
    {
        User::factory()->inactive()->create(['nis' => '2025202']);

        $this->post('/login', ['login' => '2025202', 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'Akun Anda tidak aktif. Hubungi petugas sekolah.']);
    }

    public function test_admin_can_approve_and_student_can_then_login(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->pendingUser(['nis' => '2025300']);

        $this->actingAs($admin)
            ->post(route('admin.users.approve-registration', $student))
            ->assertRedirect(route('admin.users.index', ['role' => 'pending']));

        $student->refresh();
        $this->assertSame('approved', $student->registration_status);
        $this->assertTrue($student->is_active);
        $this->assertSame($admin->id, $student->registration_reviewed_by);
        $this->assertTrue(ActivityLog::where('action', 'registration_approved')->exists());

        $this->post('/logout');
        $this->post('/login', ['login' => '2025300', 'password' => 'password'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($student);
    }

    public function test_reject_requires_note_and_keeps_account_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->pendingUser();

        $this->actingAs($admin)->from('/x')
            ->post(route('admin.users.reject-registration', $student), [])
            ->assertSessionHasErrors('note');
        $this->assertSame('pending', $student->fresh()->registration_status);

        $this->post(route('admin.users.reject-registration', $student), ['note' => 'Data tidak valid.'])
            ->assertRedirect(route('admin.users.index', ['role' => 'pending']));

        $student->refresh();
        $this->assertSame('rejected', $student->registration_status);
        $this->assertSame('Data tidak valid.', $student->registration_note);
        $this->assertFalse($student->is_active);
        $this->assertTrue(ActivityLog::where('action', 'registration_rejected')->exists());
    }

    public function test_rejected_registration_can_be_approved_later(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->pendingUser(['registration_status' => 'rejected', 'registration_note' => 'salah']);

        $this->actingAs($admin)->post(route('admin.users.approve-registration', $student));

        $student->refresh();
        $this->assertSame('approved', $student->registration_status);
        $this->assertNull($student->registration_note);
        $this->assertTrue($student->is_active);
    }

    public function test_approve_and_reject_are_admin_only(): void
    {
        $student = $this->pendingUser();
        $other = User::factory()->create();

        $this->actingAs($other)->post(route('admin.users.approve-registration', $student))->assertForbidden();
        $this->post(route('admin.users.reject-registration', $student), ['note' => 'x'])->assertForbidden();
        $this->assertSame('pending', $student->fresh()->registration_status);

        $this->post('/logout');
        $this->post(route('admin.users.approve-registration', $student))->assertRedirect(route('login'));
    }

    public function test_approve_ignores_normal_students_and_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $normal = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.approve-registration', $normal))
            ->assertSessionHas('error');
        $this->post(route('admin.users.approve-registration', User::factory()->admin()->create()))
            ->assertSessionHas('error');
    }

    public function test_pending_tab_lists_registrants_and_students_tab_excludes_them(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = $this->pendingUser(['name' => 'Pendaftar Baru Satu']);
        $rejected = $this->pendingUser(['name' => 'Pendaftar Ditolak Dua', 'registration_status' => 'rejected', 'registration_note' => 'cek ulang']);
        User::factory()->create(['name' => 'Siswa Resmi Tiga']);

        $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'pending']))
            ->assertOk()->assertSee('Pendaftar Baru Satu')->assertSee('Setujui')->assertSee('Tolak')
            ->assertDontSee('Pendaftar Ditolak Dua')->assertDontSee('Siswa Resmi Tiga');

        $this->get(route('admin.users.index', ['role' => 'pending', 'reg' => 'rejected']))
            ->assertOk()->assertSee('Pendaftar Ditolak Dua')->assertSee('cek ulang')->assertDontSee('Pendaftar Baru Satu');

        $this->get(route('admin.users.index'))
            ->assertOk()->assertSee('Siswa Resmi Tiga')
            ->assertDontSee('Pendaftar Baru Satu')->assertDontSee('Pendaftar Ditolak Dua');
    }

    public function test_pending_registrants_are_not_counted_as_students(): void
    {
        $this->pendingUser();
        User::factory()->create();

        $this->assertSame(1, User::students()->count());
        $this->assertSame(1, User::registrants()->count());
    }

    public function test_opening_registration_notification_goes_to_pending_tab(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->pendingUser();
        $admin->notify(new StudentRegistered($student));
        $id = $admin->notifications()->first()->id;

        $this->actingAs($admin)->post(route('notifications.open', $id))
            ->assertRedirect(route('admin.users.index', ['role' => 'pending']));

        $this->assertNotNull($admin->notifications()->first()->read_at);
    }
}
