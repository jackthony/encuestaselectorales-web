<?php

namespace App\Domain\Vote\Contracts;

use App\Application\Data\PrivacySignals;

// ponytail: Domain contract returns an Application-layer DTO — invert only if Domain needs to
// consume this without the Application layer loaded.
interface VotePrivacy
{
    public function protect(
        string $clientIp,
        string $deviceToken,
        string $browserFingerprint,
    ): PrivacySignals;
}
