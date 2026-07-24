<?php

namespace App\Application\Data;

final readonly class PrivacySignals
{
    public function __construct(
        public string $ipCiphertext,
        public string $ipNonce,
        public string $ipAuthTag,
        public int $encryptionKeyVersion,
        public string $ipHmac,
        public int $ipHmacKeyVersion,
        public string $deviceTokenCiphertext,
        public string $deviceTokenNonce,
        public string $deviceTokenAuthTag,
        public string $deviceHmac,
        public int $deviceHmacKeyVersion,
        public string $browserFingerprintCiphertext,
        public string $browserFingerprintNonce,
        public string $browserFingerprintAuthTag,
        public string $browserHmac,
        public int $browserHmacKeyVersion,
    ) {}
}
