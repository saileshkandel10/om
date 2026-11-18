import requests
import urllib3
urllib3.disable_warnings()

base = "https://saileshkandel.com.np"
s = requests.Session()
s.verify = False

# Homepage
r = requests.get(base, verify=False, timeout=10)
print(f"Home: {r.status_code}")
if "Whoops" in r.text: print("  -> Laravel error")
elif "403" in r.text: print("  -> 403 Forbidden")
elif "500" in r.text: print("  -> 500 Error")
else: print("  -> OK")

# Login
r = requests.get("https://saileshkandel.com.np/login", verify=False)
import re
m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
csrf = m.group(1) if m else ""

s = requests.Session()
s.verify = False
r = requests.post("https://saileshkandel.com.np/login", data={"_token": re.search(r'name="_token"\s+value="([^"]+)"', r.text).group(1), "email": "admin@omconnect.com", "password": "Password123!"}, allow_redirects=True)
print(f"Login: {r.status_code} -> {r.url}")

# Check routes
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
    st = ""
    if "Whoops" in r.text: st = "Laravel Error"
    elif "403" in r.text: st = "403 Forbidden"
    elif "500" in r.text: st = "500 Error"
    elif r.status_code == 200: st = "OK"
    print(f"  {r.status_code} {path} ({st})")

# API
for path, label in [("/api/v1/products", "Products API"), ("/api/v1/pharmacy/quizzes", "Quiz API"), ("/api/v1/pharmacy/quizzes/categories", "Categories API")]:
    r = requests.get(f"https://saileshkandel.com.np{path}", verify=False, timeout=10)
    print(f"  {r.status_code} {label}")

# APK
r = requests.head("https://saileshkandel.com.np/app-release.apk", verify=False)
print(f"\nAPK: {r.status_code} Size: {r.headers.get('Content-Length', 'N/A')}")

print("\n=== DONE ===")