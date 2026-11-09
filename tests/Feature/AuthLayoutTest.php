<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_share_public_layout_with_back_button(): void
    {
        foreach (['login', 'register'] as $name) {
            $this->get(route($name))->assertOk()
                ->assertSee('mv-auth-side', false)
                ->assertSee('css/maivile-public.css', false)
                ->assertSee('Kembali ke beranda')
                ->assertSee(route('home').'#fitur', false)
                ->assertSee('mv-logo-anim', false);
        }
    }

    public function test_landing_loads_public_css_and_boxed_faq_css_exists(): void
    {
        $this->get('/')->assertOk()->assertSee('css/maivile-public.css', false)->assertSee('mv-logo-anim', false);

        $css = file_get_contents(public_path('css/maivile-public.css'));
        $this->assertStringContainsString('prefers-reduced-motion', $css);
        $this->assertStringContainsString('.mv-lp-accordion', $css);
        $this->assertSame(substr_count($css, '{'), substr_count($css, '}'));
    }

    public function test_forms_still_work_on_new_layout(): void
    {
        $this->get(route('login'))->assertSee('Email atau NIS/NIP')->assertSee('Belum punya akun?');
        $this->get(route('register'))->assertSee('Ulangi kata sandi')->assertSee('Sudah punya akun?');
    }
}
