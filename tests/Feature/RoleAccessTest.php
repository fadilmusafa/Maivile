<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\ProcessStatus;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_protected_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_student_cannot_open_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_cannot_open_student_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('student.dashboard'))
            ->assertForbidden();
    }

    public function test_each_role_can_open_its_own_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))->assertOk()->assertSee('Halo,');

        $this->actingAs(User::factory()->create())
            ->get(route('student.dashboard'))->assertOk();
    }

    public function test_inactive_user_with_existing_session_is_logged_out(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_student_dashboard_only_shows_own_transactions(): void
    {
        $service = ServiceType::create(['name' => 'Legalisir Dokumen', 'code' => 'legalisir']);
        $alya = User::factory()->create();
        $bima = User::factory()->create();

        $this->makeTransaction($alya, $service, 'MVL-TEST-ALYA');
        $this->makeTransaction($bima, $service, 'MVL-TEST-BIMA');

        $this->actingAs($alya)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('MVL-TEST-ALYA')
            ->assertDontSee('MVL-TEST-BIMA');
    }

    public function test_admin_dashboard_lists_transactions_from_database(): void
    {
        $service = ServiceType::create(['name' => 'Legalisir Dokumen', 'code' => 'legalisir']);
        $student = User::factory()->create();
        $this->makeTransaction($student, $service, 'MVL-TEST-0001');
        $this->makeTransaction($student, $service, 'MVL-TEST-0002');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('MVL-TEST-0001')
            ->assertSee('MVL-TEST-0002');
    }

    public function test_student_cannot_set_status_through_mass_assignment(): void
    {
        $service = ServiceType::create(['name' => 'Legalisir Dokumen', 'code' => 'legalisir']);
        $student = User::factory()->create();

        $t = new Transaction();
        $t->fill([
            'transaction_code' => 'MVL-TEST-MASS',
            'user_id' => $student->id,
            'service_type_id' => $service->id,
            'request_date' => now()->toDateString(),
            'approval_status' => 'approved',   // harus diabaikan
            'process_status' => 'selesai',     // harus diabaikan
        ]);
        $t->save();

        $t->refresh();
        $this->assertSame(ApprovalStatus::Pending, $t->approval_status);
        $this->assertSame(ProcessStatus::MenungguVerifikasi, $t->process_status);
    }

    private function makeTransaction(User $user, ServiceType $service, string $code): Transaction
    {
        return Transaction::forceCreate([
            'transaction_code' => $code,
            'user_id' => $user->id,
            'service_type_id' => $service->id,
            'request_date' => now()->toDateString(),
            'approval_status' => ApprovalStatus::Pending,
            'process_status' => ProcessStatus::MenungguVerifikasi,
        ]);
    }
}
