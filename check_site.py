import requests
import urllib3
urllib3.disable_warnings()

base = "https://saileshkandel.com.np"
s = requests.Session()
s.verify = False

print("=== DIAGNOSING saileshkandel.com.np ===\n")

# 1. Homepage
r = requests.get(base_url, verify=False, timeout=10)
print(f"Homepage: {r.status_code}")
if "Whoops" in r.text: print("  -> Laravel error page!")
elif "500" in r.text: print("  -> 500 error")
elif "403" in r.text: print("  -> 403 Forbidden")
elif "Maintenance" in r.text: print("  -> Maintenance mode")
else: print("  -> OK")

# 2. Login
r = requests.get("https://saileshkandel.com.np/login", verify=False, timeout=10)
print(f"\nLogin page: {r.status_code}")
m = __import__('re').search(r'name="_token"\s+value="([^"]+)"', r.text)
csrf = m.group(1) if m else ""

# 3. Login
if r.status_code == 200:
    s = requests.Session()
    s.verify = False
    r = s.post("https://saileshkandel.com.np/login", data={"_token": __import__('re').search(r'name="_token"\s+value="([^"]+)"', r.text).group(1), "email": "admin@omconnect.com", "password": "Password123!"}, allow_redirects=True)
    print(f"\nLogin: {r.status_code} -> {r.url}")
    
    # Check admin routes
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
    
    for path, label in [("/admin/dashboard", "Dashboard"), ("/admin/pharmacist-quiz", "Quiz"), ("/admin/pharmacist-quiz/categories", "Categories"), ("/admin/pharmacist-quiz/questions", "Questions"), ("/admin/pharmacist-quiz/results", "Results"), ("/admin/pharmacist-quiz/settings", "Settings"), ("/admin/products", "Products"), ("/admin/doctors", "Doctors"), ("/admin/reports/product-demand", "Reports")]:
        r = requests.get(f"https://saileshkandel.com.np{path}", verify=False, timeout=10)
        print(f"  {r.status_code} {path}")

# Check API
print("\nAPI:")
for path, label in [("/api/v1/products", "Products API"), ("/api/v1/pharmacy/quizzes", "Quiz API"), ("/api/v1/pharmacy/quizzes/categories", "Categories API")]:
    r = requests.get(f"https://saileshkandel.com.np{path}", verify=False, timeout=10)
    print(f"  {r.status_code} {label}")

# APK download
r = requests.head("https://saileshkandel.com.np/app-release.apk", verify=False)
print(f"\nAPK: {r.status_code} Size: {r.headers.get('Content-Length', 'N/A')}")

print("\n=== DONE ===")