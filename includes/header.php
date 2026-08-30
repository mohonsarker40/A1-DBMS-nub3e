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
$dept_check = $conn->query("SELECT COUNT(*) AS total FROM departments WHERE is_deleted = 0")->fetch_assoc()['total'] ?? 0;
$emp_check  = $conn->query("SELECT COUNT(*) AS total FROM employees WHERE is_deleted = 0")->fetch_assoc()['total'] ?? 0;

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

// Language Switcher Logic
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = ($_GET['lang'] === 'bn') ? 'bn' : 'en';
}
$lang = $_SESSION['lang'] ?? 'en';

// Simple Localization Array
$translations = [
    'en' => [
        'dashboard'    => 'Dashboard',
        'manage_users' => 'Manage Users',
        'departments'  => 'Departments',
        'employees'    => 'Employees',
        'attendance'   => 'Attendance',
        'notifications'=> 'Notifications',
        'mark_read'    => 'Mark all as read',
        'system_live'  => 'System Live',
        'new'          => 'NEW',
        'search_ph'    => 'Search anything...',
    ],
    'bn' => [
        'dashboard'    => 'ড্যাশবোর্ড',
        'manage_users' => 'ইউজার ব্যবস্থাপনা',
        'departments'  => 'ডিপার্টমেন্টসমূহ',
        'employees'    => 'কর্মচারীবৃন্দ',
        'attendance'   => 'উপস্থিতি',
        'notifications'=> 'নোটিফিকেশন',
        'mark_read'    => 'সব পড়া হয়েছে চিহ্নিত করুন',
        'system_live'  => 'সিস্টেম সচল',
        'new'          => 'নতুন',
        'search_ph'    => 'যেকোনো কিছু খুঁজুন...',
    ]
];

$t = $translations[$lang];

$role = $_SESSION['user_role'] ?? 'Employee';
$name = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Portal - Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Syne:wght@700;800&family=Tiro+Bangla&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-glow: #ff0055;
            --secondary-glow: #7928ca;
            --sidebar-width: 260px;
            --dark-bg: #08090d;
            --card-bg: rgba(18, 20, 29, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Tiro Bangla', sans-serif;
            background-color: var(--dark-bg);
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Ambient Background Glow */
        .bg-glow-1 {
            position: fixed;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--primary-glow) 0%, rgba(0,0,0,0) 70%);
            top: -150px;
            left: -150px;
            opacity: 0.15;
            filter: blur(100px);
            z-index: 0;
            pointer-events: none;
        }

        /* Layout Container */
        .app-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Left Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(12, 14, 22, 0.95);
            backdrop-filter: blur(16px);
            border-right: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 24px 20px;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.3rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 30%, var(--primary-glow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--card-border);
        }

        .sidebar-menu {
            padding: 20px 12px;
            list-style: none;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #a1a1aa;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 12px;
            margin-bottom: 6px;
            transition: all 0.2s ease;
        }

        .sidebar-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(255, 0, 85, 0.2), rgba(121, 40, 202, 0.2));
            border: 1px solid rgba(255, 0, 85, 0.3);
            box-shadow: 0 4px 15px rgba(255, 0, 85, 0.15);
        }

        .sidebar-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-user {
            padding: 16px 20px;
            border-top: 1px solid var(--card-border);
            background: rgba(0, 0, 0, 0.2);
        }

        /* Main Content Wrapper */
        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 30px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* Glass Cards */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .border-glow-primary::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-glow), var(--secondary-glow));
        }

        .role-badge {
            background: rgba(255, 0, 85, 0.15);
            border: 1px solid rgba(255, 0, 85, 0.3);
            color: #ff4d8d;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        /* Search Bar Box Styling */
        .topbar-search-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 6px 14px;
            width: 280px;
            transition: all 0.3s ease;
        }
        .topbar-search-box:focus-within {
            border-color: var(--primary-glow);
            box-shadow: 0 0 12px rgba(255, 0, 85, 0.25);
            background: rgba(255, 255, 255, 0.08);
            width: 320px;
        }
        .topbar-search-box input {
            background: transparent;
            border: none;
            color: #fff;
            outline: none;
            font-size: 0.85rem;
            width: 100%;
        }

        /* Digital Live Clock Widget */
        .topbar-clock {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 6px 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #a1a1aa;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .topbar-clock #liveClock {
            color: #38bdf8;
            font-weight: 700;
        }

        /* Custom Topbar Dropdowns */
        .topbar-dropdown-menu {
            background: rgba(18, 20, 29, 0.95) !important;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 14px !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6) !important;
        }

        .notification-dropdown {
            width: 320px;
            padding: 0;
            overflow: hidden;
        }

        .notification-header {
            background: rgba(255, 255, 255, 0.03);
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #d4d4d8;
            text-decoration: none;
            display: flex;
            gap: 12px;
            transition: background 0.2s ease;
        }

        .notification-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .notification-badge-pulse {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 8px;
            height: 8px;
            background-color: var(--primary-glow);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--primary-glow);
        }

        /* Mobile Responsive */
        @media (max-width: 991px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .topbar-search-box {
                width: 180px;
            }
            .topbar-search-box:focus-within {
                width: 200px;
            }
        }
    </style>
