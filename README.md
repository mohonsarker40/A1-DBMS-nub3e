# HR Management System (HRMS) 

A university-level Database Management Systems (DBMS) project. This repository features a scalable relational database backend implemented in **Oracle SQL** and a responsive web application frontend built with **HTML5, CSS3, and Bootstrap 5**.

---

## 🛠️ Technology Stack
- **Database Backend:** Oracle SQL
- **User Interface Frontend:** HTML5, CSS3, Bootstrap 5
- **Design Philosophy:** 3rd Normal Form (3NF) relational constraints for high scalability and zero redundancy.

---

## 📊 Database Schema Design
The core architecture consists of **5 normalized tables** with strict primary-key and foreign-key integrity constraints.

### 1. DEPARTMENTS
Stores corporate organizational structural details.
- `dept_id` (NUMBER, Primary Key)
- `dept_name` (VARCHAR2(100), NOT NULL, UNIQUE)

### 2. JOBS
Defines positions, corporate titles, and standardized salary bands.
- `job_id` (VARCHAR2(10), Primary Key)
- `job_title` (VARCHAR2(100), NOT NULL)
- `min_salary` (NUMBER(10,2))
- `max_salary` (NUMBER(10,2))

### 3. EMPLOYEES
The master directory table containing all staff profiles.
- `emp_id` (NUMBER, Primary Key)
- `first_name` (VARCHAR2(50), NOT NULL)
- `last_name` (VARCHAR2(50), NOT NULL)
- `email` (VARCHAR2(100), NOT NULL, UNIQUE)
- `hire_date` (DATE, NOT NULL)
- `dept_id` (NUMBER, Foreign Key ➡️ DEPARTMENTS)
- `job_id` (VARCHAR2(10), Foreign Key ➡️ JOBS)

### 4. SALARY_HISTORY
Tracks payroll tracking records over time for auditing.
- `history_id` (NUMBER, Primary Key)
- `emp_id` (NUMBER, Foreign Key ➡️ EMPLOYEES)
- `current_salary` (NUMBER(10,2), NOT NULL)
- `effective_date` (DATE, NOT NULL)

### 5. ATTENDANCE_LOGS
Tracks administrative operational records for clocking metrics.
- `log_id` (NUMBER, Primary Key)
- `emp_id` (NUMBER, Foreign Key ➡️ EMPLOYEES)
- `log_date` (DATE, NOT NULL)
- `status` (VARCHAR2(20), CHECK constraint for: 'Present', 'Absent', 'Leave')

---

## 🎨 Frontend UI Plan (Bootstrap 5)
- **Dashboard:** Features visual summary layout structures with numeric data totals powered by Bootstrap `.card` layouts.
- **Data Forms:** Interactive entry components with consistent formatting relying on `.form-control` and responsive structural grid wrappers.
- **Records Directories:** Fully responsive information arrays designed through contextual class utilities including `.table`, `.table-striped`, and `.table-hover`.

---

## 🚀 Scalability Blueprint (Future Proof)
The system is intentionally modular to enable future expansion paths without refactoring existing schemas. Supplementary modules can be attached seamlessly by appending tables with appropriate relationship mapping rules:
1. **Leave Allocation Module:** Connect a secondary table (e.g., `LEAVE_REQUESTS`) via `emp_id`.
2. **Performance Evaluation Tracking:** Connect a secondary table for tracking periodic feedback scores via `emp_id`.
