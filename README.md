# College Management System
### Mother Theresa Degree College, Palamaner

A PHP + MySQL web application with a **student portal** and an **admin dashboard**.

---

## Features

**Admin**
- Dashboard: student count, today's attendance, monthly fee collection, recent payments
- Student management: add / edit / delete, auto-created login accounts, password reset
- Attendance: mark class-wise per day (All Present / All Absent shortcuts), monthly summary with 75% highlighting
- Fees: define fee structure per course/year, record payments with auto receipt numbers, dues report
- Results: create exams, subject-wise marks entry per class
- Subjects, Timetable (weekly grid per course/year/section), Notices, Courses, admin password change

**Faculty portal** (new)
- Login with employee number (account auto-created by admin, random password shown/emailed)
- Dashboard: assigned classes, student counts, today's attendance status per class
- Mark attendance **only for assigned classes** (admin assigns classes in Admin → Faculty)
- Marks entry for assigned classes, restricted to that class's subjects
- View students of their classes with attendance %, profile & password change

**Student**
- Login with admission number
- Dashboard: attendance %, fee balance, latest exam average, notices
- My Attendance (daily + month-wise, below-75% warning)
- My Fees (structure, payments, balance) + **printable PDF receipt** per payment
- My Results (subject-wise marks, grade, printable memo)
- Class timetable, profile editing, change password

**PDF fee receipts** (new)
- Every payment gets a formatted A5 receipt page (`receipt.php?id=…`) with college letterhead, amount in words (Indian format), and signature lines
- Admin is taken to the receipt right after recording a payment; students can reprint any of their own receipts
- Use the browser's **Print → Save as PDF** — no extra libraries needed
- Access-controlled: students can only open their own receipts

**Email / SMS notifications** (new — disabled by default)
- Auto-notifies on: student/faculty account creation (login credentials), fee payment received (receipt no.), student absence (to guardian's phone), and optional notice email broadcast to all students
- Enable email: set `MAIL_ENABLED = true` in `config/db.php` (uses PHP `mail()`, works on most hosting)
- Enable SMS: set `SMS_ENABLED = true` + your gateway API key (pre-wired for Fast2SMS-style APIs; adjust `includes/notify.php` for your provider)
- Every attempt (sent/skipped/failed) is logged to `logs/notifications.log`

**Security:** bcrypt password hashing, PDO prepared statements (SQL-injection safe), CSRF tokens on all forms, session-based role access control (admin / faculty / student), faculty restricted to assigned classes only, HTML output escaping.

---

## Requirements
- PHP 8.0+ (works on XAMPP / WAMP / LAMP)
- MySQL 5.7+ / MariaDB
- Apache (or `php -S` for quick testing)

## Installation (XAMPP example)

1. Copy the `mtdc` folder into `C:\xampp\htdocs\` (so you have `htdocs/mtdc/`).
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Import the database:
   - Open http://localhost/phpmyadmin → **Import** → choose `database/schema.sql` → Go.
   - (or CLI: `mysql -u root < database/schema.sql`)
4. If your MySQL user/password differ from `root` / empty, edit `config/db.php`.
5. Open **http://localhost/mtdc/**

## Default Login

| Role  | Username | Password  |
|-------|----------|-----------|
| Admin | `admin`  | `admin@123` |

> ⚠ **Change the admin password immediately** (Settings → Change Admin Password).

### Student logins
Created automatically when the admin adds a student:
- **Username** = admission number
- **Password** = date of birth as `DDMMYYYY` (or `Mtdc@123` if DOB was not entered)

Students should change their password after first login (My Profile → Change Password).

### Faculty logins
Created automatically when the admin adds a faculty member (Admin → Faculty):
- **Username** = employee number
- **Password** = random `Mtdc@XXXX`, shown on screen after saving (and emailed/SMSed if notifications are enabled)

Then assign classes to the faculty member on the same page — they can only mark attendance / enter marks for assigned classes.

### Upgrading an existing installation
If you already imported the old `schema.sql`, run `database/upgrade_v2.sql` once to add the faculty tables. Fresh installs just import `schema.sql` (it includes everything).

## Configuration
Edit `config/db.php`:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — database connection
- `CURRENT_ACADEMIC_YEAR` — e.g. `2026-27` (used for fee dues and defaults)

## Folder Structure
```
mtdc/
├── index.php              # login page (all roles)
├── logout.php
├── receipt.php            # printable PDF fee receipt
├── assets/style.css
├── config/db.php          # DB + notification settings (edit this)
├── database/
│   ├── schema.sql         # fresh install — import into MySQL
│   └── upgrade_v2.sql     # upgrade an old install (adds faculty)
├── includes/              # auth, notify (email/SMS), header, footer
├── logs/                  # notifications.log (auto-created, protected)
├── admin/                 # dashboard, students, faculty, attendance,
│                          # fees, results, subjects, timetable,
│                          # notices, settings
├── faculty/               # dashboard, attendance, marks, students, profile
└── student/               # dashboard, attendance, fees, results,
                           # timetable, profile
```

## Going live (recommended before real deployment)
- Serve over **HTTPS** and set `session.cookie_secure = 1`
- Change DB credentials from root/empty to a dedicated MySQL user
- Take regular MySQL backups (`mysqldump mtdc_cms > backup.sql`)
