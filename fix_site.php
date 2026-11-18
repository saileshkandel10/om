<?php
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

chdir('/home/saileshk/omconnect');

echo "=== OMCONNECT SITE RECOVERY (PHP 8.3+) ===\n";

// 1. Run Composer Install for PHP 8.3
echo "1. Running composer install...\n";
exec('composer install --no-interaction --optimize-autoloader 2>&1', $output, $rv);
echo implode("\n", $output) . "\n";

// 2. Clear Caches
echo "\n2. Clearing application cache...\n";
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kernel->call('optimize:clear');
echo $kernel->output() . "\n";

echo "=== FIXES APPLIED. PLEASE RELOAD THE SITE. ===\n";
