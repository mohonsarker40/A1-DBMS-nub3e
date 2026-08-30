<?php
include_once 'includes/header.php';

// Fetch metrics for cards
$total_depts = $conn->query("SELECT COUNT(*) AS total FROM departments WHERE is_deleted = 0")->fetch_assoc()['total'] ?? 0;
$total_emps  = $conn->query("SELECT COUNT(*) AS total FROM employees WHERE is_deleted = 0")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;

// Dashboard specific translations
$dash_translations = [
    'en' => [
        'welcome'        => 'Welcome',
        'logged_in_as'   => 'You are logged in as',
        'select_option'  => 'Select options from the left sidebar to manage the portal.',
        'attendance_log' => 'Attendance Log',
        'total_depts'    => 'Departments',
        'total_emps'     => 'Employees',
        'system_users'   => 'System Users',
    ],
    'bn' => [
        'welcome'        => 'স্বাগতম',
        'logged_in_as'   => 'আপনি লগইন করেছেন',
        'select_option'  => 'পোর্টাল পরিচালনা করতে বামপাশের সাইডবার থেকে অপশন নির্বাচন করুন।',
        'attendance_log' => 'উপস্থিতির তথ্য',
        'total_depts'    => 'ডিপার্টমেন্টসমূহ',
        'total_emps'     => 'কর্মচারীবৃন্দ',
        'system_users'   => 'সিস্টেম ইউজার',
    ]
];

$dt = $dash_translations[$lang] ?? $dash_translations['en'];
?>

<!-- Welcome Banner -->
<div class="glass-card border-glow-primary p-4 p-md-5 mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold mb-2" style="font-family: 'Syne', sans-serif;">
                <?= $dt['welcome'] ?>, <span style="background: linear-gradient(135deg, #fff, #ff0055); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= htmlspecialchars($_SESSION['user_name']) ?></span>!
            </h1>
            <p class="text-secondary mb-0">
                <?= $dt['logged_in_as'] ?> <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25"><?= htmlspecialchars($_SESSION['user_role']) ?></span>। <?= $dt['select_option'] ?>
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="attendance.php" class="btn btn-danger fw-bold px-4 py-2" style="background: linear-gradient(135deg, #ff0055, #7928ca); border:none; border-radius: 10px;">
                <i class="fa-solid fa-clock me-2"></i> <?= $dt['attendance_log'] ?>
            </a>
        </div>
    </div>
</div>

<!-- Stats Overview Grid -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;"><?= $dt['total_depts'] ?></span>
                <i class="fa-solid fa-sitemap text-info fa-lg"></i>
            </div>
            <h2 class="fw-bold mb-0" style="font-family: 'Syne', sans-serif; font-size: 2rem;"><?= sprintf("%02d", $total_depts) ?></h2>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;"><?= $dt['total_emps'] ?></span>
                <i class="fa-solid fa-users text-danger fa-lg"></i>
            </div>
            <h2 class="fw-bold mb-0" style="font-family: 'Syne', sans-serif; font-size: 2rem;"><?= sprintf("%02d", $total_emps) ?></h2>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;"><?= $dt['system_users'] ?></span>
                <i class="fa-solid fa-user-shield text-warning fa-lg"></i>
            </div>
            <h2 class="fw-bold mb-0" style="font-family: 'Syne', sans-serif; font-size: 2rem;"><?= sprintf("%02d", $total_users) ?></h2>
        </div>
    </div>
</div>
 
<?php include_once 'includes/footer.php'; ?>