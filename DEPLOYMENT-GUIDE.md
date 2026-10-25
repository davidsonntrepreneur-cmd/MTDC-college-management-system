# 🚀 Deployment Guide — Step by Step
### College Management System · Mother Theresa Degree College, Palamaner

There are two stages:
- **Stage A** — Run it on one computer (see it working today)
- **Stage B** — Put it on the internet (students/faculty use it from their phones)

---

## STAGE A — Run on your computer (XAMPP on Windows)

### Step 1 — Install XAMPP
1. Go to **https://www.apachefriends.org**
2. Download **XAMPP for Windows** (PHP 8.x version)
3. Run the installer → keep clicking **Next** (defaults are fine)
4. It installs to `C:\xampp`

### Step 2 — Copy the project
1. Download **mtdc-college-management.zip** from the workspace and extract it
2. Copy the whole **`mtdc`** folder into: `C:\xampp\htdocs\`
3. You should now have: `C:\xampp\htdocs\mtdc\index.php` ✔

### Step 3 — Start the servers
1. Open **XAMPP Control Panel** (Start Menu → XAMPP)
2. Click **Start** next to **Apache** → turns green
3. Click **Start** next to **MySQL** → turns green

### Step 4 — Create the database
1. Open your browser → go to **http://localhost/phpmyadmin**
2. Click the **Import** tab (top menu)
3. Click **Choose File** → select `C:\xampp\htdocs\mtdc\database\schema.sql`
4. Click **Import** (bottom of page)
5. You should see green success messages, and a new database **mtdc_cms** in the left sidebar ✔

### Step 5 — Open the system
1. Go to: **http://localhost/mtdc/**
2. Login with:
   | | |
   |---|---|
   | Username | `admin` |
   | Password | `admin@123` |
3. **Immediately** go to **Settings → Change Admin Password** 🔐

### Step 6 — First-time setup (in this order)
1. **Settings** → check the courses list, add/remove courses if needed
2. **Subjects** → add subjects for each course/year/semester
3. **Students** → add students (each gets a login: username = admission no, password = DOB as DDMMYYYY)
4. **Faculty** → add faculty (random password shown after saving — note it down!) → assign classes to each
5. **Fees** → add the fee structure for each course/year
6. **Timetable / Notices** → as needed

### Access from other computers/phones on the SAME college network
1. On the XAMPP computer, open Command Prompt → type `ipconfig` → note the **IPv4 Address** (e.g. `192.168.1.50`)
2. On any device on the same Wi-Fi/LAN, open: `http://192.168.1.50/mtdc/`
3. If it doesn't open, allow Apache through Windows Firewall:
   Control Panel → Windows Defender Firewall → Allow an app → tick **Apache HTTP Server** for Private networks

> 💡 Many colleges run exactly like this — one office PC as the server, everyone on the college Wi-Fi uses it. No hosting cost. The PC must stay on during college hours. Take backups (Step below).

---

## STAGE B — Put it on the internet (shared hosting)

Best option for a college: **shared hosting with cPanel** (₹100–300/month from Hostinger, GoDaddy, BigRock, MilesWeb, etc.). No server knowledge needed.

### Step 1 — Buy hosting + domain
1. Buy a plan that includes **PHP 8+ and MySQL** (almost all do)
2. Choose a domain, e.g. `mtdcpalamaner.in` (or use a subdomain like `portal.your-college-site.in` if the college already has a website)

### Step 2 — Upload the files
1. Log in to **cPanel** → open **File Manager**
2. Go to **public_html**
3. Click **Upload** → upload `mtdc-college-management.zip`
4. Right-click the zip → **Extract**
5. Two choices:
   - Keep folder → portal at `yourdomain.in/mtdc/`
   - OR move everything from `mtdc/` directly into `public_html/` → portal at `yourdomain.in/`

### Step 3 — Create the database (cPanel)
1. cPanel → **MySQL® Databases**
2. Create a database → e.g. `collmgmt` (cPanel prefixes it: `youruser_collmgmt`)
3. Create a **MySQL user** with a strong password
4. **Add User to Database** → select the user + database → tick **ALL PRIVILEGES** → Save
5. Note down all three: database name, username, password

