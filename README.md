# Human Resource Management System (HRMS) Portal

A lightweight, multi-role Human Resource Management System built with PHP, MySQL, and Bootstrap 5. It features role-based access control, automatic database initialization, soft-deletion handling, employee management, department tracking, and attendance logging.

---

## 🛠️ Features

* **Auto-Database Setup:** Automatically detects if tables exist and sets up schema and seed data on first load.
* **Role-Based Access Control (RBAC):**
  * **System Admin:** Full access to manage users, departments, employees, attendance, restore soft-deleted items, or execute permanent hard deletes.
  * **Department Manager:** Access to departments, employees, attendance, and soft-deletion operations.
  * **Employee:** Standard attendance access.
* **Soft Delete & Permanent Erasure:** Admin users can view soft-deleted records, restore them, or permanently delete them.
* **Interactive Quick Login:** Built-in demo credentials switcher on the login page for rapid testing.

---

## 📁 Project Structure

```text
hrms-project/
│
├── config/
│   └── db.php           # Database configuration & auto-migration engine
│
├── includes/
│   ├── header.php       # Shared navigation header & setup enforcer
│   └── footer.php       # Shared page footer & script imports
│
├── index.php            # Main dashboard
├── login.php            # Login page with interactive role switchers
├── logout.php           # Session destroyer & redirect
├── users.php            # Admin user management panel
├── departments.php      # Department directory & management
├── employees.php        # Employee records & registration
└── attendance.php       # Daily attendance log system