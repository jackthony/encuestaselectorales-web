<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/vote-security.php';
require_once __DIR__ . '/../includes/trusted-ip.php';
require_once __DIR__ . '/../includes/vote-handler.php';

$pdo = require __DIR__ . '/../includes/db.php';
$rawInput = file_get_contents('php://input');
$rawInput = $rawInput === false ? '' : $rawInput;

$result = voteHandleRequest($pdo, $_SERVER, $_GET, $_COOKIE, $rawInput);

http_response_code($result['status']);

foreach (($result['headers'] ?? []) as $name => $value) {
    header($name . ': ' . $value);
}

foreach (($result['cookies'] ?? []) as $cookie) {
    setcookie($cookie['name'], $cookie['value'], $cookie['options']);
}

echo json_encode($result['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
