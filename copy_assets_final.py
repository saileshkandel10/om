import ftplib
import io
import os

ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")

def copy_file(ftp, src, dst):
    try:
        data = io.BytesIO()
        ftp.retrbinary(f"RETR {src}", data.write)
        content = data.getvalue()
        data = io.BytesIO(content)
        ftp.storbinary(f"STOR {dst}", data)
        print(f"  Copied {src} -> {dst}")
        return True
    except Exception as e:
        print(f"Error copying {src}: {e}")
        return False

def list_dir(ftp, path):
    items = []
    try:
        original_dir = ftp.pwd()
        ftp.cwd(path)
        items = []
        ftp.retrlines("LIST", items.append)
        ftp.cwd("..")
        return items
    except Exception as e:
        print(f"Error listing {path}: {e}")
        return []

# 1. Ensure omconnect/public exists
try:
    ftp.mkd("omconnect/public")
    print("Created omconnect/public")
except:
    print("omconnect/public already exists")

# 2. Copy .htaccess and index.php
def copy_file(ftp, src, dst):
    return copy_file(ftp, src, dst)

print("Copying .htaccess...")
copy_file(ftp, "public_html/.htaccess", "omconnect/public/.htaccess")

print("Copying index.php...")
copy_file(ftp, "public_html/index.php", "omconnect/public/index.php")

# 3. Copy assets directory - use simple approach
# First, let's list the assets directory
ftp.cwd("public_html/assets")
items = []
ftp.retrlines("LIST", lambda x: None)
items = []
ftp.retrlines("LIST", lambda x: None)
# Use a different approach
items = []
ftp.retrlines("LIST", items.append)

print("Assets directory contents:")
for item in items:
    parts = item.split()
    if len(parts) >= 9:
        name = parts[-1]
        if name in ['.', '..']:
            continue
        if item.startswith('d'):
            print(f"  Dir: {name}")
        else:
            print(f"  File: {name}")

ftp.quit()