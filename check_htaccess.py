import ftplib
import io

ftp = ftplib.FTP("r30.merodns.com", timeout=30)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect")

# Check for maintenance mode file
try:
    data = io.BytesIO()
    ftp.retrbinary("RETR storage/framework/down", data.write)
    content = data.getvalue().decode('utf-8')
    print("Maintenance file found:")
    print(content[:500])
except:
    print("No maintenance mode file found")

# Check .htaccess in omconnect
try:
    data = io.BytesIO()
    ftp.retrbinary("RETR .htaccess", data.write)
    content = data.getvalue().decode('utf-8')
    print("omconnect .htaccess:")
    print(content[:2000])
except:
    print("No omconnect .htaccess found")

# Check public_html .htaccess
ftp.cwd("/home/saileshk/public_html")
try:
    data = io.BytesIO()
    ftp.retrbinary("RETR .htaccess", data.write)
    content = data.getvalue().decode('utf-8')
    print("\npublic_html .htaccess:")
    print(content[:2000])
except:
    print("No public_html .htaccess found")

# Check public_html index.php
try:
    data = io.BytesIO()
    ftp.retrbinary("RETR index.php", data.write)
    content = data.getvalue().decode('utf-8')
    print("\npublic_html/index.php:")
    print(content[:1000])
except:
    print("No index.php found")

ftp.quit()