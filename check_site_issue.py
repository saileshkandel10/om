import ftplib
import io
import requests
import urllib3
urllib3.disable_warnings()

ftp = ftplib.FTP("r30.merodns.com", timeout=30)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")

# Check directory structure
try:
    ftp.cwd("/home/saileshk")
    dirs = []
    ftp.retrlines("LIST", dirs.append)
    print("Directories in /home/saileshk:")
    for d in dirs:
        print(" ", d)
except Exception as e:
    print("Error:", e)

# Check if public_html exists and check its contents
try:
    ftp.cwd("public_html")
    files = []
    ftp.retrlines("LIST", files.append)
    print("public_html contents:")
    for f in files:
        print(" ", f)
except Exception as e:
    print("Error accessing public_html:", e)

# Check storage/framework/down (maintenance mode)
try:
    data = io.BytesIO()
    ftp.retrbinary("RETR omconnect/storage/framework/down", data.write)
    content = data.getvalue().decode('utf-8')
    print("Maintenance mode file found:")
    print(content[:500])
except:
    print("No maintenance mode file found")

# Check .htaccess in public_html
try:
    data = io.BytesIO()
    ftp.retrbinary("RETR .htaccess", data.write)
    content = data.getvalue().decode('utf-8')
    print("\npublic_html/.htaccess:")
    print(content[:2000])
except:
    print("No .htaccess in public_html")

# Check index.php in public_html
try:
    data = io.BytesIO()
    ftp.retrbinary("RETR index.php", data.write)
    content = data.getvalue().decode('utf-8')
    print("\nindex.php in public_html:")
    print(content[:1000])
except:
    print("No index.php in public_html")

# Check omconnect/public directory
try:
    ftp.cwd("omconnect/public")
    files = []
    ftp.retrlines("LIST", files.append)
    print("\nomconnect/public contents:")
    for f in files:
        print(" ", f)
except Exception as e:
    print("Error accessing omconnect/public:", e)

ftp.quit()