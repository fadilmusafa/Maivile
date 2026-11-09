<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_all_landing_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Daftar Sekarang')
            ->assertSee('id="fitur"', false)
            ->assertSee('id="panduan"', false)
            ->assertSee('id="faq"', false)
            ->assertSee('Peminjaman Rapor')
            ->assertSee('Legalisir Dokumen')
            ->assertSee('Pengambilan Ijazah')
            ->assertSee('Hanya 4 langkah')
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false);
    }

    public function test_faq_states_the_real_service_rules(): void
    {
        $this->get('/')
            ->assertSee('Paling lama 7 hari')
            ->assertSee('Satu sampai 20 salinan')
            ->assertSee('2 MB')
            ->assertSee('surat kuasa')
            ->assertSee('Belum. Persetujuan hanya berarti pengajuan diterima');
    }

    public function test_landing_uses_local_assets_only(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('cdn.jsdelivr.net', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringContainsString('css/maivile-landing.css', $html);
    }

    public function test_footer_shows_placeholders_until_school_data_is_configured(): void
    {
        // Kosongkan data sekolah dari .env supaya test tidak bergantung pada isi .env lokal.
        config([
            'maivile.school_name' => '[NAMA SEKOLAH]',
            'maivile.school_address' => '[ALAMAT SEKOLAH]',
            'maivile.service_hours' => '[JAM LAYANAN]',
            'maivile.contact' => '[KONTAK]',
        ]);

        $this->get('/')->assertSee('[ALAMAT SEKOLAH]')->assertSee('[JAM LAYANAN]')->assertSee('[KONTAK]');

        config(['maivile.school_name' => 'SMA Contoh Nusantara']);
        $this->get('/')->assertSee('SMA Contoh Nusantara');
    }

    public function test_logged_in_users_are_redirected_from_root_to_their_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/')->assertRedirect(route('admin.dashboard'));

        $this->actingAs(User::factory()->create())
            ->get('/')->assertRedirect(route('student.dashboard'));
    }

    public function test_login_page_links_to_register_and_home(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee(route('register'), false)
            ->assertSee('Belum punya akun?');
    }

    public function test_logged_in_user_cannot_open_register_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/register')->assertRedirect('/');
    }
}
