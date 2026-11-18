import ftplib
import requests
import urllib3
urllib3.disable_warnings()

ftp_host = "r30.merodns.com"
ftp_user = "saileshk"
ftp_pass = "XNi4r(}FTr#H3J!f"

# Try to delete composer.lock
ftp = ftplib.FTP(ftp_host, timeout=30)
ftp.login(ftp_user, ftp_pass)
ftp.cwd("omconnect")

try:
    ftp.delete("composer.lock")
    print("Deleted composer.lock")
except Exception as e:
    print("Could not delete composer.lock:", e)

ftp.quit()

# Now trigger composer update
import requests
import urllib3
urllib3.disable_warnings()

url = "https://saileshkandel.com.np/run_composer_update.php"
print(f"Triggering composer update at {url}...")
r = requests.get(url, verify=False, timeout=300)
print(f"Status: {r.status_code}")
print("Output:\n" + r.text)