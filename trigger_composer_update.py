import ftplib
import requests
import urllib3
urllib3.disable_warnings()

ftp_host = "r30.merodns.com"
ftp_user = "saileshk"
ftp_pass = "XNi4r(}FTr#H3J!f"

print("Uploading updated run_composer_update.php...")
ftp = ftplib.FTP(ftp_host, timeout=30)
ftp.login(ftp_user, ftp_pass)
ftp.cwd("omconnect")

with open(r"C:\Users\ADMIN\Downloads\om.lims.com.np\om.lims.com.np\run_composer_update.php", "rb") as fp:
    ftp.storbinary("STOR run_composer_update.php", fp)

ftp.quit()
print("Uploaded!")

url = "https://saileshkandel.com.np/run_composer_update.php"
print(f"Triggering {url}...")
r = requests.get(url, verify=False, timeout=300)
print(f"Status: {r.status_code}")
print("Output:\n" + r.text)