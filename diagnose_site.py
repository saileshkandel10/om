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
    r = requests.get("https://saileshkandel.com.np/", verify=False, timeout=15)
    print(f"  Homepage status: {r.status_code}")
    if r.status_code == 200:
        # Check for Laravel errors or maintenance mode
        if "Whoops" in r.text or "Whoops!" in r.text:
            print("  -> Laravel error page detected")
        elif "403 Forbidden" in r.text:
            print("  -> 403 Forbidden")
        elif "500" in r.text or "500 Internal Server Error" in r.text:
            print("  -> 500 Server Error")
        elif "Maintenance Mode" in r.text:
            print("  -> Maintenance Mode")
        else:
            print("  -> OK")
    else:
        print(f"  -> Error: {r.status_code}")
except Exception as e:
    print(f"  Error: {e}")

# 2. Check login page
print("\n2. Checking login page...")
try:
    r = requests.get("https://saileshkandel.com.np/login", verify=False, timeout=15)
    print(f"  Login page status: {r.status_code}")
    if "csrf_token" in r.text:
        print("  CSRF token found - login form OK")
    if "Whoops" in r.text:
        print("  -> Laravel error page!")
except Exception as e:
    print(f"  Error: {e}")

# 3. Check key admin routes
print("\n3. Checking key admin routes...")
admin_routes = [
    "/admin/dashboard",
    "/admin/pharmacist-quiz",
    "/admin/pharmacist-quiz/categories",
    "/admin/pharmacist-quiz/questions",
    "/admin/pharmacist-quiz/results",
    "/admin/pharmacist-quiz/settings",
    "/admin/pharmacist-quiz/settings",
    "/admin/advertisements",
    "/admin/users",
    "/admin/purchase-bills",
]

import requests
import re
session = requests.Session()
session.verify = False

r_login_page = session.get("https://saileshkandel.com.np/login")
m = re.search(r'name="_token"\s+value="([^"]+)"', r_login_page.text)
csrf_token = m.group(1) if m else ""

if csrf_token:
    session.post("https://saileshkandel.com.np/login", data={
        "_token": csrf_token,
        "email": "admin@omconnect.com",
        "password": "Password123!"
    })
    
    for path, label in [
        ("/admin/dashboard", "Dashboard"),
        ("/admin/pharmacist-quiz", "Pharmacist Quiz"),
        ("/admin/pharmacist-quiz/categories", "Categories"),
        ("/admin/pharmacist-quiz/questions", "Question Bank"),
        ("/admin/pharmacist-quiz/results", "Results"),
        ("/admin/pharmacist-quiz/settings", "Settings"),
        ("/admin/products", "Products"),
        ("/admin/products/export", "Export"),
    ]:
        r = session.get(f"https://saileshkandel.com.np{path}")
        if r.status_code == 200:
            print(f"  ✓ {label}: 200 OK")
        else:
            print(f"  ✗ {label}: {r.status_code}")
            if "Whoops" in r.text:
                print("    -> Laravel error page!")
            elif "403 Forbidden" in r.text:
                print("    -> 403 Forbidden")
            elif "500" in r.text or "500 Internal Server Error" in r.text:
                print("    -> 500 Server Error")
            elif "Maintenance" in r.text:
                print("    -> Maintenance Mode")
except Exception as e:
    print(f"Error: {e}")

# Check API endpoints
print("\n3. Checking API endpoints...")
api_routes = [
    ("/api/v1/products", "Products API"),
    ("/api/v1/pharmacy/quizzes", "Quiz API"),
    ("/api/v1/pharmacy/quizzes/categories", "Quiz Categories API"),
    ("/api/v1/pharmacy/quizzes/settings", "Quiz Settings API"),
]

for path, label in api_routes:
    r = session.get(f"https://saileshkandel.com.np{path}")
    print(f"  {label}: {r.status_code}")
    if r.status_code != 200:
        print(f"    Response: {r.text[:200]}")

# Check key pages
print("\n4. Key pages check...")
pages = [
    ("/", "Homepage"),
    ("/login", "Login"),
    ("/register", "Register"),
    ("/admin/login", "Admin Login"),
]

for path, label in pages:
    r = requests.get(f"https://saileshkandel.com.np{path}", verify=False, timeout=10)
    print(f"  {label}: {r.status_code}")

print("\n=== DIAGNOSIS COMPLETE ===")