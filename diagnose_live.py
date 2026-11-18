import requests
import re
import urllib3
urllib3.disable_warnings()

base_url = "https://saileshkandel.com.np"
session = requests.Session()
session.verify = False

print("=== DIAGNOSING SAILESHKANDEL.COM.NP ===\n")

# 1. Check homepage
print("1. Checking homepage...")
try:
    r = requests.get(base_url, verify=False, timeout=15)
    print(f"  Homepage status: {r.status_code}")
    if "Whoops" in r.text or "Whoops!" in r.text:
        print("  -> Laravel error page detected!")
    elif "403 Forbidden" in r.text:
        print("  -> 403 Forbidden")
    elif "500" in r.text:
        print("  -> 500 Server Error")
    elif "Maintenance" in r.text:
        print("  -> Maintenance Mode")
    else:
        print("  -> OK")
except Exception as e:
    print(f"  Error: {e}")

# 2. Check login page
print("\n2. Checking login page...")
try:
    r = requests.get("https://saileshkandel.com.np/login", verify=False, timeout=15)
    print(f"  Login page status: {r.status_code}")
    if "Whoops" in r.text:
        print("  -> Laravel error page!")
    elif "csrf_token" in r.text:
        print("  -> Login form OK")
except Exception as e:
    print(f"  Error: {e}")

# 3. Check admin routes after login
print("\n3. Checking admin routes...")
session = requests.Session()
session.verify = False

r_login = requests.get("https://saileshkandel.com.np/login", verify=False, timeout=15)
m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
csrf_token = m.group(1) if m else ""

if csrf_token:
    session.post("https://saileshkandel.com.np/login", data={
        "_token": csrf_token,
        "email": "admin@omconnect.com",
        "password": "Password123!"
    })
    
    routes = [
        ("/admin/dashboard", "Dashboard"),
        ("/admin/pharmacist-quiz", "Quiz"),
        ("/admin/pharmacist-quiz/categories", "Categories"),
        ("/admin/pharmacist-quiz/questions", "Questions"),
        ("/admin/pharmacist-quiz/results", "Results"),
        ("/admin/pharmacist-quiz/settings", "Settings"),
        ("/admin/products", "Products"),
        ("/admin/doctors", "Doctors"),
        ("/admin/reports/product-demand", "Reports"),
    ]

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
        if r.status_code == 200:
            print(f"  OK {path}")
        else:
            print(f"  FAIL {path}: {r.status_code}")
            if "Whoops" in r.text:
                print("  -> Laravel error!")
            elif "403" in r.text:
                print("  -> 403 Forbidden")
            elif "500" in r.text:
                print("  -> 500 Error")

# Check API
print("\nAPI check:")
api_routes = [
    ("/api/v1/products", "Products API"),
    ("/api/v1/pharmacy/quizzes", "Quiz API"),
    ("/api/v1/pharmacy/quizzes/categories", "Categories API"),
]

for path, label in [
    ("/api/v1/products", "Products API"),
    ("/api/v1/pharmacy/quizzes", "Quiz API"),
    ("/api/v1/pharmacy/quizzes/categories", "Categories API"),
]:
    r = requests.get(f"https://saileshkandel.com.np{path}", verify=False, timeout=10)
    print(f"  {r.status_code} {label}")

print("\n=== DIAGNOSIS COMPLETE ===")