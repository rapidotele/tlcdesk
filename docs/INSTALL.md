# Installation Instructions

## Requirements
- PHP 7.4 or higher
- MySQL or MariaDB
- Web Server (Apache/Nginx)
- PHP Extensions: PDO, JSON, mbstring

## Steps for Shared cPanel Hosting

1. **Upload Files**
   - Upload the contents of the repository to your server.
   - Typically, put the contents of `public/` into your `public_html` (or a subdirectory).
   - Put the rest of the folders (`app`, `storage`, `install`, etc.) **outside** `public_html` if possible for security.
   - If you must put everything in `public_html`, ensure `.htaccess` protects sensitive directories.

2. **Configure Web Root**
   - Point your domain/subdomain to the `public/` directory.

3. **Run Installer**
   - Visit your site URL (e.g., `https://yourdomain.com/`).
   - You should be redirected to `/install`.
   - Follow the on-screen instructions to enter database credentials and create the admin account.

4. **Post-Install**
   - The installer will create a `storage/install.lock` file. Do not delete this unless you want to re-install.
   - Ensure `storage/` directory is writable by the web server.

## Local Development
1. Start PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```
2. Navigate to `http://localhost:8000`.
