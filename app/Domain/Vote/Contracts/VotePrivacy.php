<?php

namespace App\Domain\Vote\Contracts;

use App\Application\Data\PrivacySignals;

interface VotePrivacy
{
    public function protect(
        string $clientIp,
        string $deviceToken,
        string $browserFingerprint,
    ): PrivacySignals;
}
