import requests
import urllib3
urllib3.disable_warnings()

base = "https://saileshkandel.com.np"
s = requests.Session()
s.verify = False

# Homepage
r = requests.get(base_url, verify=False, timeout=10)
print(f"Home: {r.status_code}")
if "Whoops" in r.text: print("  -> Laravel error")
elif "403" in r.text: print("  -> 403 Forbidden")
elif "500" in r.text: print("  -> 500 Error")

# Login
r = requests.get(base_url + "/login", verify=False)
import re
m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
csrf = m.group(1) if m else ""

# Login
session = requests.Session()
session.verify = False
r = session.post(base_url + "/login", data={"_token": re.search(r'name="_token"\s+value="([^"]+)"', r.text).group(1), "email": "admin@omconnect.com", "password": "Password123!"})
print(f"\nLogin: {r.status_code} -> {r.url}")

# Test key routes
for path, label in [
    ("/admin/dashboard", "Dashboard"),
    ("/admin/pharmacist-quiz", "Quiz"),
    ("/admin/pharmacist-quiz/categories", "Categories"),
    ("/admin/pharmacist-quiz/questions", "Questions"),
    ("/admin/pharmacist-quiz/results", "Results"),
    ("/admin/pharmacist-quiz/settings", "Settings"),
    ("/admin/products", "Products"),
    ("/admin/reports/product-demand", "Reports"),
]:
    r = requests.get(f"https://saileshkandel.com.np{path}", verify=False, timeout=10)
    status = r.status_code
    status_text = ""
    if "Whoops" in r.text: status_text = "Laravel Error"
    elif "403" in r.text: status_text = "403 Forbidden"
    elif "500" in r.text: status_text = "500 Error"
    elif r.status_code == 200: status_text = "OK"
    print(f"  {r.status_code} {path} ({status_text})")