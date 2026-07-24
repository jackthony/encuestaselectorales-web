<?php

namespace Tests\Unit\Security;

use App\Infrastructure\Persistence\Models\Territory;
use App\Infrastructure\Security\AesGcmVotePrivacy;
use App\Infrastructure\Security\ConfiguredGeographicValidator;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class VoteInfrastructureTest extends TestCase
{
    public function test_vote_privacy_uses_local_fallback_when_runtime_keys_are_missing(): void
    {
        $signals = (new AesGcmVotePrivacy())->protect(
            clientIp: '127.0.0.1',
            deviceToken: '0123456789abcdef0123456789abcdef',
            browserFingerprint: 'Linux|1|1920x1080|24|8|8|America/Lima',
        );

        self::assertNotSame('', $signals->ipCiphertext);
        self::assertSame(12, strlen($signals->ipNonce));
        self::assertSame(16, strlen($signals->ipAuthTag));
        self::assertSame(64, strlen($signals->ipHmac));
        self::assertSame(64, strlen($signals->deviceHmac));
        self::assertSame(64, strlen($signals->browserHmac));
    }

    public function test_geographic_validator_allows_testing_without_bounds_configuration(): void
    {
        $originalMaxAccuracy = config('vote.max_gps_accuracy_meters');
        Config::set('vote.max_gps_accuracy_meters', 100);

        $territory = new Territory([
            'official_code' => '070103',
        ]);

        try {
            $validator = new ConfiguredGeographicValidator();

            self::assertTrue($validator->contains($territory, -12.057222, -77.095833, 18));
            self::assertFalse($validator->contains($territory, -12.057222, -77.095833, 500));
        } finally {
            Config::set('vote.max_gps_accuracy_meters', $originalMaxAccuracy);
        }
    }
}
