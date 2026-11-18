import ftplib
import io

ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")

def check_perms(path):
    try:
        # Use simple directory listing to get permission info
        files = []
        ftp.cwd(path)
        ftp.retrlines("LIST", files.append)
        print(f"Permissions for {path}:")
        for f in files[:5]: # just the first few
            print(f"  {f}")
        ftp.cwd("..")
    except Exception as e:
        print(f"Error checking {path}: {e}")

print("Checking permissions...")
check_perms("/home/saileshk/public_html")
check_perms("/home/saileshk/omconnect/public")
check_perms("/home/saileshk/omconnect/storage")

ftp.quit()
