<?php

namespace Tests\Feature;

use Tests\TestCase;

final class LegacyCallaoRouteRedirectTest extends TestCase
{
    public function test_legacy_callao_region_slug_redirects_to_canonical_url(): void
    {
        $this->get('/encuestas/region/callao')
            ->assertStatus(301)
            ->assertRedirect('/encuestas/region/callao-region');
    }
}
