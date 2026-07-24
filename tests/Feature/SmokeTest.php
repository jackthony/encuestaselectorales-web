<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Acceso directo')
            ->assertSee('Abre una encuesta')
            ->assertSee('Ver detalle');
    }

    public function test_health_endpoint_loads(): void
    {
        $this->get('/up')->assertOk();
    }
}
