<?php

set_time_limit(600);
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "=== COMPOSER FIX FOR PHP VERSION ===\n\n";

$extractTo = '/home/saileshk/omconnect';
chdir($extractTo);

echo 'Current directory: '.getcwd()."\n";
echo 'PHP Version: '.PHP_VERSION."\n\n";

// 1. Delete composer.lock if exists
echo "1. Removing composer.lock...\n";
if (file_exists('composer.lock')) {
    unlink('composer.lock');
    echo "Removed composer.lock\n";
} else {
    echo "composer.lock not found\n";
}

// 2. Delete vendor directory
echo "\n2. Removing vendor directory...\n";
if (is_dir('vendor')) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('vendor', RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir('vendor');
    echo "Removed vendor directory\n";
} else {
    echo "vendor directory not found\n";
}

// 3. Run composer install with ignore-platform-req
echo "\n2. Running composer install with --ignore-platform-req=php...\n";
echo "Command: composer install --no-interaction --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-dom\n";

$output = [];
$returnVar = 0;
exec('composer install --no-interaction --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-dom 2>&1', $output, $returnVar);

echo "Return code: $returnVar\n";
echo "Output:\n";
echo implode("\n", $output)."\n";

if ($returnVar === 0) {
    echo "\n=== COMPOSER INSTALL SUCCESSFUL ===\n";
} else {
    echo "\n=== COMPOSER INSTALL FAILED ===\n";
}
