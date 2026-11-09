<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileCssTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_stylesheet_exists_and_is_scoped_to_small_screens(): void
    {
        $path = public_path('css/maivile-mobile.css');

        $this->assertFileExists($path);
        $css = file_get_contents($path);
        $this->assertStringContainsString('@media (max-width: 767.98px)', $css);
        // Semua aturan harus berada di dalam media query (tidak menyentuh desktop).
        $this->assertSame(1, substr_count($css, '@media'));
    }

    public function test_pages_link_the_mobile_stylesheet(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('css/maivile-mobile.css');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))->assertOk()->assertSee('css/maivile-mobile.css');
    }
}
