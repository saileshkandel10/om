import ftplib
import requests
import urllib3
urllib3.disable_warnings()

ftp_host = "r30.merodns.com"
ftp_user = "saileshk"
ftp_pass = "XNi4r(}FTr#H3J!f"

print("Uploading fix_site.php...")
ftp = ftplib.FTP(ftp_host, timeout=30)
ftp.login(ftp_user, ftp_pass)
ftp.cwd("public_html")

with open(r"C:\Users\ADMIN\Downloads\om.lims.com.np\om.lims.com.np\fix_site.php", "rb") as fp:
    ftp.storbinary("STOR fix_site.php", fp)

ftp.quit()
print("Uploaded!")

url = "https://saileshkandel.com.np/fix_site.php"
print(f"Triggering {url}...")
# Use long timeout for composer install
r = requests.get(url, verify=False, timeout=600)
print(f"Status: {r.status_code}")
print("Output:")
print(r.text)