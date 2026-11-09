<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_update_phone_but_not_name_or_role(): void
    {
        $student = User::factory()->create(['name' => 'Nama Asli', 'phone' => '0811111111']);

        $this->actingAs($student)->put(route('profile.update'), [
            'name' => 'Nama Diubah',
            'role' => 'admin',
            'is_active' => 0,
            'phone' => '0822222222',
        ])->assertRedirect(route('profile.edit'));

        $student->refresh();
        $this->assertSame('0822222222', $student->phone);
        $this->assertSame('Nama Asli', $student->name);
        $this->assertSame('student', $student->role);
        $this->assertTrue($student->is_active);
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('profile.update'), ['phone' => 'abc<script>'])
            ->assertSessionHasErrors('phone');
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'bukan-password',
            'password' => 'Baru12345',
            'password_confirmation' => 'Baru12345',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_change_requires_confirmation_letters_and_digits(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'abcdefgh',
            'password_confirmation' => 'abcdefgh',
        ])->assertSessionHasErrors('password');

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'Baru12345',
            'password_confirmation' => 'Beda12345',
        ])->assertSessionHasErrors('password');
    }

    public function test_password_change_succeeds_and_clears_forced_change_flag(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'Baru12345',
            'password_confirmation' => 'Baru12345',
        ])->assertRedirect(route('home'));

        $user->refresh();
        $this->assertTrue(Hash::check('Baru12345', $user->password));
        $this->assertFalse($user->must_change_password);
        $this->assertNotSame('Baru12345', $user->password);
    }
}
