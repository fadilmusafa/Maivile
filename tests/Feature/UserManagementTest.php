<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\ProcessStatus;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function studentPayload(array $override = []): array
    {
        return array_merge([
            'role' => 'student',
            'name' => 'Dina Safitri',
            'nis' => '2030001',
            'class_name' => 'XII-IPA-1',
            'major' => 'IPA',
            'email' => '',
            'phone' => '0812345678',
            'is_active' => 1,
            'password' => '',
        ], $override);
    }

    public function test_student_cannot_access_user_management(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.users.store'), $this->studentPayload())->assertForbidden();
        $this->assertDatabaseMissing('users', ['nis' => '2030001']);
    }

    public function test_admin_creates_student_with_generated_password_that_must_be_changed(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $this->studentPayload())
            ->assertRedirect(route('admin.users.index', ['role' => 'student']))
            ->assertSessionHas('generated_password');

        $created = User::where('nis', '2030001')->firstOrFail();
        $this->assertSame('student', $created->role);
        $this->assertTrue($created->must_change_password);
        $this->assertTrue(Hash::check(session('generated_password'), $created->password));
        $this->assertNotSame(session('generated_password'), $created->password);
    }

    public function test_admin_can_set_manual_initial_password(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $this->studentPayload(['password' => 'Awal12345']))
            ->assertSessionMissing('generated_password');

        $created = User::where('nis', '2030001')->firstOrFail();
        $this->assertTrue(Hash::check('Awal12345', $created->password));
        $this->assertTrue($created->must_change_password);
    }

    public function test_student_requires_nis_and_class_and_nis_must_be_unique(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $this->studentPayload(['nis' => '', 'class_name' => '']))
            ->assertSessionHasErrors(['nis', 'class_name']);

        User::factory()->create(['nis' => '2030001']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $this->studentPayload())
            ->assertSessionHasErrors('nis');
    }

    public function test_admin_account_requires_email(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), ['role' => 'admin', 'name' => 'Petugas Baru', 'email' => '', 'is_active' => 1])
            ->assertSessionHasErrors('email');
    }

    public function test_role_cannot_be_changed_through_update(): void
    {
        $student = User::factory()->create();

        $this->actingAs($this->admin)->put(route('admin.users.update', $student), [
            'name' => 'Nama Baru', 'nis' => $student->nis, 'class_name' => 'XII-IPS-1',
            'is_active' => 1, 'role' => 'admin',
        ])->assertRedirect();

        $student->refresh();
        $this->assertSame('student', $student->role);
        $this->assertSame('Nama Baru', $student->name);
    }

    public function test_admin_can_reset_password_and_user_must_change_it(): void
    {
        $student = User::factory()->create();
        $old = $student->password;

        $this->actingAs($this->admin)
            ->post(route('admin.users.reset-password', $student))
            ->assertSessionHas('generated_password');

        $student->refresh();
        $this->assertNotSame($old, $student->password);
        $this->assertTrue($student->must_change_password);
        $this->assertTrue(Hash::check(session('generated_password'), $student->password));
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $student = User::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.users.toggle-active', $student));
        $this->assertFalse($student->fresh()->is_active);

        $this->actingAs($this->admin)->patch(route('admin.users.toggle-active', $student));
        $this->assertTrue($student->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_or_delete_own_account(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.users.toggle-active', $this->admin))
            ->assertSessionHas('error');
        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin))
            ->assertSessionHas('error');

        $this->assertTrue($this->admin->fresh()->is_active);
        $this->assertNotSoftDeleted($this->admin);
    }

    public function test_user_with_transactions_cannot_be_deleted(): void
    {
        $service = ServiceType::create(['name' => 'Legalisir', 'code' => 'legalisir']);
        $student = User::factory()->create();
        Transaction::forceCreate([
            'transaction_code' => 'MVL-T-0001', 'user_id' => $student->id, 'service_type_id' => $service->id,
            'request_date' => now()->toDateString(),
            'approval_status' => ApprovalStatus::Pending, 'process_status' => ProcessStatus::MenungguVerifikasi,
        ]);

        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $student))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($student);
    }

    public function test_unused_student_can_be_soft_deleted(): void
    {
        $student = User::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.users.destroy', $student))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($student);
    }

    public function test_search_and_status_filter(): void
    {
        User::factory()->create(['name' => 'Zainal Arifin', 'nis' => '2099001']);
        User::factory()->inactive()->create(['name' => 'Yuni Pratiwi', 'nis' => '2099002']);

        $this->actingAs($this->admin)->get(route('admin.users.index', ['q' => 'Zainal']))
            ->assertOk()->assertSee('Zainal Arifin')->assertDontSee('Yuni Pratiwi');

        $this->actingAs($this->admin)->get(route('admin.users.index', ['status' => 'inactive']))
            ->assertOk()->assertSee('Yuni Pratiwi')->assertDontSee('Zainal Arifin');
    }
}
