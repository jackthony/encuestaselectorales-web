<?php

namespace Tests\Feature;

use Tests\TestCase;

final class ApiHealthTest extends TestCase
{
    public function test_api_routes_are_registered(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }
}