### Step 4 — Import the schema
1. cPanel → **phpMyAdmin**
2. Click your database (`youruser_collmgmt`) in the left sidebar
3. **Import** tab → choose `database/schema.sql` from your computer
4. ⚠ If the import complains about `CREATE DATABASE`: open schema.sql in Notepad first and delete the first two lines (`CREATE DATABASE...` and `USE mtdc_cms;`), then import again
5. Click **Go** → green success ✔

### Step 5 — Connect the app to the database
Edit `config/db.php` (File Manager → right-click → Edit):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'youruser_collmgmt');   // your cPanel DB name
define('DB_USER', 'youruser_dbuser');     // your cPanel DB user
define('DB_PASS', 'your-strong-password');
```

### Step 6 — Enable HTTPS (free)
1. cPanel → **SSL/TLS Status** → **Run AutoSSL** (free Let's Encrypt certificate)
2. Force HTTPS: create/edit `.htaccess` in the app folder and add at the TOP:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Step 7 — Test & secure
1. Open `https://yourdomain.in/mtdc/` → login as `admin` / `admin@123`
2. **Change the admin password immediately**
3. Verify these URLs show **403 Forbidden** (protection working):
   - `yourdomain.in/mtdc/config/db.php`
   - `yourdomain.in/mtdc/database/schema.sql`

### Step 8 — Turn on email notifications (optional, recommended)
In `config/db.php`:
```php
define('MAIL_ENABLED', true);
define('MAIL_FROM', 'no-reply@yourdomain.in');  // use your real domain
```
PHP `mail()` works out of the box on most shared hosting. Now students automatically get login credentials, fee receipts and notices by email.

### Step 9 — SMS notifications (optional)
1. Sign up at an SMS gateway (e.g. **Fast2SMS**, **MSG91**) — note: for sending to parents in India, DLT registration of sender ID & templates is required by TRAI; the SMS provider guides you through it
2. In `config/db.php`: set `SMS_ENABLED` to `true` and paste your `SMS_API_KEY`
3. Absence alerts then go to guardians' phones automatically

---

## 🔄 Backups (IMPORTANT — do this weekly)

**Local (XAMPP):** open Command Prompt:
```
cd C:\xampp\mysql\bin
mysqldump -u root mtdc_cms > D:\backups\mtdc_%date:~-4%%date:~4,2%%date:~7,2%.sql
```

**Hosting (cPanel):** phpMyAdmin → select database → **Export** → Quick → Go (downloads a .sql file). Or cPanel → **Backup** → Download a Database Backup.

To restore: create empty DB → phpMyAdmin → Import the backup .sql.

---

## 🆘 Troubleshooting

| Problem | Fix |
|---|---|
| "Database connection failed" | Check the 4 values in `config/db.php` match your MySQL exactly |
| Apache won't start (XAMPP) | Port 80 busy (often Skype/IIS). XAMPP → Config → change to port 8080, then use `http://localhost:8080/mtdc/` |
| Blank/white page | Enable errors temporarily: add `ini_set('display_errors',1); error_reporting(E_ALL);` at top of `config/db.php`, reload, read the error, then remove |
| Import fails on hosting | Remove the `CREATE DATABASE` + `USE` lines from schema.sql (Step B-4) |
| Student can't log in | Username = admission no (exact), password = DOB as DDMMYYYY. Admin can Reset Pwd from the Students page |
| 403 on portal pages themselves | Make sure you edited only the app-root `.htaccess`, not the ones inside `config/`, `includes/`, `database/` (those SHOULD stay deny-all) |
| Emails not arriving | Check spam folder; verify MAIL_FROM uses your real domain; check `logs/notifications.log` for SENT/FAILED status |

---

## Quick reference — default logins

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin@123` → **change immediately** |
| Student | admission no | DOB as `DDMMYYYY` (or `Mtdc@123`) |
| Faculty | employee no | random `Mtdc@XXXX` shown when created |
