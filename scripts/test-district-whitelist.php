<?php
require __DIR__ . '/vote-test-support.php';

$bootstrap = voteTestBootstrapSecurity(['local_dev' => false]);
try {
    $pdo = voteTestPdo();
    $encuesta = voteTestSeedEncuesta($pdo);
    $get = ['encuesta_id' => $encuesta['id']];
    $server = [
        'REQUEST_METHOD' => 'POST',
        'HTTP_CF_CONNECTING_IP' => '198.51.100.30',
        'HTTP_CF_IPCOUNTRY' => 'PE',
        'HTTP_USER_AGENT' => 'Codex Vote Test',
    ];

    $before = (int) $pdo->query('SELECT COUNT(*) FROM votos_interactivos')->fetchColumn();
    $result = voteTestRequest($pdo, $server, $get, [], voteTestPayload([
        'ubigeo_votacion' => 'no-existe',
    ]));
    $after = (int) $pdo->query('SELECT COUNT(*) FROM votos_interactivos')->fetchColumn();

    voteTestAssert($result['status'] === 400, 'unknown district code must be rejected');
    voteTestAssert($after === $before, 'unknown district code must not write to votos_interactivos');

    echo "OK: district whitelist rejects unknown ubigeo values before any insert\n";
} finally {
    voteTestCleanup($bootstrap);
}
