import ftplib
import io

ftp = ftplib.FTP("r30.merodns.com", timeout=30)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect/public")

htaccess = """<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# php -- BEGIN cPanel-generated handler, do not edit
# Set the ea-php84 package as the default PHP programming language.
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php84___lsphp .php .php8 .phtml
</IfModule>
# php -- END cPanel-generated handler, do not edit
"""

import io
data = io.BytesIO(htaccess.encode('utf-8'))
ftp = ftplib.FTP("r30.merodns.com", timeout=30)
ftp.login("saileshk", "XNi4r(}FTr#H3J!f")
ftp.cwd("omconnect/public")

ftp.storbinary("STOR .htaccess", data)
print("Uploaded fixed .htaccess")
ftp.quit()