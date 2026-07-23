<?php
require __DIR__ . '/vote-test-support.php';

$bootstrap = voteTestBootstrapSecurity(['local_dev' => false]);
try {
    $pdo = voteTestPdo();
    $encuesta = voteTestSeedEncuesta($pdo);
    $get = ['encuesta_id' => $encuesta['id']];
    $server = [
        'REQUEST_METHOD' => 'POST',
        'HTTP_CF_CONNECTING_IP' => '198.51.100.20',
        'HTTP_CF_IPCOUNTRY' => 'PE',
        'HTTP_USER_AGENT' => 'Codex Vote Test',
    ];
    $threshold = 5;

    require_once __DIR__ . '/../includes/helpers.php';
    require_once __DIR__ . '/../includes/vote-security.php';
    require_once __DIR__ . '/../includes/trusted-ip.php';
    require_once __DIR__ . '/../includes/vote-handler.php';

    $threw = false;
    try {
        resolveTrustedClientIpFromServer([
            'REMOTE_ADDR' => '192.0.2.1',
        ]);
    } catch (RuntimeException $e) {
        $threw = true;
    }
    voteTestAssert($threw, 'production must not silently fall back to REMOTE_ADDR when CF-Connecting-IP is missing');

    for ($i = 0; $i < $threshold; $i++) {
        $seed = voteTestRequest($pdo, $server, $get, [], voteTestPayload([
            'browser_fingerprint' => bin2hex(random_bytes(16)),
        ]));
        voteTestAssert($seed['status'] === 200, 'expected seed vote ' . ($i + 1) . ' to succeed');
    }

    $forged = voteTestRequest($pdo, $server, $get, [], voteTestPayload([
        'browser_fingerprint' => bin2hex(random_bytes(16)),
    ]));

    voteTestAssert($forged['status'] === 429, 'a forged fingerprint without a cookie must still be rate-limited by IP');
    voteTestAssert(!array_key_exists('trust_score', $forged['body']), 'trust_score must never be exposed on the error response');

    echo "OK: forged fingerprints do not bypass the IP gate\n";
} finally {
    voteTestCleanup($bootstrap);
}
