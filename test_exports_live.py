import requests
import re
import urllib3
urllib3.disable_warnings()

base_url = "https://saileshkandel.com.np"
session = requests.Session()
session.verify = False

# 1. Login as Admin
r_login_page = session.get(f"{base_url}/login")
m = re.search(r'name="_token"\s+value="([^"]+)"', r_login_page.text)
csrf_token = m.group(1) if m else ""

r_login = session.post(f"{base_url}/login", data={
    "_token": csrf_token,
    "email": "admin@omconnect.com",
    "password": "Password123!"
}, allow_redirects=True)
print("Admin Login Status:", r_login.status_code)

# 2. Test All Export Endpoints
export_urls = [
    ("/admin/products/export?format=xlsx", "Products Export (.xlsx)"),
    ("/admin/products/export?format=csv", "Products Export (.csv)"),
    ("/admin/doctors/export?scope=all&format=xlsx", "Doctors Export (.xlsx)"),
    ("/admin/doctors/export?scope=all&format=csv", "Doctors Export (.csv)"),
    ("/admin/shareholders/export?scope=all&format=xlsx", "Shareholders Export (.xlsx)"),
    ("/admin/shareholders/export?scope=all&format=csv", "Shareholders Export (.csv)"),
    ("/admin/roles/export?type=roles&format=xlsx", "Roles Export (.xlsx)"),
    ("/admin/roles/export?type=roles&format=csv", "Roles Export (.csv)"),
    ("/admin/reports/product-demand/export?format=xlsx", "Product Demand Report Export (.xlsx)"),
]

print("\n--- TESTING EXPORT ENDPOINTS ---")
for path, label in export_urls:
    try:
        r = session.get(f"{base_url}{path}", allow_redirects=False)
        content_type = r.headers.get("Content-Type", "N/A")
        content_disp = r.headers.get("Content-Disposition", "N/A")
        size = len(r.content)
        print(f"[{r.status_code}] {label}")
        print(f"       Content-Type: {content_type} | Length: {size} bytes | Disposition: {content_disp}")
        if r.status_code != 200:
            print(f"       ERROR Body: {r.text[:300]}")
    except Exception as e:
        print(f"[ERR] {label}: {e}")

