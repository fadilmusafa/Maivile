<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PolishTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Aset lokal (tanpa CDN)
    // ------------------------------------------------------------------

    public function test_vendor_files_exist_locally(): void
    {
        foreach ([
            'vendor/bootstrap/css/bootstrap.min.css',
            'vendor/bootstrap/js/bootstrap.bundle.min.js',
            'vendor/bootstrap-icons/bootstrap-icons.min.css',
            'vendor/bootstrap-icons/fonts/bootstrap-icons.woff2',
            'vendor/inter/inter.css',
            'vendor/inter/inter-latin-400-normal.woff2',
            'vendor/inter/inter-latin-800-normal.woff2',
        ] as $file) {
            $this->assertFileExists(public_path($file), $file);
        }
    }

    public function test_login_page_uses_local_assets_only(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringNotContainsString('cdn.jsdelivr.net', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('fonts.gstatic.com', $html);
        $this->assertStringContainsString('vendor/bootstrap/css/bootstrap.min.css', $html);
        $this->assertStringContainsString('vendor/bootstrap/js/bootstrap.bundle.min.js', $html);
    }

    public function test_app_layout_uses_local_assets_only(): void
    {
        $html = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringNotContainsString('cdn.jsdelivr.net', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringContainsString('vendor/bootstrap-icons/bootstrap-icons.min.css', $html);
    }

    // ------------------------------------------------------------------
    // maivile:create-admin
    // ------------------------------------------------------------------

    public function test_create_admin_command_creates_active_admin(): void
    {
        $this->artisan('maivile:create-admin', ['--name' => 'Petugas Baru', '--email' => 'petugas@sekolah.test', '--nip' => '198501012010011002'])
            ->expectsQuestion('Kata sandi', 'Rahasia#1234')
            ->expectsQuestion('Ulangi kata sandi', 'Rahasia#1234')
            ->assertSuccessful();

        $user = User::where('email', 'petugas@sekolah.test')->firstOrFail();
        $this->assertSame('admin', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertSame('198501012010011002', $user->nip);
        $this->assertTrue(Hash::check('Rahasia#1234', $user->password));
    }

    public function test_create_admin_command_rejects_weak_password(): void
    {
        $this->artisan('maivile:create-admin', ['--name' => 'Petugas', '--email' => 'lemah@sekolah.test', '--nip' => ''])
            ->expectsQuestion('Kata sandi', 'abc')
            ->expectsQuestion('Ulangi kata sandi', 'abc')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'lemah@sekolah.test']);
    }

    public function test_create_admin_command_rejects_mismatched_confirmation_and_duplicate_email(): void
    {
        User::factory()->admin()->create(['email' => 'ada@sekolah.test']);

        $this->artisan('maivile:create-admin', ['--name' => 'A', '--email' => 'baru@sekolah.test', '--nip' => ''])
            ->expectsQuestion('Kata sandi', 'Rahasia#1234')
            ->expectsQuestion('Ulangi kata sandi', 'Beda#1234')
            ->assertFailed();

        $this->artisan('maivile:create-admin', ['--name' => 'B', '--email' => 'ada@sekolah.test', '--nip' => ''])
            ->expectsQuestion('Kata sandi', 'Rahasia#1234')
            ->expectsQuestion('Ulangi kata sandi', 'Rahasia#1234')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'baru@sekolah.test']);
        $this->assertSame(1, User::where('email', 'ada@sekolah.test')->count());
    }
}
