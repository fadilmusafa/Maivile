<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_has_facts_strip_and_closing_cta(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Maksimal peminjaman rapor')
            ->assertSee('Salinan legalisir per pengajuan')
            ->assertSee('Ukuran maksimal tiap berkas')
            ->assertSee('Siap mengurus dokumen akademik Anda?')
            ->assertSee('Layanan Tata Usaha online');
    }

    public function test_landing_polish_css_adds_rules_without_removing_old_ones(): void
    {
        $css = file_get_contents(public_path('css/maivile-landing.css'));

        foreach (['.mv-lp-hero', '.mv-lp-feature', '.mv-lp-footer', '.mv-lp-pill', '.mv-lp-facts', '.mv-lp-cta'] as $selector) {
            $this->assertStringContainsString($selector, $css);
        }
        $this->assertSame(substr_count($css, '{'), substr_count($css, '}'));
    }
}
