# PawCare — Pet Grooming System v2

## Folder Structure

```
pawcare/
├── index.php                  ← Landing page
├── login.php                  ← Login (User + Admin, single page)
├── register.php               ← Customer registration
├── logout.php
├── pawcare_db.sql             ← Database setup + seed data
│
├── config/
│   ├── database.php           ← DB connection (PDO)
│   └── auth.php               ← Session, role guards, helpers
│
├── includes/
│   ├── head.php               ← Shared <head> HTML partial
│   ├── sidebar_user.php       ← User sidebar nav
│   └── sidebar_admin.php      ← Admin sidebar nav
│
├── views/
│   ├── user/
│   │   ├── dashboard.php      ← User home / overview
│   │   ├── book.php           ← Book an appointment
│   │   ├── my-pets.php        ← Manage pets
│   │   ├── history.php        ← Appointment history
│   │   └── profile.php        ← Edit profile / change password
│   │
│   └── admin/
│       ├── dashboard.php      ← Admin overview / stats
│       ├── appointments.php   ← Manage all bookings
│       ├── services.php       ← Add / edit services
│       └── users.php          ← View all users
│
└── assets/
    ├── css/
    │   └── style.css          ← All custom styles
    ├── js/                    ← (add JS files here)
    └── images/
        ├── favicon.svg
        ├── avatars/           ← User profile photos (future)
        ├── pets/              ← Pet photos (future)
        ├── services/          ← Service photos (future)
        └── uploads/           ← User uploaded files (future)
```

## Setup Instructions

### 1. Database
Import `pawcare_db.sql` via phpMyAdmin or terminal:
```bash
mysql -u root -p < pawcare_db.sql
```

### 2. Configure DB Connection
Edit `config/database.php`:
```php
define('DB_NAME', 'pet_grooming');  // your DB name
define('DB_USER', 'root');          // your DB user
define('DB_PASS', '');              // your DB password
```

### 3. Place in XAMPP
Copy the entire `pawcare` folder to:
```
C:\xampp\htdocs\pawcare\
```

### 4. Open in Browser
```
http://localhost/pawcare/
http://localhost/pawcare/login.php
```

## Default Login Accounts

| Role  | Email               | Password   |
|-------|---------------------|------------|
| Admin | admin@pawcare.ph    | password   |
| User  | maria@email.com     | password   |

> **Important:** Change passwords immediately after first login!

## Tech Stack
- PHP 8+ (PDO, sessions)
- MySQL / MariaDB
- Bootstrap 5.3
- Vanilla JS
- Google Fonts (Cormorant Garamond + Jost)
- Unsplash (for photo placeholders)
