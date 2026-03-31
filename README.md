# 🐾 PawCare — Pet Grooming Management System v2

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Status](https://img.shields.io/badge/Status-Development-orange?style=for-the-badge)

**A modern, full-stack web application for pet grooming businesses. Streamline appointments, manage pet profiles, and handle services with ease.**

</div>

---

## 📺 Project Demo


https://github.com/user-attachments/assets/d187b269-4464-43fd-bb01-ca0bedc3cf6a



---

## 🚀 Key Features

### 👤 Customer Side
* **Online Booking:** Real-time scheduling for pet grooming services.
* **Pet Management:** Create and manage multiple pet profiles (Name, Breed, Age).
* **Booking History:** Track past and upcoming appointments.
* **Secure Authentication:** User registration and login with role-based access.

### 🛡️ Admin Dashboard
* **Appointment Management:** Approve, decline, or update customer bookings.
* **Service Catalog:** Add, edit, or remove grooming packages and prices.
* **User Oversight:** View and manage registered customers.
* **Quick Stats:** Overview of total appointments and active users.

---

## 📁 Project Structure

```bash
pawcare/
├── config/              # DB connection (PDO) & Auth guards
├── includes/            # Shared HTML partials (Heads, Sidebars)
├── views/               
│   ├── user/            # Customer-specific pages
│   └── admin/           # Management tools (Services, Appointments)
├── assets/              # UI/UX (CSS, JS, Images, Favicons)
└── index.php            # Professional Landing Page
🛠️ Installation Guide1. Database SetupOpen phpMyAdmin (http://localhost/phpmyadmin).Create a new database named pet_grooming.Import the pawcare_db.sql file.2. ConfigurationEdit config/database.php and update your credentials:PHPdefine('DB_NAME', 'pet_grooming');
define('DB_USER', 'root'); 
define('DB_PASS', ''); 
3. Local Deployment
Copy the folder to your XAMPP htdocs:
C:\xampp\htdocs\pawcare\
Then access via: http://localhost/pawcare/

🔑 Default Credentials
Role           │ Email                     │  Password
Administrator  │ admin@pawcare.ph          │  password
Customer       │ campusthrift77@gmail.com  │  password
[!WARNING]
Security Notice: Please change the default passwords immediately after your first login.

🎨 Tech Stack & Tools
Backend: PHP 8+ (PDO for secure DB interactions)
Database: MySQL / MariaDB
Frontend: Bootstrap 5.3, Vanilla JavaScript
Typography: Jost & Cormorant Garamond
Design: Industrial Aesthetic with Glassmorphism accents

👨‍💻 Author
🆂🆈🅽🆃🆄🆇🆉 - Web Developer