</head>
<body>

<div class="bg-glow-1"></div>

<div class="app-wrapper">
    <!-- LEFT SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-shield-halved text-danger"></i> HRMS PORTAL
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="index.php" class="sidebar-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-pie"></i> <?= $t['dashboard'] ?>
                </a>
            </li>

            <?php if ($role === 'Admin'): ?>
            <li>
                <a href="users.php" class="sidebar-link <?= $current_page == 'users.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-gear text-warning"></i> <?= $t['manage_users'] ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role === 'Admin' || $role === 'Department'): ?>
            <li>
                <a href="departments.php" class="sidebar-link <?= $current_page == 'departments.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-sitemap"></i> <?= $t['departments'] ?>
                </a>
            </li>
            <li>
                <a href="employees.php" class="sidebar-link <?= $current_page == 'employees.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i> <?= $t['employees'] ?>
                </a>
            </li>
            <?php endif; ?>

            <li>
                <a href="attendance.php" class="sidebar-link <?= $current_page == 'attendance.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-clipboard-user"></i> <?= $t['attendance'] ?>
                </a>
            </li>
        </ul>

        <!-- Sidebar User Profile -->
        <div class="sidebar-user d-flex align-items-center justify-content-between">
            <div class="overflow-hidden me-2">
                <div class="fw-bold text-truncate text-white" style="font-size: 0.85rem;"><?= htmlspecialchars($name) ?></div>
                <span class="role-badge"><?= $role ?></span>
            </div>
            <a href="logout.php" class="btn btn-outline-danger btn-sm p-2 rounded-circle" title="Logout">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content">
        <!-- Top Navbar with Search, Live Clock, Language Switcher & Notification -->
        <div class="top-navbar d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-dark d-lg-none border-secondary" type="button" id="sidebarToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <!-- TOPBAR SEARCH BAR -->
                <!-- <div class="topbar-search-box d-flex align-items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                    <input type="text" placeholder="<?= $t['search_ph'] ?>" id="topbarSearchInput">
                </div> -->
            </div>

            <div class="ms-auto d-flex align-items-center gap-3">
                
                <!-- LIVE DIGITAL CLOCK WIDGET -->
                <!-- <div class="topbar-clock d-none d-md-flex">
                    <i class="fa-regular fa-clock text-warning"></i>
                    <span id="liveClock">00:00:00 AM</span>
                </div> -->

                <!-- LANGUAGE SWITCHER DROPDOWN -->
                <div class="dropdown">
                    <button class="btn btn-dark border-secondary border-opacity-25 rounded-3 px-3 py-2 d-flex align-items-center gap-2 small" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.05);">
                        <i class="fa-solid fa-globe text-secondary"></i>
                        <span class="fw-bold text-white"><?= $lang === 'bn' ? 'বাংলা' : 'English' ?></span>
                        <i class="fa-solid fa-chevron-down text-secondary" style="font-size: 0.75rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end topbar-dropdown-menu mt-2 p-1">
                        <li>
                            <a class="dropdown-menu-item dropdown-item rounded-2 text-white small d-flex align-items-center justify-content-between py-2 <?= $lang === 'en' ? 'active bg-danger bg-opacity-25' : '' ?>" href="?lang=en">
                                <span>English</span>
                                <?php if ($lang === 'en'): ?><i class="fa-solid fa-check text-danger small"></i><?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-menu-item dropdown-item rounded-2 text-white small d-flex align-items-center justify-content-between py-2 <?= $lang === 'bn' ? 'active bg-danger bg-opacity-25' : '' ?>" href="?lang=bn">
                                <span>বাংলা</span>
                                <?php if ($lang === 'bn'): ?><i class="fa-solid fa-check text-danger small"></i><?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- NOTIFICATION DROPDOWN -->
                <div class="dropdown">
                    <button class="btn btn-dark position-relative border-0 rounded-circle p-2" type="button" id="notificationMenu" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.05); width: 42px; height: 42px;">
                        <i class="fa-regular fa-bell fs-5 text-light"></i>
                        <span class="notification-badge-pulse"></span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end topbar-dropdown-menu notification-dropdown mt-2" aria-labelledby="notificationMenu">
                        <div class="notification-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white small"><?= $t['notifications'] ?></span>
                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25" style="font-size: 0.65rem;">3 <?= $t['new'] ?></span>
                        </div>

                        <div class="notification-list" style="max-height: 280px; overflow-y: auto;">
                            <a href="attendance.php" class="notification-item">
                                <div class="notification-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-semibold small text-truncate text-white">Attendance Reminder</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">Don't forget to mark your daily attendance.</div>
                                    <div class="text-muted mt-1" style="font-size: 0.65rem;">10 mins ago</div>
                                </div>
                            </a>

                            <a href="employees.php" class="notification-item">
                                <div class="notification-icon bg-info bg-opacity-10 text-info">
                                    <i class="fa-solid fa-user-plus"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-semibold small text-truncate text-white">New Employee Added</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">A new profile was added to IT Department.</div>
                                    <div class="text-muted mt-1" style="font-size: 0.65rem;">1 hour ago</div>
                                </div>
                            </a>

                            <a href="index.php" class="notification-item">
                                <div class="notification-icon bg-success bg-opacity-10 text-success">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-semibold small text-truncate text-white">Security Update</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">System successfully upgraded to v2.4.</div>
                                    <div class="text-muted mt-1" style="font-size: 0.65rem;">Yesterday</div>
                                </div>
                            </a>
                        </div>

                        <div class="p-2 text-center border-top border-secondary border-opacity-10 bg-black bg-opacity-20">
                            <a href="#" class="text-danger small text-decoration-none fw-bold" style="font-size: 0.75rem;"><?= $t['mark_read'] ?></a>
                        </div>
                    </div>
                </div>

                <!-- Status Indicator -->
                <div class="d-none d-xl-flex align-items-center gap-2 bg-dark bg-opacity-50 px-3 py-2 rounded-3 border border-secondary border-opacity-10">
                    <i class="fa-solid fa-circle text-success" style="font-size: 8px;"></i>
                    <span class="text-secondary small" style="font-size: 0.75rem;"><?= $t['system_live'] ?></span>
                </div>

            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Mobile Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Realtime Digital Clock Engine
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // 0 hour is converted to 12
        const formattedHours = String(hours).padStart(2, '0');

        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = `${formattedHours}:${minutes}:${seconds} ${ampm}`;
        }
    }

    setInterval(updateClock, 1000);
    updateClock(); // Initial Trigger
});
</script>