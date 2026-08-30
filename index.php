<?php
include_once 'includes/header.php';

// Fetch metrics for cards
$total_depts = $conn->query("SELECT COUNT(*) AS total FROM departments WHERE is_deleted = 0")->fetch_assoc()['total'] ?? 0;
$total_emps  = $conn->query("SELECT COUNT(*) AS total FROM employees WHERE is_deleted = 0")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;

// Fetch Attendance Status Summary for Chart (Today's Data)
$today = date('Y-m-d');
$present_count = $conn->query("SELECT COUNT(*) AS total FROM attendance_logs WHERE log_date = '$today' AND status = 'Present' AND is_deleted = 0")->fetch_assoc()['total'] ?? 0;
$absent_count  = $conn->query("SELECT COUNT(*) AS total FROM attendance_logs WHERE log_date = '$today' AND status = 'Absent' AND is_deleted = 0")->fetch_assoc()['total'] ?? 0;
$leave_count   = $conn->query("SELECT COUNT(*) AS total FROM attendance_logs WHERE log_date = '$today' AND status = 'Leave' AND is_deleted = 0")->fetch_assoc()['total'] ?? 0;

// Fetch Recent Attendance Logs (Latest 5 records for Today)
$recent_logs = $conn->query("
    SELECT a.*, e.name AS emp_name, d.dept_name 
    FROM attendance_logs a 
    JOIN employees e ON a.employee_id = e.id 
    LEFT JOIN departments d ON e.department_id = d.id 
    WHERE a.log_date = '$today' AND a.is_deleted = 0 
    ORDER BY a.id DESC LIMIT 5
");

// Fetch Department Wise Employee Distribution
$dept_stats = $conn->query("
    SELECT d.dept_name, COUNT(e.id) AS emp_count 
    FROM departments d 
    LEFT JOIN employees e ON d.id = e.department_id AND e.is_deleted = 0 
    WHERE d.is_deleted = 0 
    GROUP BY d.id LIMIT 4
");

// Dashboard specific translations
$dash_translations = [
    'en' => [
        'welcome'            => 'Welcome',
        'logged_in_as'       => 'You are logged in as',
        'select_option'      => 'Select options from the left sidebar to manage the portal.',
        'attendance_log'     => 'Attendance Log',
        'total_depts'        => 'Departments',
        'total_emps'         => 'Employees',
        'system_users'       => 'System Users',
        'overview_chart'     => 'System Overview',
        'attendance_chart'   => "Today's Attendance Overview",
        'chart_present'      => 'Present',
        'chart_absent'       => 'Absent',
        'chart_leave'        => 'Leave',
        'quick_actions'      => 'Quick Actions',
        'add_employee'       => 'Add Employee',
        'mark_attendance'    => 'Mark Attendance',
        'add_department'     => 'Add Department',
        'manage_users'       => 'Manage Users',
        'recent_activity'    => "Today's Recent Attendance",
        'employee'           => 'Employee',
        'department'         => 'Department',
        'status'             => 'Status',
        'time'               => 'Time',
        'no_records'         => 'No attendance records logged today yet.',
        'dept_distribution'  => 'Department Capacity'
    ],
    'bn' => [
        'welcome'            => 'স্বাগতম',
        'logged_in_as'       => 'আপনি লগইন করেছেন',
        'select_option'      => 'পোর্টাল পরিচালনা করতে বামপাশের সাইডবার থেকে অপশন নির্বাচন করুন।',
        'attendance_log'     => 'উপস্থিতির তথ্য',
        'total_depts'        => 'ডিপার্টমেন্টসমূহ',
        'total_emps'         => 'কর্মচারীবৃন্দ',
        'system_users'       => 'সিস্টেম ইউজার',
        'overview_chart'     => 'সিস্টেম ওভারভিউ',
        'attendance_chart'   => 'আজকের উপস্থিতির চিত্র',
        'chart_present'      => 'উপস্থিত',
        'chart_absent'       => 'অনুপস্থিত',
        'chart_leave'        => 'ছুটিতে',
        'quick_actions'      => 'দ্রুত নেভিগেশন',
        'add_employee'       => 'কর্মচারী যোগ করুন',
        'mark_attendance'    => 'উপস্থিতি দিন',
        'add_department'     => 'ডিপার্টমেন্ট যোগ করুন',
        'manage_users'       => 'ইউজার ম্যানেজ করুন',
        'recent_activity'    => 'আজকের সাম্প্রতিক উপস্থিতি',
        'employee'           => 'কর্মচারী',
        'department'         => 'ডিপার্টমেন্ট',
        'status'             => 'স্ট্যাটাস',
        'time'               => 'সময়',
        'no_records'         => 'আজ এখনো কোনো উপস্থিতি রেকর্ড করা হয়নি।',
        'dept_distribution'  => 'ডিপার্টমেন্টভিত্তিক বিভাজন'
    ]
];

$dt = $dash_translations[$lang] ?? $dash_translations['en'];
?>

<!-- Include Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Welcome Banner -->
<div class="glass-card border-glow-primary p-4 p-md-5 mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold mb-2" style="font-family: 'Syne', sans-serif;">
                <?= $dt['welcome'] ?>, <span style="background: linear-gradient(135deg, #fff, #ff0055); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>!
            </h1>
            <p class="text-secondary mb-0">
                <?= $dt['logged_in_as'] ?> <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Employee') ?></span>। <?= $dt['select_option'] ?>
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

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Bar Chart: Overview -->
    <div class="col-12 col-lg-7">
        <div class="glass-card p-4 h-100">
            <div class="fw-bold text-white mb-3 d-flex align-items-center">
                <i class="fa-solid fa-chart-column me-2 text-primary"></i> <?= $dt['overview_chart'] ?>
            </div>
            <div style="position: relative; height: 280px;">
                <canvas id="overviewChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Donut Chart: Today's Attendance Summary -->
    <div class="col-12 col-lg-5">
        <div class="glass-card p-4 h-100">
            <div class="fw-bold text-white mb-3 d-flex align-items-center">
                <i class="fa-solid fa-chart-pie me-2 text-danger"></i> <?= $dt['attendance_chart'] ?>
            </div>
            <div style="position: relative; height: 280px;" class="d-flex justify-content-center align-items-center">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart Script Configuration -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Shared dark theme options for Chart.js
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

    // 1. System Overview Bar Chart
    const ctxOverview = document.getElementById('overviewChart').getContext('2d');
    new Chart(ctxOverview, {
        type: 'bar',
        data: {
            labels: [
                '<?= $dt['total_depts'] ?>', 
                '<?= $dt['total_emps'] ?>', 
                '<?= $dt['system_users'] ?>'
            ],
            datasets: [{
                label: 'Total Count',
                data: [<?= $total_depts ?>, <?= $total_emps ?>, <?= $total_users ?>],
                backgroundColor: [
                    'rgba(56, 189, 248, 0.6)',
                    'rgba(244, 63, 94, 0.6)',
                    'rgba(251, 191, 36, 0.6)'
                ],
                borderColor: [
                    '#38bdf8',
                    '#f43f5e',
                    '#fbbf24'
                ],
                borderWidth: 1.5,
                borderRadius: 8,
                barThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Today's Attendance Doughnut Chart
    const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctxAttendance, {
        type: 'doughnut',
        data: {
            labels: [
                '<?= $dt['chart_present'] ?>', 
                '<?= $dt['chart_absent'] ?>', 
                '<?= $dt['chart_leave'] ?>'
            ],
            datasets: [{
                data: [<?= $present_count ?>, <?= $absent_count ?>, <?= $leave_count ?>],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(234, 179, 8, 0.8)'
                ],
                borderColor: '#0f172a',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            cutout: '70%'
        }
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>