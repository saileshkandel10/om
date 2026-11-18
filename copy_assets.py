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

# 3. Copy assets directory - use simple approach
print("\nCopying assets directory...")
# Create assets directory in target
try:
    ftp.mkd("omconnect/public/assets")
    print("Created omconnect/public/assets")
except:
    print("assets directory already exists")

# List assets directories
def list_dir(ftp, path):
    items = []
    try:
        ftp.cwd(path)
        items = []
        ftp.retrlines("LIST", lambda x: x)
        ftp.retrlines("LIST", lambda x: items.append(x))
        ftp.cwd("..")
    except:
        pass
    return items

# Copy assets subdirectories
assets_subdirs = ["css", "fonts", "images", "js", "libs"]
for subdir in ["css", "fonts", "images", "js", "libs"]:
    src = f"public_html/assets/{subdir}"
    dst = f"omconnect/public/assets/{subdir}"
    try:
        ftp.mkd(f"omconnect/public/assets/{subdir}")
        print(f"Created omconnect/public/assets/{subdir}")
    except:
        print(f"  {subdir} already exists")
    
    # List files in source subdir
    try:
        ftp.cwd(f"public_html/assets/{subdir}")
        items = []
        ftp.retrlines("LIST", lambda x: None)
        items = []
        ftp.retrlines("LIST", lambda x: None)
        # Use a different approach
        items = []
        ftp.retrlines("LIST", lambda x: items.append(x))
        
        for item in items:
            parts = item.split()
            if len(parts) >= 9:
                name = parts[-1]
                if name in ['.', '..']:
                    continue
                src_path = f"public_html/assets/{subdir}/{name}"
                dst_path = f"omconnect/public/assets/{subdir}/{name}"
                if item.startswith('d'):
                    # directory
                    try:
                        ftp.mkd(f"omconnect/public/assets/{subdir}/{name}")
                        print(f"  Created dir: omconnect/public/assets/{subdir}/{name}")
                    except:
                        pass
                else:
                    # file
                    try:
                        data = io.BytesIO()
                        ftp.retrbinary(f"RETR {subdir}/{name}", data.write)
                        content = data.getvalue()
                        data = io.BytesIO(content)
                        ftp.storbinary(f"STOR omconnect/public/assets/{subdir}/{name}", data)
                        print(f"  Copied {subdir}/{name}")
                    except Exception as e:
                        print(f"Error copying {subdir}/{name}: {e}")
        ftp.cwd("..")
    except Exception as e:
        print(f"Error copying {subdir}: {e}")

print("\nDone!")
ftp.quit()