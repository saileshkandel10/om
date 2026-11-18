import requests
import urllib3
urllib3.disable_warnings()

host = "https://r30.merodns.com:2083"
username = "saileshk"
password = "XNi4r(}FTr#H3J!f"

session = requests.Session()
session.verify = False

# Login
login_res = session.post(f"{host}/login/?login_only=1", data={"user": username, "pass": password})
sec_token = login_res.json()["security_token"]

# Check PHP version via cPanel UAPI
print("Checking available PHP versions via cPanel UAPI...")
r = session.get(f"{host}{sec_token}/execute/PHP/get_php_versions", verify=False, timeout=30)
print("PHP Versions:", r.status_code)
print(r.text)

# Check current PHP version for the account
print("\nChecking current PHP version for account...")
r2 = session.get(f"{host}{sec_token}/execute/LangPHP/php_get_vhost_versions", verify=False, timeout=30)
print("PHP Versions for vhosts:", r2.status_code)
print(r2.text)