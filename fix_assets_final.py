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
    files = []
    try:
        ftp.cwd(path)
        files = []
        ftp.retrlines("LIST", lambda x: files.append(x))
        ftp.cwd("..")
        return files
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
print("Copying .htaccess...")
copy_file(ftp, "public_html/.htaccess", "omconnect/public/.htaccess")

print("Copying index.php...")
copy_file_ftp(ftp, "public_html/index.php", "omconnect/public/index.php")

# 3. Copy assets directory recursively
def copy_dir(ftp, src, dst):
    try:
        ftp.mkd(dst)
    except:
        pass
    try:
        ftp.cwd(src)
        items = []
        ftp.retrlines("LIST", lambda x: None)  # Just to get listing
        items = []
        ftp.retrlines("LIST", lambda x: items.append(x))
        ftp.cwd("..")
        
        for item in items:
            parts = item.split()
            if len(parts) >= 9:
                name = parts[-1]
                if name in ['.', '..']:
                    continue
                src_path = f"{src}/{name}"
                dst_path = f"{dst}/{name}"
                if item.startswith('d'):
                    copy_dir(ftp, src_path, dst_path)
                else:
                    copy_file_ftp(ftp, src_path, dst_path)
    except Exception as e:
        print(f"Error copying dir {src}: {e}")

print("\nCopying assets directory...")
copy_dir(ftp, "public_html/assets", "omconnect/public/assets")

print("\nDone!")
ftp.quit()