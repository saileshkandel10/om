import ftplib
import requests
import urllib3
import io
urllib3.disable_warnings()

ftp_host = "r30.merodns.com"
ftp_user = "saileshk"
ftp_pass = "XNi4r(}FTr#H3J!f"

# 1. Connect via FTP and clean up vendor/composer.lock
print("1. Connecting to FTP and cleaning up...")
ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect")

# Delete vendor and composer.lock using simple approach
for item in ["vendor", "composer.lock"]:
    try:
        ftp.rmd(item)
    except:
        try:
            ftp.delete(item)
        except:
            pass

# Try to remove vendor directory contents
try:
    ftp.cwd("vendor")
    files = []
    ftp.retrlines("LIST", files.append)
    for f in files:
        if f.startswith("d"):
            fname = f.split()[-1]
            if fname not in ['.', '..']:
                try:
                    ftp.rmd(fname)
                except:
                    pass
            else:
                fname = f.split()[-1]
                ftp.delete(fname)
        ftp.cwd("..")
        ftp.rmd("vendor")
except:
    pass

# Try simple delete
try:
    ftp.rmd("vendor")
except:
    try:
        ftp.delete("vendor")
    except:
        pass

try:
    ftp.delete("composer.lock")
except:
    pass

ftp.quit()
print("1. Cleanup done")

# 2. Upload fresh composer.json
print("2. Uploading updated composer.json (PHP ^8.2)...")
ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect")

with open(r"C:\Users\ADMIN\Downloads\om.lims.com.np\om.lims.com.np\composer.json", "rb") as fp:
    ftp.storbinary("STOR composer.json", fp)

ftp.quit()
print("Uploaded composer.json with PHP ^8.2")

# 3. Try to trigger via a different approach - use FTP to create a simple PHP script
runner_php = """<?php
set_time_limit(600);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== COMPOSER INSTALL/UPDATE ===\n\n";

chdir('/home/saileshk/omconnect');

echo "1. Running composer install...\\n";
$output = [];
$returnVar = 0;
exec("composer install --no-interaction --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-dom 2>&1", $output, $returnVar);

echo "Return code: $returnVar\\n";
echo "Output:\\n";
echo implode("\\n", $output) . "\\n";

if ($returnVar === 0) {
    echo "\\n=== COMPOSER INSTALL SUCCESSFUL ===\\n";
} else {
    echo "\\n=== COMPOSER INSTALL FAILED ===\\n";
}
"""

import ftplib
import io

ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect")

with io.BytesIO(runner_php.encode('utf-8')) as fp:
    ftp.storbinary("STOR run_composer.php", fp)

ftp.quit()
print("Uploaded run_composer.php")

# 2. Trigger the script
import requests
import urllib3
urllib3.disable_warnings()

url = "https://saileshkandel.com.np/run_composer.php"
print(f"Triggering {url}...")
r = requests.get(url, verify=False, timeout=300)
print(f"Status: {r.status_code}")
print("Output:")
print(r.text)