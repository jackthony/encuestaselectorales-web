<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Keep the public front controller bootable even when Hostinger has not
// provisioned a .env yet. A stable key is stored outside the web root so the
// app does not rotate encryption state on every request.
if (empty($_ENV['APP_KEY'] ?? null) && empty($_SERVER['APP_KEY'] ?? null) && getenv('APP_KEY') === false) {
    $keyFile = __DIR__ . '/../storage/app.key';
    $appKey = null;
    if (is_file($keyFile)) {
        $appKey = trim((string) file_get_contents($keyFile));
    }
    if (!is_string($appKey) || $appKey === '') {
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        @file_put_contents($keyFile, $appKey . PHP_EOL, LOCK_EX);
    }
    $_ENV['APP_KEY'] = $_SERVER['APP_KEY'] = $appKey;
    putenv('APP_KEY=' . $appKey);
}

$_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] = $_ENV['SESSION_DRIVER'] ?? 'file';
$_ENV['CACHE_STORE'] = $_SERVER['CACHE_STORE'] = $_ENV['CACHE_STORE'] ?? 'array';
$_ENV['QUEUE_CONNECTION'] = $_SERVER['QUEUE_CONNECTION'] = $_ENV['QUEUE_CONNECTION'] ?? 'sync';
putenv('SESSION_DRIVER=' . $_ENV['SESSION_DRIVER']);
putenv('CACHE_STORE=' . $_ENV['CACHE_STORE']);
putenv('QUEUE_CONNECTION=' . $_ENV['QUEUE_CONNECTION']);

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
