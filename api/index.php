<?php

define('LARAVEL_START', microtime(true));

$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;

$storagePath = '/tmp/storage';
foreach ([
    $storagePath . '/app/public',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/testing',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
    '/tmp/views',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

$dbFile = '/tmp/database.sqlite';
$needsInit = !file_exists($dbFile) || filesize($dbFile) === 0;
if ($needsInit) {
    @touch($dbFile);
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($storagePath);

if ($needsInit) {
    try {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->call('migrate', ['--force' => true]);
        $kernel->call('db:seed', ['--force' => true]);
    } catch (\Throwable $e) {
        error_log('DB init error: ' . $e->getMessage());
    }
}

$app->handleRequest(Illuminate\Http\Request::capture());
