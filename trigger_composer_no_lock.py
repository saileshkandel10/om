import ftplib
import requests
import urllib3
urllib3.disable_warnings()

ftp_host = "r30.merodns.com"
ftp_user = "saileshk"
ftp_pass = "XNi4r(}FTr#H3J!f"

ftp = ftplib.FTP(ftp_host, timeout=30)
ftp.login(ftp_user, ftp_pass)
ftp.cwd("omconnect")

# Delete composer.lock
try:
    ftp.delete("composer.lock")
    print("Deleted composer.lock")
except Exception as e:
    print("Could not delete composer.lock:", e)

ftp.quit()

# Trigger composer update again
url = "https://saileshkandel.com.np/run_composer_update.php"
print(f"Triggering {url}...")
r = requests.get(url, verify=False, timeout=300)
print(f"Status: {r.status_code}")
print("Output:\n" + r.text)