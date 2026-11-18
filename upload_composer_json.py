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

with open(r"C:\Users\ADMIN\Downloads\om.lims.com.np\om.lims.com.np\composer.json", "rb") as fp:
    ftp.storbinary("STOR composer.json", fp)

ftp.quit()
print("Updated composer.json uploaded!")