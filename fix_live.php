<?php
// fix_live.php
set_time_limit(300);
echo "=== SERVER FIX SCRIPT ===\n";

$baseDir = '/home/saileshk';
$appDir = $baseDir . '/omconnect';
$pubDir = $baseDir . '/public_html';

// 1. Fix Storage Permissions
echo "Fixing storage permissions...\n";
$storagePath = $appDir . '/storage';
if (is_dir($storagePath)) {
    exec("chmod -R 777 $storagePath");
    echo "  -> storage permissions set to 777\n";
}

// 2. Fix bootstrap cache
$cachePath = $appDir . '/bootstrap/cache';
if (is_dir($cachePath)) {
    exec("chmod -R 777 $cachePath");
    echo "  -> bootstrap/cache permissions set to 777\n";
}

// 3. Clear Cache
echo "Clearing cache...\n";
require $appDir . '/vendor/autoload.php';
$app = require_once $appDir . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('optimize:clear');
echo "  -> Cache cleared.\n";

echo "\nFix complete!\n";
