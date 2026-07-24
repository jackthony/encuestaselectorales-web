<?php

namespace App\Infrastructure\Security;

use App\Application\Data\PrivacySignals;
use App\Domain\Vote\Contracts\VotePrivacy;
use RuntimeException;

final class AesGcmVotePrivacy implements VotePrivacy
{
    public function protect(
        string $clientIp,
        string $deviceToken,
        string $browserFingerprint,
    ): PrivacySignals {
        $ip = filter_var(trim($clientIp), FILTER_VALIDATE_IP);
        if ($ip === false) {
            throw new RuntimeException('The server could not resolve a valid client IP.');
        }

        $encryptionKey = $this->key('vote.encryption_key', 32, exact: true);
        $ipHmacKey = $this->key('vote.ip_hmac_key', 32);
        $deviceHmacKey = $this->key('vote.device_hmac_key', 32);
        $version = max(1, (int) config('vote.encryption_key_version', 1));
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $ip,
            'aes-256-gcm',
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
        );

        if ($ciphertext === false || strlen($tag) !== 16) {
            throw new RuntimeException('Unable to protect the client network signal.');
        }

        return new PrivacySignals(
            ipCiphertext: $ciphertext,
            ipNonce: $nonce,
            ipAuthTag: $tag,
            encryptionKeyVersion: $version,
            ipHmac: hash_hmac('sha256', $ip, $ipHmacKey),
            ipHmacKeyVersion: $version,
            deviceHmac: hash_hmac('sha256', trim($deviceToken), $deviceHmacKey),
            deviceHmacKeyVersion: $version,
            browserHmac: hash_hmac('sha256', trim($browserFingerprint), $deviceHmacKey),
            browserHmacKeyVersion: $version,
        );
    }

    private function key(string $configKey, int $minimumBytes, bool $exact = false): string
    {
        $value = (string) config($configKey, '');
        $decoded = str_starts_with($value, 'base64:')
            ? base64_decode(substr($value, 7), true)
            : $value;

        if ($decoded !== false
            && ($exact ? strlen($decoded) === $minimumBytes : strlen($decoded) >= $minimumBytes)
        ) {
            return $decoded;
        }

        if (app()->environment(['local', 'testing'])) {
            return $this->fallbackKey($configKey, $minimumBytes);
        }

        throw new RuntimeException("Invalid runtime key: {$configKey}.");
    }

    private function fallbackKey(string $configKey, int $length): string
    {
        $appKey = (string) config('app.key', '');
        $seed = $appKey !== '' ? $appKey : 'encuestaselectorales-vote';
        $material = hash('sha256', $seed.'|'.$configKey, true);

        if ($length <= strlen($material)) {
            return substr($material, 0, $length);
        }

        return str_repeat($material, (int) ceil($length / strlen($material)));
    }
}
