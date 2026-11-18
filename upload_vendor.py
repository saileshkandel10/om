import os
import zipfile
import ftplib
import requests
import urllib3
urllib3.disable_warnings()

root_dir = r"C:\Users\ADMIN\Downloads\om.lims.com.np\om.lims.com.np"
zip_path = r"C:\Users\ADMIN\Downloads\om.lims.com.np\vendor_updated.zip"

print("1. Creating vendor_updated.zip...")
with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zf:
    vendor_dir = os.path.join(root_dir, 'vendor')
    for r, dirs, files in os.walk(vendor_dir):
        for file in files:
            fp = os.path.join(r, file)
            arcname = os.path.relpath(fp, root_dir)
            zf.write(fp, arcname.replace('\\', '/'))

print(f"vendor_updated.zip size: {os.path.getsize(zip_path) / (1024*1024):.2f} MB")

# 2. Upload via FTP
ftp_host = "r30.merodns.com"
ftp_user = "saileshk"
ftp_pass = "XNi4r(}FTr#H3J!f"

print("2. Uploading vendor_updated.zip to FTP...")
ftp = ftplib.FTP(ftp_host, timeout=120)
ftp.login(ftp_user, ftp_pass)
ftp.sendcmd("TYPE I")
ftp.cwd("omconnect")

with open(zip_path, "rb") as fp:
    ftp.storbinary("STOR vendor_updated.zip", fp, blocksize=512*1024)

print("Upload complete!")

# Create extractor script
extractor_php = """<?php
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain');

$zipFile = __DIR__ . '/vendor_updated.zip';
$extractTo = __DIR__; // omconnect/

$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    echo "Opened vendor_updated.zip! Num files: " . $zip->numFiles . "\n";
    $zip->extractTo($extractTo);
    $zip->close();
    echo "Extraction complete!\\n";
    @unlink($zipFile);
} else {
    echo "Failed to open vendor_updated.zip\\n";
}
"""

import io
ftp.storbinary("STOR extract_vendor_v2.php", io.BytesIO(extractor_php.encode('utf-8')))
ftp.quit()

# Trigger extraction
url = "https://saileshkandel.com.np/extract_vendor_v2.php"
print("Triggering extraction on server...")
r = requests.get("https://saileshkandel.com.np/extract_vendor_v2.php", verify=False, timeout=180)
print(r.text)