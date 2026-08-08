<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

// First-time setup enforcement checks
$dept_check = $conn->query("SELECT COUNT(*) AS total FROM departments WHERE is_deleted = 0")->fetch_assoc()['total'];
$emp_check  = $conn->query("SELECT COUNT(*) AS total FROM employees WHERE is_deleted = 0")->fetch_assoc()['total'];

$current_page = basename($_SERVER['PHP_SELF']);

if (($dept_check == 0 || $emp_check == 0) && !in_array($current_page, ['departments.php', 'employees.php', 'logout.php'])) {
    if ($dept_check == 0) {
        header("Location: departments.php?setup=1");
        exit();
    } elseif ($emp_check == 0) {
        header("Location: employees.php?setup=1");
        exit();
    }
}

$role = $_SESSION['user_role'] ?? 'Employee';
$name = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand font-monospace" href="index.php">HRMS Portal</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <?php if ($role === 'Admin'): ?>
            <li class="nav-item"><a class="nav-link text-warning" href="users.php">Manage Users</a></li>
        <?php endif; ?>
        <?php if ($role === 'Admin' || $role === 'Department'): ?>
            <li class="nav-item"><a class="nav-link" href="departments.php">Departments</a></li>
            <li class="nav-item"><a class="nav-link" href="employees.php">Employees</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="attendance.php">Attendance</a></li>
      </ul>
      <div class="d-flex align-items-center text-white">
        <span class="me-3 badge bg-info text-dark"><?= htmlspecialchars($name) ?> (<?= $role ?>)</span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
      </div>
    </div>
  </div>
</nav>
<div class="container">