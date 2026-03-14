# LabTech Office — Lost & Found Management System

A complete multi-campus/branch lost and found management system built with native PHP, MySQL, Bootstrap 5, and jQuery.

---

## Features

- **Multi-Campus/Branch Support** — Super Admin, Branch Managers, and Staff with branch-scoped access
- **Found Item Logging** — Log items with photo, category, location, description template, and storage info
- **Lost Item Reports** — Accept and track lost item reports from the public (with week/month filter)
- **Item Matching** — Match found items to lost reports with confirmation workflow
- **Claim Processing** — Full verification workflow with proof of ownership, staff questions, and confirmation checklist
- **Dashboard** — Charts and stats (Chart.js): monthly trend, category breakdown, branch overview
- **PDF & CSV Export** — Printable reports with weekly/monthly quick-print buttons
- **QR Code** — Auto-generated QR code for the Guest Portal on the Reports page
- **Activity Logging** — All user actions are tracked
- **Campus Customization** — Super Admin can configure campus type, theme color, and access controls per branch

---

## Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled (or Nginx)
- XAMPP / WAMP / LAMP / Laragon

---

## Installation

### 1. Copy Files
Place the `lost-found` folder in your web root:
```
XAMPP: C:/xampp/htdocs/lost-found
WAMP:  C:/wamp/www/lost-found
Linux: /var/www/html/lost-found
```

### 2. Create Database
Open phpMyAdmin or MySQL CLI and run:
```sql
SOURCE /path/to/lost-found/config/schema.sql;
```

### 3. Configure Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password
define('DB_NAME', 'lost_found_db');
```

### 4. Set Base URL
Edit `config/config.php`:
```php
define('BASE_URL', 'http://localhost/lost-found');
```

### 5. Enable mod_rewrite
In XAMPP: httpd.conf → uncomment `LoadModule rewrite_module`
Also ensure `AllowOverride All` is set for your htdocs directory.

### 6. Set Upload Permissions
```bash
chmod 755 public/uploads/items/
```
On Windows/XAMPP this is usually not needed.

### 7. Access the System
Open: `http://localhost/lost-found`

---

## Default Accounts

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@lostandfound.com | Admin@1234 |
| Branch Manager (Main) | main.manager@lostandfound.com | Manager@1234 |
| Branch Manager (North) | north.manager@lostandfound.com | Manager@1234 |
| Staff (Main) | main.staff@lostandfound.com | Staff@1234 |

**Change all passwords after first login!**

---

## Folder Structure

```
lost-found/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── FoundItemController.php
│   │   ├── LostReportController.php
│   │   ├── MatchController.php
│   │   ├── ClaimController.php
│   │   ├── BranchController.php
│   │   ├── UserController.php
│   │   ├── GuestController.php
│   │   └── ReportController.php
│   └── views/
│       ├── layouts/
│       │   ├── header.php
│       │   └── footer.php
│       ├── auth/login.php
│       ├── dashboard/index.php
│       ├── items/          (found & lost views/forms)
│       ├── branches/
│       ├── users/
│       ├── guest/
│       └── reports/
├── config/
│   ├── config.php
│   ├── database.php
│   └── schema.sql
├── includes/
│   ├── auth.php
│   └── helpers.php
├── public/
│   └── uploads/items/
├── .htaccess
├── index.php
├── reset_password.php   ← DELETE after first use!
└── README.md
```

---

## User Role Permissions

| Feature | Super Admin | Branch Manager | Staff |
|---------|------------|----------------|-------|
| All Campuses/Branches | ✅ | ❌ Own only | ❌ Own only |
| Manage Campuses | ✅ | ❌ | ❌ |
| Manage All Users | ✅ | ❌ | ❌ |
| Manage Branch Users | ✅ | ✅ | ❌ |
| Found Items | ✅ | ✅ | ✅ |
| Lost Reports | ✅ | ✅ | ✅ |
| Item Matching | ✅ | ✅ | ✅ |
| Claims | ✅ | ✅ | ✅ |
| Reports/Export | ✅ | ✅ | ✅ |

---

## PDF Export
The PDF export uses browser print functionality (no additional libraries required).
Open the report → Click "Print / Save PDF" → Save as PDF using your browser.

---

## Security Notes
- Passwords hashed with bcrypt (cost 12)
- All inputs escaped with `htmlspecialchars()`
- Prepared statements for all DB queries
- Branch-scoped access enforcement on all controllers
- Session-based authentication

---

## Public Portal (React Frontend)

The `portal.php` file is the **public-facing landing page** for the Lost & Found system. It is a React-based single-page UI that:

- Serves as the homepage at `http://localhost/lost-found/`
- Allows students/guests to browse found items without logging in
- Provides a login screen; **student/guest roles** stay in the React portal, while **staff/admin roles** are redirected to the PHP backend at `?page=login`
- Pulls `BASE_URL` from `config/config.php` automatically

### Navigation Flow

```
/ (root)  →  portal.php  (React public portal)
             ├── Browse Found Items  →  stays in portal
             ├── Student Login       →  stays in portal
             └── Staff/Admin Login   →  redirects to /?page=login (PHP backend)

/?page=login  →  PHP backend login  (Admin/Staff system)
              └── "Back to Public Portal" link  →  portal.php
```
