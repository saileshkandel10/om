import requests
import urllib3
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
print("Login response:", login_res.text[:200])

if login_res.status_code == 200:
    try:
        data = login_res.json()
        sec_token = data.get("security_token", "")
        print("Security token:", sec_token[:20] + "..." if sec_token else "None")

        # Check PHP version via cPanel UAPI
        print("\nChecking PHP version via cPanel UAPI...")
        r = session.get(f"{host}{sec_token}/execute/LangPHP/php_get_vhost_versions", verify=False, timeout=30)
        print("PHP Versions status:", r.status_code)
        print("PHP Versions:", r.text)

        # Check current PHP version for the account
        r2 = session.get(f"{host}{sec_token}/execute/LangPHP/php_get_vhost_versions", verify=False, timeout=30)
        print("PHP Versions for vhosts:", r2.status_code)
        print(r2.text)

        # Check current PHP version for the account
        r3 = session.get(f"{host}{sec_token}/execute/LangPHP/php_get_impacted_domains", verify=False, timeout=30)
        print("Impacted domains:", r3.status_code)
        print(r3.text)
    except Exception as e:
        print("Error:", e)
        print("Response text:", login_res.text[:500])