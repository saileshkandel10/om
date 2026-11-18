<?php

set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "=== COMPOSER FIX FOR PHP VERSION ===\n\n";

$extractTo = '/home/saileshk/omconnect';
chdir($extractTo);

echo 'Current directory: '.getcwd()."\n";
echo 'PHP Version: '.PHP_VERSION."\n\n";

// 1. Delete composer.lock if it exists
echo "1. Removing composer.lock if exists...\n";
if (file_exists('composer.lock')) {
    unlink('composer.lock');
    echo "  -> Removed composer.lock\n";
} else {
    echo "  -> composer.lock not found\n";
}

// 2. Run composer update with ignore-platform-req
echo "\n2. Running composer update with --ignore-platform-req=php...\n";
echo "Command: composer update --no-interaction --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-dom\n";

$output = [];
$returnVar = 0;
exec('composer update --no-interaction --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-dom 2>&1', $output, $returnVar);

echo "Return code: $returnVar\n";
echo "Output:\n";
echo implode("\n", $output)."\n";

if ($returnVar === 0) {
    echo "\n=== COMPOSER UPDATE SUCCESSFUL ===\n";
} else {
    echo "\n=== COMPOSER UPDATE FAILED ===\n";
}
