<?php
/**
 * Vote security utilities shared by /api/votar.php and the CLI test scripts.
 *
 * This file loads its settings from /config/security.php, which is ignored by
 * git and lives outside public_html/ in production. The committed
 * security.example.php documents the required shape.
 */

function voteSecurityConfigPath(): string
{
    $localPath = __DIR__ . '/../config/security.php';
    $prodPath = isset($_SERVER['DOCUMENT_ROOT'])
        ? dirname($_SERVER['DOCUMENT_ROOT']) . '/config/security.php'
        : null;
    $envPath = getenv('VOTE_SECURITY_CONFIG') ?: getenv('CODEX_SECURITY_CONFIG') ?: '';

    if ($envPath !== '' && is_file($envPath)) {
        return $envPath;
    }

    if (is_file($localPath)) {
        return $localPath;
    }

    if ($prodPath !== null && is_file($prodPath)) {
        return $prodPath;
    }

    throw new RuntimeException(
        'No /config/security.php found (checked ' . $localPath .
        ($prodPath !== null ? ' and ' . $prodPath : '') . ').'
    );
}

function voteSecurityConfig(): array
{
    static $config = null;

    if (is_array($config)) {
        return $config;
    }

    $loaded = require voteSecurityConfigPath();
    if (!is_array($loaded)) {
        throw new RuntimeException('/config/security.php must return an array.');
    }

    $config = array_merge([
        'local_dev' => false,
        'rate_limit_threshold' => 5,
        'rate_limit_window_seconds' => 3600,
        'ip_salt' => '',
        'aes_key_path' => '',
        'trust_score' => [],
    ], $loaded);

    if (!is_string($config['ip_salt']) || $config['ip_salt'] === '') {
        throw new RuntimeException('/config/security.php must define ip_salt.');
    }

    if (!is_string($config['aes_key_path']) || $config['aes_key_path'] === '') {
        throw new RuntimeException('/config/security.php must define aes_key_path.');
    }

    return $config;
}

function voteLocalDevEnabled(): bool
{
    $config = voteSecurityConfig();
    return !empty($config['local_dev']);
}

function voteRateLimitThreshold(): int
{
    $config = voteSecurityConfig();
    return max(1, (int) $config['rate_limit_threshold']);
}

function voteRateLimitWindowSeconds(): int
{
    $config = voteSecurityConfig();
    return max(60, (int) $config['rate_limit_window_seconds']);
}

function voteIpSalt(): string
{
    $config = voteSecurityConfig();
    return (string) $config['ip_salt'];
}

function voteAesKey(): string
{
    $config = voteSecurityConfig();
    $path = (string) $config['aes_key_path'];

    if (!is_file($path)) {
        throw new RuntimeException('AES key file not found at ' . $path . '.');
    }

    $key = file_get_contents($path);
    if ($key === false) {
        throw new RuntimeException('Unable to read AES key file at ' . $path . '.');
    }

    if (strlen($key) !== 32) {
        throw new RuntimeException('AES key file must contain exactly 32 raw bytes.');
    }

    return $key;
}

function voteHashIp(string $ip): string
{
    return hash_hmac('sha256', $ip, voteIpSalt());
}

function voteEncryptIp(string $ip): array
{
    $key = voteAesKey();
    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($ip, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

    if ($ciphertext === false || $tag === '') {
        throw new RuntimeException('Unable to encrypt IP with AES-256-GCM.');
    }

    return [
        'ciphertext' => $ciphertext,
        'iv' => $iv,
        'tag' => $tag,
    ];
}

function voteNormalizeText(?string $value, int $maxLength = 0): string
{
    $value = trim((string) $value);
    if ($maxLength > 0 && strlen($value) > $maxLength) {
        $value = substr($value, 0, $maxLength);
    }
    return $value;
}

function voteFingerprint(string $browserFingerprint, string $userAgent, string $clientIp): string
{
    $browserFingerprint = trim($browserFingerprint);
    if ($browserFingerprint !== '') {
        return substr(hash('sha256', $browserFingerprint), 0, 64);
    }

    return hash('sha256', $userAgent . '|' . $clientIp);
}

function voteDefaultDeviceToken(): string
{
    return bin2hex(random_bytes(32));
}

function voteDistrictApproximateMatch(string $districtId, float $lat, float $lng): bool
{
    static $geofences = [
        'callao' => [
            'lat_min' => -12.14,
            'lat_max' => -11.99,
            'lng_min' => -77.19,
            'lng_max' => -77.03,
        ],
        'lima-cercado' => [
            'lat_min' => -12.10,
            'lat_max' => -11.99,
            'lng_min' => -77.10,
            'lng_max' => -77.00,
        ],
        'miraflores' => [
            'lat_min' => -12.13,
            'lat_max' => -12.09,
            'lng_min' => -77.05,
            'lng_max' => -77.01,
        ],
    ];

    if (!isset($geofences[$districtId])) {
        return false;
    }

    $bounds = $geofences[$districtId];

    return $lat >= $bounds['lat_min']
        && $lat <= $bounds['lat_max']
        && $lng >= $bounds['lng_min']
        && $lng <= $bounds['lng_max'];
}

function voteScore(array $signals): int
{
    $config = voteSecurityConfig();
    $weights = array_merge([
        'country_pe' => 15,
        'gps_accuracy_good' => 20,
        'gps_accuracy_ok' => 10,
        'interaction_medium' => 20,
        'interaction_slow' => 5,
        'device_cookie_present' => 10,
        'recent_burst_clear' => 20,
        'district_match' => 20,
    ], is_array($config['trust_score']) ? $config['trust_score'] : []);

    $score = 0;

    if (!empty($signals['district_match'])) {
        $score += (int) $weights['district_match'];
    }

    if (($signals['cf_country'] ?? '') === 'PE') {
        $score += (int) $weights['country_pe'];
    }

    $accuracy = isset($signals['gps_accuracy_meters']) ? (float) $signals['gps_accuracy_meters'] : null;
    if ($accuracy !== null) {
        if ($accuracy <= 25) {
            $score += (int) $weights['gps_accuracy_good'];
        } elseif ($accuracy <= 100) {
            $score += (int) $weights['gps_accuracy_ok'];
        }
    }

    $interaction = isset($signals['interaction_time_ms']) ? (int) $signals['interaction_time_ms'] : 0;
    if ($interaction >= 200 && $interaction < 15000) {
        $score += (int) $weights['interaction_medium'];
    } elseif ($interaction < 60000) {
        $score += (int) $weights['interaction_slow'];
    }

    if (!empty($signals['device_cookie_present'])) {
        $score += (int) $weights['device_cookie_present'];
    }

    if (empty($signals['recent_burst'])) {
        $score += (int) $weights['recent_burst_clear'];
    }

    return max(0, min(100, $score));
}
