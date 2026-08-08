<?php
include_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="p-5 mb-4 bg-white rounded-3 shadow-sm border">
            <div class="container-fluid py-3">
                <h1 class="display-5 fw-bold text-primary">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
                <p class="col-md-8 fs-4 text-muted">Role: <span class="badge bg-dark"><?= htmlspecialchars($_SESSION['user_role']) ?></span></p>
                <hr class="my-4">
                <p>Use the top navigation bar to manage Users, Departments, Employees, and Attendance.</p>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>