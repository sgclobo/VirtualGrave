# In Loving Memory — Installation Guide

A peaceful digital memorial platform for Hercio Maria da Neves Campos (1945–2024).

---

## Requirements

| Requirement | Minimum Version |
|-------------|----------------|
| PHP         | 8.0+           |
| MySQL       | 5.7+ / MariaDB 10.4+ |
| Web Server  | Apache 2.4+ or Nginx |
| PHP Extensions | PDO, PDO_MySQL, GD, fileinfo |

---

## Step 1 — Download & Extract

Place the `memorial/` folder in your web server's document root:

```
/var/www/html/memorial/      (Apache/Linux)
C:\xampp\htdocs\memorial\    (XAMPP/Windows)
/Applications/MAMP/htdocs/memorial/  (MAMP/Mac)
```

---

## Step 2 — Create Database

1. Open phpMyAdmin or your MySQL client
2. Create a new database:
   ```sql
   CREATE DATABASE memorial_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Select the database and import:
   ```
   File: memorial/database.sql
   ```
   This creates all tables and inserts sample data.

---

## Step 3 — Configure the Application

Edit `memorial/includes/config.php`:

```php
define('DB_HOST', 'localhost');     // MySQL host
define('DB_NAME', 'memorial_db');   // Database name (created above)
define('DB_USER', 'root');          // MySQL username
define('DB_PASS', '');              // MySQL password

define('SITE_URL', 'http://localhost/memorial');  // Your site URL (no trailing slash)
```

**For production**, also change:
- `SITE_URL` to your actual domain (e.g. `https://yourdomain.com`)
- Use a dedicated MySQL user (not root)

---

## Step 4 — Set File Permissions

The `uploads/` directory must be writable by the web server:

**Linux/Mac:**
```bash
chmod -R 755 memorial/
chmod -R 777 memorial/uploads/
```

**Windows (XAMPP):** No changes needed — writable by default.

---

## Step 5 — Apache Configuration (if needed)

If you get 404 errors, enable `mod_rewrite`:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Ensure `AllowOverride All` is set in your Apache VirtualHost.

---

## Step 6 — First Login

### Admin Panel
- URL: `http://localhost/memorial/admin/login.php`
- Email: `admin@memorial.com`
- Password: `admin123`

⚠️ **IMPORTANT:** Change the admin password immediately after first login!

### Member Area
- URL: `http://localhost/memorial/pages/login.php`
- Email: `maria@example.com`
- Password: `password123`

(Sample member from the database seed data)

---

## Step 7 — PWA Setup (Optional)

For PWA functionality to work:

1. The site **must be served over HTTPS** (required for Service Workers)
2. Add the following to your Apache VirtualHost or `.htaccess` for HTTPS redirect:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```
3. Add app icons:
   - `memorial/assets/images/icon-192.png` (192×192 px)
   - `memorial/assets/images/icon-512.png` (512×512 px)

---

## Step 8 — Customize Content

### In the Admin Panel (`/admin/`):
1. **Settings** — Update deceased name, dates, memorial quote, footer message
2. **Biography** — Edit biography sections
3. **Timeline** — Add/edit life events
4. **Gallery** — Upload photos and videos
5. **Flowers Catalog** — Manage flower types
6. **Candles Catalog** — Manage candle types

### Portrait Photo
Replace the placeholder with the actual portrait:
- Upload a portrait photo
- Update `includes/header.php` and `index.php` to reference the image path

### Ambient Music
1. Upload an `.mp3` file to `assets/` or a CDN
2. In Admin → Settings, paste the URL in **Ambient Music URL**

---

## Folder Structure

```
memorial/
├── admin/                  ← Admin panel
│   ├── includes/           ← Admin header/footer
│   │   ├── header.php
│   │   └── footer.php
│   ├── pages/              ← Admin sub-pages
│   │   ├── members.php
│   │   ├── moderate.php
│   │   ├── gallery.php
│   │   ├── biography.php
│   │   ├── timeline.php
│   │   ├── settings.php
│   │   ├── flowers.php
│   │   └── candles.php
│   ├── index.php           ← Dashboard
│   ├── login.php
│   └── logout.php
├── api/                    ← AJAX endpoints
│   ├── deposit_flower.php
│   ├── light_candle.php
│   ├── submit_prayer.php
│   └── submit_testimony.php
├── assets/
│   ├── css/
│   │   ├── main.css        ← Full design system
│   │   └── animations.css  ← Keyframes & scroll reveals
│   ├── js/
│   │   ├── main.js         ← Memorial interactions
│   │   └── petals.js       ← Floating petals effect
│   └── images/             ← Static images & icons
├── includes/               ← Shared PHP modules
│   ├── config.php          ← DB + constants
│   ├── functions.php       ← Auth, CSRF, helpers
│   ├── header.php          ← Site navigation
│   └── footer.php          ← Site footer
├── pages/                  ← Public pages
│   ├── biography.php
│   ├── gallery.php
│   ├── timeline.php
│   ├── memorial.php        ← Virtual grave
│   ├── prayers.php
│   ├── testimonies.php
│   ├── guestbook.php
│   ├── register.php
│   ├── login.php
│   └── logout.php
├── uploads/                ← User-uploaded files
│   ├── avatars/
│   ├── gallery/
│   ├── testimonies/
│   ├── flowers/
│   └── candles/
├── database.sql            ← Full schema + sample data
├── index.php               ← Homepage
├── manifest.json           ← PWA manifest
├── sw.js                   ← Service worker
├── offline.html            ← Offline fallback
└── install.md              ← This file
```

---

## Security Checklist

Before going live, verify:

- [ ] Admin password changed from default
- [ ] `DB_PASS` is a strong password
- [ ] Site runs on HTTPS
- [ ] `uploads/` directory has no PHP execution (add `.htaccess`):
  ```apache
  <FilesMatch "\.php$">
      Deny from all
  </FilesMatch>
  ```
- [ ] Error display disabled in PHP (`display_errors = Off`)
- [ ] `config.php` is not publicly accessible

---

## Troubleshooting

**Blank page / errors:**
- Check `error_log` for PHP errors
- Verify DB credentials in `config.php`
- Ensure PDO_MySQL extension is enabled

**Upload failures:**
- Check `uploads/` is writable (`chmod 777`)
- Check `upload_max_filesize` and `post_max_size` in `php.ini`

**Candles/Flowers not appearing after click:**
- Open browser console (F12) and check for JS errors
- Verify CSRF meta tag is present in page `<head>`

**Admin panel shows "Access denied":**
- Ensure you logged in at `/admin/login.php` (not the member login)

---

## Credits

Built with love, care and reverence.  
"In memory of Hercio Maria da Neves Campos — love continues."

Technologies: PHP 8, MySQL, Bootstrap 5, Vanilla JavaScript, CSS3 animations.
