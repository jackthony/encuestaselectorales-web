<?php
require __DIR__ . '/vote-test-support.php';

$bootstrap = voteTestBootstrapSecurity(['local_dev' => false]);
try {
    $pdo = voteTestPdo();
    $encuesta = voteTestSeedEncuesta($pdo);
    $get = ['encuesta_id' => $encuesta['id']];
    $serverBase = [
        'REQUEST_METHOD' => 'POST',
        'HTTP_CF_CONNECTING_IP' => '198.51.100.10',
        'HTTP_CF_IPCOUNTRY' => 'PE',
        'HTTP_USER_AGENT' => 'Codex Vote Test',
    ];
    $threshold = 5;

    for ($i = 0; $i < $threshold; $i++) {
        $result = voteTestRequest($pdo, $serverBase, $get, [], voteTestPayload([
            'browser_fingerprint' => bin2hex(random_bytes(16)),
        ]));
        voteTestAssert($result['status'] === 200, 'expected seed vote ' . ($i + 1) . ' to succeed');
    }

    $limited = voteTestRequest($pdo, $serverBase, $get, [], voteTestPayload([
        'browser_fingerprint' => bin2hex(random_bytes(16)),
    ]));
    voteTestAssert($limited['status'] === 429, 'expected N+1 request to hit the rate limit');

    $differentIp = $serverBase;
    $differentIp['HTTP_CF_CONNECTING_IP'] = '198.51.100.11';
    $allowed = voteTestRequest($pdo, $differentIp, $get, [], voteTestPayload([
        'browser_fingerprint' => bin2hex(random_bytes(16)),
    ]));
    voteTestAssert($allowed['status'] === 200, 'different ip_hash should have an independent budget');

    echo "OK: rate limit rejects the N+1th vote and keeps other IP hashes independent\n";
} finally {
    voteTestCleanup($bootstrap);
}
