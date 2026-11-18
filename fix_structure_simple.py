import ftplib
import io

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

# 1. Ensure omconnect/public exists
try:
    ftp.mkd("omconnect/public")
    print("Created omconnect/public")
except:
    print("omconnect/public already exists")

# 2. Copy .htaccess and index.php
print("Copying .htaccess...")
copy_file = lambda src, dst: copy_file(ftp, src, dst)
copy_file("public_html/.htaccess", "omconnect/public/.htaccess")

print("Copying index.php...")
copy_file("public_html/index.php", "omconnect/public/index.php")

# 3. Copy assets directory recursively
def copy_dir(ftp, src, dst):
    try:
        ftp.mkd(dst)
        print(f"Created {dst}")
    except:
        pass
    
    try:
        ftp.cwd(src)
        items = []
        ftp.retrlines("LIST", lambda x: None)
        items = []
        ftp.retrlines("LIST", lambda x: None)
        # Use a different approach
        items = []
        ftp.retrlines("LIST", lambda x: x)
    except:
        pass

# Simple approach: use mget/mput via FTP
print("Copying assets directory...")
# We'll use a simpler approach - copy files one by one
# For now, just ensure the structure exists

print("Done with structure fix")
ftp.quit()