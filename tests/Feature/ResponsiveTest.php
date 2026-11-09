<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveTest extends TestCase
{
    use RefreshDatabase;

    private const CARD_VIEWS = [
        'admin/transactions/index',
        'admin/documents/index',
        'student/documents/index',
        'student/transactions/index',
    ];

    private function css(): string
    {
        $path = public_path('css/maivile-responsive.css');
        $this->assertFileExists($path);

        return preg_replace('!/\*.*?\*/!s', '', file_get_contents($path));
    }

    public function test_every_rule_in_responsive_css_is_inside_a_max_width_media_query(): void
    {
        $css = $this->css();
        $depth = 0;
        $buffer = '';
        $topLevel = [];

        foreach (str_split($css) as $ch) {
            if ($ch === '{') {
                if ($depth === 0) {
                    $topLevel[] = trim($buffer);
                    $buffer = '';
                }
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                $this->assertGreaterThanOrEqual(0, $depth, 'Kurung kurawal tidak seimbang.');
            } elseif ($depth === 0) {
                $buffer .= $ch;
            }
        }

        $this->assertSame(0, $depth, 'Kurung kurawal tidak seimbang.');
        $this->assertNotEmpty($topLevel);

        foreach ($topLevel as $selector) {
            $this->assertMatchesRegularExpression('/^@media \(max-width: [\d.]+px\)$/', $selector,
                "Aturan di luar media query layar kecil akan mengubah desktop: {$selector}");
        }
    }

    public function test_responsive_css_has_touch_target_and_ios_zoom_rules(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('min-height: 44px', $css);
        $this->assertStringContainsString('font-size: 16px', $css);
        $this->assertStringContainsString('.table.mv-cards', $css);
    }

    public function test_assets_partial_loads_responsive_css_after_main_and_mobile_css(): void
    {
        $partial = file_get_contents(resource_path('views/partials/assets.blade.php'));

        $main = strpos($partial, 'css/maivile.css');
        $mobile = strpos($partial, 'css/maivile-mobile.css');
        $responsive = strpos($partial, 'css/maivile-responsive.css');

        $this->assertNotFalse($main);
        $this->assertNotFalse($mobile);
        $this->assertNotFalse($responsive);
        $this->assertGreaterThan($mobile, $responsive);
        $this->assertGreaterThan($main, $mobile);
    }

    public function test_card_tables_label_every_cell(): void
    {
        foreach (self::CARD_VIEWS as $view) {
            $src = file_get_contents(resource_path("views/{$view}.blade.php"));

            $this->assertStringContainsString('mv-cards', $src, "{$view}: class mv-cards hilang.");

            preg_match('/<thead>(.*?)<\/thead>/s', $src, $head);
            preg_match('/<tbody>(.*?)<\/tbody>/s', $src, $body);

            $headers = preg_match_all('/<th[\s>]/', $head[1]);
            $cells = preg_match_all('/<td[\s>]/', $body[1]);
            $labelled = preg_match_all('/<td data-label="[^"]+"/', $body[1]);

            $this->assertSame($headers, $cells, "{$view}: jumlah kolom header dan sel berbeda.");
            $this->assertSame($cells, $labelled, "{$view}: ada sel tanpa data-label.");
        }
    }

    public function test_public_pages_declare_viewport_and_load_responsive_css(): void
    {
        foreach (['/', '/login', '/register'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('name="viewport" content="width=device-width, initial-scale=1"', false)
                ->assertSee('css/maivile-responsive.css', false);
        }
    }

    public function test_card_table_pages_still_render_for_each_role(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $this->get(route('admin.transactions.index'))->assertOk();
        $this->get(route('admin.transactions.index', ['tab' => 'all']))->assertOk();
        $this->get(route('admin.documents.index'))->assertOk();

        $this->post('/logout');
        $this->actingAs(User::factory()->create());
        $this->get(route('student.documents.index'))->assertOk();
        $this->get(route('student.transactions.index'))->assertOk();
    }
}
