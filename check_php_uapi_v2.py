import requests
import urllib3
urllib3.disable_warnings()

host = "https://r30.merodns.com:2083"
username = "saileshk"
password = "XNi4r(}FTr#H3J!f"

session = requests.Session()
session.verify = False

# First visit login page
session.get(f"{host}/", verify=False)

login_res = session.post(f"{host}/login/?login_only=1", data={"user": username, "pass": password})
print("Login status:", login_res.status_code)
print("Login text:", login_res.text[:500])

if login_res.status_code == 200:
    try:
        data = login_res.json()
        sec_token = data.get("security_token", "")
        print("Security token:", sec_token)

        # Check PHP version via PHPINI
        r = session.get(f"https://r30.merodns.com:2083/{sec_token}/execute/Mysql/get_server_information", verify=False, timeout=30)
        print("Server info:", r.status_code, r.text[:500])

        # Check PHP version via PHPINI
        r2 = session.get(f"https://r30.merodns.com:2083/{sec_token}/execute/LangPHP/php_get_vhost_versions", verify=False, timeout=30)
        print("PHP versions:", r2.status_code, r2.text)
except Exception as e:
    print("Error:", e)