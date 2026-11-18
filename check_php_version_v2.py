import requests
import urllib3
urllib3.disable_warnings()

# Try different paths for phpinfo
urls = [
    "https://saileshkandel.com.np/phpinfo.php",
    "https://saileshkandel.com.np/info.php",
    "https://saileshkandel.com.np/phpinfo.php?query=version",
]

import requests
import urllib3
urllib3.disable_warnings()

session = requests.Session()
session.verify = False

for url in urls:
    try:
        r = requests.get(url, verify=False, timeout=10)
        print(f"URL: {url} -> Status: {r.status_code}")
        if r.status_code == 200:
            import re
            match = re.search(r'PHP Version </td><td class="v">([^<]+)', r.text)
            if match:
                print(f"PHP Version: {match.group(1)}")
                break
            match = re.search(r'PHP Version.*?([0-9]+\.[0-9]+\.[0-9]+)', r.text)
            if match:
                print(f"PHP Version: {match.group(1)}")
                break
    except Exception as e:
        print(f"Error checking {url}: {e}")

# Also check via cPanel UAPI
import requests
import urllib3
urllib3.disable_warnings()

host = "https://r30.merodns.com:2083"
username = "saileshk"
password = "XNi4r(}FTr#H3J!f"

session = requests.Session()
session.verify = False

login_res = session.post("https://r30.merodns.com:2083/login/?login_only=1", data={"user": "saileshk", "pass": "XNi4r(}FTr#H3J!f"})
print("Login status:", login_res.status_code)

if login_res.status_code == 200:
    try:
        data = login_res.json()
        sec_token = data.get("security_token", "")
        print("Security token:", sec_token[:20] + "..." if sec_token else "None")

        # Check PHP version via cPanel UAPI
        r = session.get(f"https://r30.merodns.com:2083/{sec_token}/execute/LangPHP/php_get_vhost_versions", verify=False, timeout=30)
        print("PHP versions via UAPI:", r.status_code)
        print(r.text[:2000])
except Exception as e:
    print("Error:", e)