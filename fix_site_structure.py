import ftplib
import io

ftp = ftplib.FTP("r30.merodns.com", timeout=30)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")

# 1. Create omconnect/public directory
print("Creating omconnect/public directory...")
try:
    ftp.mkd("omconnect/public")
    print("Created omconnect/public directory")
except Exception as e:
    print(f"Directory might already exist or error: {e}")

# 2. Copy necessary files from public_html to omconnect/public
# We need to copy index.php and .htaccess from public_html to omconnect/public

def copy_file_ftp(ftp, src_path, dst_path):
    """Copy file via FTP by downloading and re-uploading"""
    try:
        data = io.BytesIO()
        ftp.retrbinary(f"RETR {src_path}", data.write)
        content = data.getvalue()
        data = io.BytesIO(content)
        ftp.storbinary(f"STOR {dst_path}", data)
        print(f"  Copied {src_path} -> {dst_path}")
        return True
    except Exception as e:
        print(f"Error copying {src_path}: {e}")
        return False

# Copy .htaccess
print("\nCopying .htaccess...")
copy_file_ftp(ftp, "public_html/.htaccess", "omconnect/public/.htaccess")

# Copy index.php
print("Copying index.php...")
copy_file_ftp(ftp, "public_html/index.php", "omconnect/public/index.php")

# Copy .htaccess from public_html to omconnect/public (already done above)
# Also need to copy assets directory
print("\nCopying assets directory...")
try:
    # Create assets directory
    ftp.mkd("omconnect/public/assets")
    print("Created assets directory")
except:
    pass

# Copy assets directory contents
ftp.cwd("public_html/assets")
assets_dirs = []
ftp.retrlines("LIST", lambda x: print(x) or None)
print("Assets dir listing attempted")

# Let's try a different approach - copy the entire assets directory
ftp.cwd("public_html")
dirs = []
ftp.retrlines("LIST", lambda x: print(x) or None)

ftp.quit()