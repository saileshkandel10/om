import ftplib
import io
import os

ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")

# 1. Ensure omconnect/public exists
try:
    ftp.mkd("omconnect/public")
    print("Created omconnect/public")
except:
    print("omconnect/public already exists or error")

# 2. Copy files from public_html to omconnect/public
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

# Copy .htaccess
print("Copying .htaccess...")
copy_file_ftp(ftp, "public_html/.htaccess", "omconnect/public/.htaccess")

# Copy index.php
print("Copying index.php...")
copy_file_ftp(ftp, "public_html/index.php", "omconnect/public/index.php")

# Copy assets directory recursively
def copy_dir_ftp(ftp, src, dst):
    try:
        ftp.mkd(dst)
        print(f"Created directory {dst}")
    except:
        pass
    
    # Get files in source
    files = []
    try:
        ftp.cwd(src)
        files_list = []
        ftp.retrlines("LIST", lambda x: files.append(x))
        ftp.cwd("..")
    except:
        pass
    
    for f in files:
        parts = f.split()
        if len(parts) >= 9:
            fname = parts[-1]
            if fname in ['.', '..']:
                continue
            src_path = f"{src}/{fname}"
            dst_path = f"{dst}/{fname}"
            if f.startswith('d'):
                # Directory
                copy_dir_ftp(ftp, src_path, dst_path)
            else:
                copy_file_ftp(ftp, src_path, dst_path)

print("\nCopying assets directory...")
copy_dir_ftp(ftp, "public_html/assets", "omconnect/public/assets")

print("\nDone!")
ftp.quit()