<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class VoteJavascriptContractTest extends TestCase
{
    #[DataProvider('laravelVoteContractTokens')]
    public function test_public_vote_script_uses_laravel_contract(string $token): void
    {
        $script = file_get_contents(public_path('assets/js/voto-gps.js'));

        $this->assertIsString($script);
        $this->assertStringContainsString($token, $script);
        $this->assertStringNotContainsString("fetch('api/votar.php", $script);
    }

    /** @return array<string, array{string}> */
    public static function laravelVoteContractTokens(): array
    {
        return [
            'endpoint' => ["fetch('/api/votes'"],
            'round' => ['survey_round_id'],
            'option' => ['survey_option_id'],
            'latitude' => ['gps_latitude'],
            'longitude' => ['gps_longitude'],
        ];
    }
}
