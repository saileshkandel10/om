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
        ftp.cwd(original_dir)
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

copy_file(ftp, "public_html/.htaccess", "omconnect/public/.htaccess")
print("Copied .htaccess")

copy_file(ftp, "public_html/index.php", "omconnect/public/index.php")
print("Copied index.php")

# 3. Copy assets directory - use simple approach
print("\nCopying assets directory...")

def list_dir(ftp, path):
    items = []
    try:
        original_dir = ftp.pwd()
        ftp.cwd(path)
        items = []
        ftp.retrlines("LIST", items.append)
        ftp.cwd(original_dir)
        return items
    except Exception as e:
        print(f"Error listing {path}: {e}")
        return []

# 3. Copy assets directory
print("\nCopying assets directory...")

def copy_dir(ftp, src, dst):
    try:
        ftp.mkd(dst)
        print(f"Created {dst}")
    except:
        pass
    
    items = list_dir(ftp, src)
    for item in items:
        parts = item.split()
        if len(parts) < 9:
            continue
        name = parts[-1]
        if name in ['.', '..']:
            continue
        src_path = f"{src}/{name}"
        dst_path = f"{dst}/{name}"
        if item.startswith('d'):
            copy_dir(ftp, src_path, dst_path)
        else:
            copy_file(ftp, src_path, dst_path)

def list_dir(ftp, path):
    items = []
    try:
        original_dir = ftp.pwd()
        ftp.cwd(path)
        items = []
        ftp.retrlines("LIST", items.append)
        ftp.cwd(original_dir)
        return items
    except Exception as e:
        print(f"Error listing {path}: {e}")
        return []

# Copy assets directory
print("\nCopying assets directory...")

# Get list of asset subdirectories
asset_subdirs = ["css", "fonts", "images", "js", "libs"]

for subdir in ["css", "fonts", "images", "js", "libs"]:
    src = f"public_html/assets/{subdir}"
    dst = f"omconnect/public/assets/{subdir}"
    
    try:
        ftp.mkd(f"omconnect/public/assets/{subdir}")
        print(f"Created omconnect/public/assets/{subdir}")
    except:
        print(f"  {subdir} already exists")
    
    # List files in source subdir
    items = []
    try:
        ftp.cwd(f"public_html/assets/{subdir}")
        items = []
        ftp.retrlines("LIST", items.append)
        ftp.cwd("..")
        ftp.cwd("..")
    except:
        items = []
    
    for item in items:
        parts = item.split()
        if len(parts) < 9:
            continue
        name = parts[-1]
        if name in ['.', '..']:
            continue
        if item.startswith('d'):
            try:
                ftp.mkd(f"omconnect/public/assets/{subdir}/{name}")
                print(f"  Created dir: omconnect/public/assets/{subdir}/{name}")
            except:
                pass
        else:
            src = f"public_html/assets/{subdir}/{name}"
            dst = f"omconnect/public/assets/{subdir}/{name}"
            try:
                data = io.BytesIO()
                ftp.retrbinary(f"RETR {subdir}/{name}", data.write)
                content = data.getvalue()
                data = io.BytesIO(content)
                ftp.storbinary(f"STOR omconnect/public/assets/{subdir}/{name}", data)
                print(f"  Copied {subdir}/{name}")
            except Exception as e:
                print(f"Error copying {subdir}/{name}: {e}")

print("\nDone!")
ftp.quit()