# Human Resource Management System (HRMS) Portal

A university-level Database Management Systems (DBMS) project. This repository features a relational database backend designed according to **3rd Normal Form (3NF)** principles and a dynamic web application frontend built with **PHP, MySQL, and Bootstrap 5**.

---

## 🛠️ Technology Stack

* **Database Engine:** MySQL 8.0+ (3NF Relational Architecture)
* **Backend Application:** PHP 8.x
* **Frontend UI:** HTML5, CSS3, Bootstrap 5
* **Authentication:** Session-based RBAC with BCRYPT password encryption

---

## 🔑 Default Credentials & Access Roles

All initial seed accounts are populated automatically upon first launch.

| Role | Email | Password | Access Privileges |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin@hrms.com` | `123456` | User management, full CRUD, restore & hard delete soft-deleted items |
| **Department Manager** | `dept@hrms.com` | `123456` | Department & Employee management, soft-deletion |
| **Employee Staff** | `staff@hrms.com` | `123456` | View attendance logs and individual records |

*(Interactive quick-fill login buttons are available on `login.php` for rapid testing).*

---

## 📊 Database Schema Design (3NF)

The database schema is designed in 3rd Normal Form to eliminate redundancy and maintain strict referential integrity through Foreign Keys.

### 1. `users`
System login accounts and access roles.
- `id` (INT, Primary Key, AUTO_INCREMENT)
- `name` (VARCHAR(100), NOT NULL)
- `email` (VARCHAR(100), NOT NULL, UNIQUE)
- `password` (VARCHAR(255), NOT NULL) — BCRYPT Hashed
- `role` (ENUM('Admin','Department','Employee'), Default 'Employee')
- `created_at` (TIMESTAMP)

### 2. `departments`
Organizational structure details.
- `id` (INT, Primary Key, AUTO_INCREMENT)
- `dept_name` (VARCHAR(100), NOT NULL, UNIQUE)
- `is_deleted` (TINYINT(1), Default 0) — Soft-delete flag
- `created_at` (TIMESTAMP)

### 3. `employees`
Master directory table for staff profiles.
- `id` (INT, Primary Key, AUTO_INCREMENT)
- `first_name` (VARCHAR(50), NOT NULL)
- `last_name` (VARCHAR(50), NOT NULL)
- `email` (VARCHAR(100), NOT NULL, UNIQUE)
- `salary` (DECIMAL(10,2), NOT NULL, Default 0.00)
- `dept_id` (INT, Foreign Key ➡️ `departments.id` ON DELETE CASCADE)
- `hire_date` (DATE, NOT NULL)
- `is_deleted` (TINYINT(1), Default 0) — Soft-delete flag

### 4. `attendance_logs`
Operational daily clocking metrics.
- `id` (INT, Primary Key, AUTO_INCREMENT)
- `emp_id` (INT, Foreign Key ➡️ `employees.id` ON DELETE CASCADE)
- `log_date` (DATE, NOT NULL)
- `log_time` (TIME, NOT NULL)
- `status` (ENUM('Present','Absent','Leave'), Default 'Present')
- `is_deleted` (TINYINT(1), Default 0) — Soft-delete flag

---

## 📁 Project Structure

```text
hrms-project/
│
├── config/
│   └── db.php           # Database connection & zero-config auto-migrator
│
├── includes/
│   ├── header.php       # Shared navigation header & setup checks
│   └── footer.php       # Shared footer layout
│
├── index.php            # Main dashboard with card statistics
├── login.php            # Login portal with quick-fill demo buttons
├── logout.php           # Session cleanup & logout handler
├── users.php            # System Admin user account management
├── departments.php      # Department management & soft-delete controls
├── employees.php        # Employee directory & profile registration
└── attendance.php       # Attendance tracker and daily logs