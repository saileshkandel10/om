import ftplib
import requests
import urllib3
import io
urllib3.disable_warnings()

ftp_host = "r30.merodns.com"
ftp_user = "saileshk"
ftp_pass = "XNi4r(}FTr#H3J!f"

# 1. Connect via FTP and completely clean up
print("1. Connecting to FTP and cleaning up...")
ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect")

# Remove vendor directory and composer.lock
items_to_clean = ["vendor", "composer.lock"]
for item in ["vendor", "composer.lock"]:
    try:
        # Try to remove directory recursively
        def remove_dir(ftp, path):
            try:
                ftp.cwd(path)
                files = []
                ftp.retrlines("LIST", files.append)
                for f in files:
                    if f.startswith("d"):
                        # directory
                        fname = f.split()[-1]
                        if fname not in ['.', '..']:
                            remove_dir(ftp, fname)
                    else:
                        fname = f.split()[-1]
                        ftp.delete(fname)
                ftp.cwd("..")
                ftp.rmd(path)
            except:
                pass
        
        # Try simple delete
        try:
            ftp.rmd(item)
        except:
            try:
                ftp.delete(item)
            except:
                pass

    # Try simple delete
    try:
        ftp.rmd(item)
    except:
        try:
            ftp.delete(item)
        except:
            pass

ftp.quit()

# 2. Upload fresh composer.json
print("2. Uploading updated composer.json (PHP ^8.2)...")
ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect")

with open(r"C:\Users\ADMIN\Downloads\om.lims.com.np\om.lims.com.np\composer.json", "rb") as fp:
    ftp.storbinary("STOR composer.json", fp)

ftp.quit()
print("✓ Uploaded composer.json with PHP ^8.2")

# 2. Trigger composer install with --ignore-platform-req
import requests
import urllib3
urllib3.disable_warnings()

url = "https://saileshkandel.com.np/run_composer_update.php"
print(f"\nTriggering composer install on server...")
r = requests.get("https://saileshkandel.com.np/run_composer_update.php", verify=False, timeout=300)
print(f"Status: {r.status_code}")
print("Output:")
print(r.text)