<?php
/**
 * BL-14 consumes this minimal helper now. BL-12 will harden it later with
 * Cloudflare range validation and origin trust checks.
 *
 * For the current MVP, it resolves to the real client IP when available and
 * falls back to REMOTE_ADDR for local development or when the proxy header is
 * absent. The rate limiter in /api/votar.php always uses this single source.
 */

function resolveTrustedClientIp(): array
{
    return resolveTrustedClientIpFromServer($_SERVER);
}

function resolveTrustedClientIpFromServer(array $server): array
{
    $remoteAddr = trim((string)($server['REMOTE_ADDR'] ?? ''));
    $cfConnectingIp = trim((string)($server['HTTP_CF_CONNECTING_IP'] ?? ''));
    $localDev = voteLocalDevEnabled();

    if ($localDev && $remoteAddr !== '') {
        return [
            'ip' => $remoteAddr,
            'source' => 'local_dev',
        ];
    }

    if ($cfConnectingIp !== '') {
        return [
            'ip' => $cfConnectingIp,
            'source' => 'cf_connecting_ip',
        ];
    }

    if (!$localDev) {
        throw new RuntimeException('Unable to resolve trusted client IP without CF-Connecting-IP.');
    }

    if ($remoteAddr !== '') {
        return [
            'ip' => $remoteAddr,
            'source' => 'remote_addr',
        ];
    }

    throw new RuntimeException('Unable to resolve client IP.');
}
