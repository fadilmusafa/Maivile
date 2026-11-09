<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Sejak Tahap 13, "/" adalah halaman pembuka untuk tamu (sebelumnya diarahkan ke login).
    public function test_guest_sees_landing_page_at_root(): void
    {
        $this->get('/')->assertOk()->assertSee('Maivile');
    }
}
