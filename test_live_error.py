import requests
import re
import urllib3
urllib3.disable_warnings()

base_url = "https://saileshkandel.com.np"
session = requests.Session()
session.verify = False

# 1. Login as admin
print("Logging in to live server...")
r_login_page = session.get(f"{base_url}/login")
m = re.search(r'name="_token"\s+value="([^"]+)"', r_login_page.text)
csrf_token = m.group(1) if m else ""

r_login = session.post(f"{base_url}/login", data={
    "_token": csrf_token,
    "email": "admin@omconnect.com",
    "password": "Password123!"
}, allow_redirects=True)
print("Login status:", r_login.status_code)

# 2. Test Product Demand Report
print("\nTesting /admin/reports/product-demand...")
r_demand = session.get(f"{base_url}/admin/reports/product-demand")
print("Status:", r_demand.status_code)
if r_demand.status_code == 500:
    print("Error Preview:", r_demand.text[:1500])

# 3. Test Products Export
print("\nTesting /admin/products/export...")
r_export = session.get(f"{base_url}/admin/products/export")
print("Status:", r_export.status_code)
if r_export.status_code == 500:
    print("Error Preview:", r_export.text[:1000])