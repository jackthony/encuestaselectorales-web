<?php

namespace App\Infrastructure\Security;

use Illuminate\Http\Request;
use RuntimeException;

final class TrustedClientIp
{
    public function resolve(Request $request): string
    {
        if (app()->environment('production')) {
            $clientIp = trim((string) $request->server('HTTP_CF_CONNECTING_IP', ''));
            $proxyIp = trim((string) $request->server('REMOTE_ADDR', ''));

            if (filter_var($clientIp, FILTER_VALIDATE_IP) === false
                || filter_var($proxyIp, FILTER_VALIDATE_IP) === false
                || ! $this->isTrustedProxy($proxyIp)
            ) {
                throw new RuntimeException('Unable to verify the Cloudflare client IP.');
            }

            return $clientIp;
        }

        $ip = $request->ip();

        if (! is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new RuntimeException('Unable to resolve a valid server-observed client IP.');
        }

        return $ip;
    }

    private function isTrustedProxy(string $ip): bool
    {
        foreach ((array) config('vote.trusted_proxies', []) as $cidr) {
            if (is_string($cidr) && $this->contains($cidr, $ip)) {
                return true;
            }
        }

        return false;
    }

    private function contains(string $cidr, string $ip): bool
    {
        [$network, $prefix] = array_pad(explode('/', $cidr, 2), 2, null);
        $networkBytes = inet_pton($network);
        $ipBytes = inet_pton($ip);

        if ($networkBytes === false || $ipBytes === false || strlen($networkBytes) !== strlen($ipBytes)) {
            return false;
        }

        $bits = $prefix === null ? strlen($networkBytes) * 8 : (int) $prefix;
        if ($bits < 0 || $bits > strlen($networkBytes) * 8) {
            return false;
        }

        $fullBytes = intdiv($bits, 8);
        $remainingBits = $bits % 8;

        if (substr($networkBytes, 0, $fullBytes) !== substr($ipBytes, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

        return (ord($networkBytes[$fullBytes]) & $mask) === (ord($ipBytes[$fullBytes]) & $mask);
    }
}
