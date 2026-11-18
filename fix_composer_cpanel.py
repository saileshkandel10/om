import requests
import urllib3
import base64
urllib3.disable_warnings()

host = "https://r30.merodns.com:2083"
username = "saileshk"
password = "XNi4r(}FTr#H3J!f"

session = requests.Session()
session.verify = False

# Login to cPanel
print("Logging into cPanel...")
login_res = session.post(f"{host}/login/?login_only=1", data={"user": username, "pass": password})
print("Login status:", login_res.status_code)

# Try to get security token from cookies or response
sec_token = None
if login_res.status_code == 200:
    # Check cookies for session
    for cookie in session.cookies:
        if 'session' in cookie.name.lower() or 'token' in cookie.name.lower():
            print(f"Cookie: {cookie.name} = {cookie.value[:20]}...")
    
    # Try to extract from response
    import re
    match = re.search(r'security_token["\']?\s*[:=]\s*["\']?([^"\'}\s]+)', login_res.text)
    if match:
        sec_token = match.group(1)
        print(f"Security token found: {sec_token[:20]}...")
    else:
        # Try to find in cookies
        for cookie in session.cookies:
            if 'cpsess' in cookie.name.lower():
                sec_token = cookie.value
                print(f"Session token from cookie: {sec_token[:20]}...")
                break

# Try Fileman API to upload a script
if sec_token:
    print("\nTrying Fileman API to upload composer fix...")
    
    # Create a PHP script to run composer
    fix_script = '''<?php
    set_time_limit(600);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    echo "=== COMPOSER FIX ===\\n";
    chdir('/home/saileshk/omconnect');
    
    // Remove vendor and composer.lock
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
        echo "Removed vendor\\n";
    }
    if (file_exists('composer.lock')) {
        unlink('composer.lock');
        echo "Removed composer.lock\\n";
    }
    
    // Run composer install
    echo "Running composer install...\\n";
    $output = [];
    $returnVar = 0;
    exec("composer install --no-interaction --optimize-autoloader --ignore-platform-req=php --ignore-platform-req=ext-dom 2>&1", $output, $returnVar);
    echo "Return code: $returnVar\\n";
    echo implode("\\n", $output) . "\\n";
    
    if ($returnVar === 0) {
        echo "\\n=== SUCCESS ===\\n";
    } else {
        echo "\\n=== FAILED ===\\n";
    }
    ?>
    
    // Try to upload and execute via Fileman API
    if (sec_token) {
        $script = base64_encode($fix_script);
        $data = [
            'file' => 'fix_composer.php',
            'content' => $script,
            'op' => 'upload'
        ];
        
        $ch = curl_init("https://r30.merodns.com:2083/cpsess{$sec_token}/execute/Fileman/upload");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        echo "Upload result: $result";
    }