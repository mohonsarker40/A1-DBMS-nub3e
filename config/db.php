<?php
$host = "localhost";
$user = "root";
$pass = "root"; // Change to "" if your local MySQL has no password
$db   = "hrms_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("<div style='color:red; font-family:sans-serif; padding:20px;'><b>Database Connection Error:</b> " . $conn->connect_error . "</div>");
}
 
// Auto-Setup: Check if 'users' table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");

if ($tableCheck && $tableCheck->num_rows === 0) { 
    $schemaSql = "
    CREATE TABLE IF NOT EXISTS `departments` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `dept_name` VARCHAR(100) NOT NULL,
      `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      `is_deleted` TINYINT(1) DEFAULT '0',
      PRIMARY KEY (`id`),
      UNIQUE KEY `dept_name` (`dept_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `employees` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `first_name` VARCHAR(50) NOT NULL,
      `last_name` VARCHAR(50) NOT NULL,
      `email` VARCHAR(100) NOT NULL,
      `salary` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
      `dept_id` INT NOT NULL,
      `hire_date` DATE NOT NULL,
      `is_deleted` TINYINT(1) DEFAULT '0',
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      KEY `dept_id` (`dept_id`),
      CONSTRAINT `fk_employee_dept` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `attendance_logs` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `emp_id` INT NOT NULL,
      `log_date` DATE NOT NULL,
      `log_time` TIME NOT NULL,
      `status` ENUM('Present','Absent','Leave') NOT NULL DEFAULT 'Present',
      `is_deleted` TINYINT(1) DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `emp_id` (`emp_id`),
      CONSTRAINT `fk_attendance_emp` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `users` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(100) NOT NULL,
      `email` VARCHAR(100) NOT NULL,
      `password` VARCHAR(255) NOT NULL,
       `is_deleted` TINYINT(1) DEFAULT '0',
      `role` ENUM('Admin','Department','Employee') NOT NULL DEFAULT 'Employee',
      `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    INSERT INTO `departments` (`id`, `dept_name`, `is_deleted`) VALUES (1, 'Software Engineering', 0);

    INSERT INTO `employees` (`id`, `first_name`, `last_name`, `email`, `salary`, `dept_id`, `hire_date`, `is_deleted`) VALUES
    (1, 'John', 'Doe', 'john@hrms.com', 50000.00, 1, CURDATE(), 0);

    INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
    (1, 'System Admin', 'admin@hrms.com', '$2y$10$wN1Q/X/YvO.dI1vC.E7S2.k.P5jR2Q7u6aX6yG2.J5Q2R2Q7u6aX6', 'Admin'),
    (2, 'Dept Manager', 'dept@hrms.com', '$2y$10$wN1Q/X/YvO.dI1vC.E7S2.k.P5jR2Q7u6aX6yG2.J5Q2R2Q7u6aX6', 'Department'),
    (3, 'John Staff', 'staff@hrms.com', '$2y$10$wN1Q/X/YvO.dI1vC.E7S2.k.P5jR2Q7u6aX6yG2.J5Q2R2Q7u6aX6', 'Employee');
    ";

    // Execute multi-query setup
    if ($conn->multi_query($schemaSql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }
}
?>
