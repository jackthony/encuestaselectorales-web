<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['REQUEST_METHOD'] ??= 'GET';
    $_SERVER['REQUEST_URI'] ??= '/';
    $_SERVER['SCRIPT_NAME'] ??= '/index.php';
    $_SERVER['SCRIPT_FILENAME'] ??= __FILE__;
    $_SERVER['SERVER_NAME'] ??= 'localhost';
    $_SERVER['HTTP_HOST'] ??= 'localhost';
    $_SERVER['SERVER_PORT'] ??= '80';
    $_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] = 'file';
    $_ENV['CACHE_STORE'] = $_SERVER['CACHE_STORE'] = 'array';
    if (empty($_ENV['APP_KEY'] ?? null) && empty($_SERVER['APP_KEY'] ?? null) && getenv('APP_KEY') === false) {
        $fallbackKey = 'base64:' . base64_encode(random_bytes(32));
        $_ENV['APP_KEY'] = $_SERVER['APP_KEY'] = $fallbackKey;
        putenv('APP_KEY=' . $fallbackKey);
    }
    putenv('SESSION_DRIVER=file');
    putenv('CACHE_STORE=array');
}

require __DIR__ . '/laravel-app/public/index.php';
