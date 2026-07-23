<?php
require __DIR__ . '/vote-test-support.php';

$bootstrap = voteTestBootstrapSecurity(['local_dev' => false]);
try {
    $pdo = voteTestPdo();
    $encuesta = voteTestSeedEncuesta($pdo);
    $get = ['encuesta_id' => $encuesta['id']];
    $server = [
        'REQUEST_METHOD' => 'POST',
        'HTTP_CF_CONNECTING_IP' => '198.51.100.40',
        'HTTP_CF_IPCOUNTRY' => 'PE',
        'HTTP_USER_AGENT' => 'Codex Vote Test',
    ];

    $success = voteTestRequest($pdo, $server, $get, [], voteTestPayload());
    voteTestAssert($success['status'] === 200, 'expected a normal vote to succeed');
    voteTestAssert(!array_key_exists('trust_score', $success['body']), 'success response must not expose trust_score');
    voteTestAssert(strpos(json_encode($success['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'trust_score') === false, 'success payload must not serialize trust_score');

    $error = voteTestRequest($pdo, $server, $get, [], voteTestPayload([
        'ubigeo_votacion' => 'bad-slug',
    ]));
    voteTestAssert($error['status'] === 400, 'expected an error for an invalid district code');
    voteTestAssert(!array_key_exists('trust_score', $error['body']), 'error response must not expose trust_score');
    voteTestAssert(strpos(json_encode($error['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'trust_score') === false, 'error payload must not serialize trust_score');

    echo "OK: trust_score never appears in success or error responses\n";
} finally {
    voteTestCleanup($bootstrap);
}
