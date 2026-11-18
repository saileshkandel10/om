import requests
import urllib3
urllib3.disable_warnings()

r = requests.get("https://saileshkandel.com.np/phpinfo.php", verify=False, timeout=10)
print("PHP Info page status:", r.status_code)
# Find PHP version in response
import re
match = re.search(r'PHP Version </td><td class="v">([^<]+)', r.text)
if match:
    print(f"PHP Version on server: {match.group(1)}")
else:
    # Try alternative pattern
    match = re.search(r'PHP Version.*?([0-9]+\.[0-9]+\.[0-9]+)', r.text)
    if match:
        print(f"PHP Version on server: {match.group(1)}")
    else:
        print("Could not determine PHP version from output")
        print(r.text[:2000])