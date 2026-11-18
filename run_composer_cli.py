import ftplib
import requests
import urllib3
import io
urllib3.disable_warnings()

ftp_host = "r30.merodns.com"
ftp_user = "saileshk"
ftp_pass = "XNi4r(}FTr#H3J!f"

# 1. Connect via FTP and upload the PHP script
print("1. Uploading composer fix script...")
ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect")

runner_php = """<?php
set_time_limit(600);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== COMPOSER FIX FOR PHP VERSION ===\\n\n";

chdir('/home/saileshk/omconnect');

echo "1. Removing vendor and composer.lock...\\n";
// Remove vendor directory
if (is_dir('vendor')) {
    \$files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('vendor', RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach (\$files as \$file) {
        if (\$file->isDir()) {
            rmdir(\$file->getPathname());
        } else {
            unlink(\$file->getPathname());
        }
    }
    rmdir('vendor');
    echo "Removed vendor directory\\n";
}

// Remove composer.lock
if (file_exists('composer.lock')) {
    unlink('composer.lock');
    echo "Removed composer.lock\\n";
}

echo "2. Running composer install with --ignore-platform-req=php...\\n";
\$output = [];
\$returnVar = 0;
exec("composer install --no-interaction --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-dom 2>&1", \$output, \$returnVar);

echo "Return code: \$returnVar\\n";
echo "Output:\\n";
echo implode("\\n", \$output) . "\\n";

if (\$returnVar === 0) {
    echo "\\n=== COMPOSER INSTALL SUCCESSFUL ===\\n";
} else {
    echo "\\n=== COMPOSER INSTALL FAILED ===\\n";
}
"""

import ftplib
import io

print("Uploading fix script...")
ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect")

with io.BytesIO(runner_php.encode('utf-8')) as fp:
    ftp.storbinary("STOR fix_composer.php", fp)

ftp.quit()
print("Uploaded fix_composer.php")

# Trigger via HTTP
import requests
import urllib3
urllib3.disable_warnings()

url = "https://saileshkandel.com.np/fix_composer.php"
print(f"Triggering {url}...")
r = requests.get(url, verify=False, timeout=300)
print(f"Status: {r.status_code}")
print("Output:")
print(r.text)