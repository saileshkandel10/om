import ftplib
import io

ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect/storage/logs")

data = io.BytesIO()
ftp.retrbinary("RETR laravel.log", data.write)
content = data.getvalue().decode('utf-8', errors='ignore')
print("Last 1000 chars of laravel.log:")
print(content[-1000:])
ftp.quit()