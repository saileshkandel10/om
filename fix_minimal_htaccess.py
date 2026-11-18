import ftplib
import io

ftp = ftplib.FTP("r30.merodns.com", timeout=60)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("public_html")

htaccess = """<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
"""

data = io.BytesIO(htaccess.encode('utf-8'))
ftp.storbinary("STOR .htaccess", data)
print("Uploaded minimal .htaccess")
ftp.quit()