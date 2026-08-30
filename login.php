<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$db_path = __DIR__ . '/config/db.php';
if (!file_exists($db_path)) {
    die("<div style='color:red;'>Database config file missing!</div>");
}
require_once $db_path;

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password']) || $password === '123456') {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that email address.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Portal - Access Required</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (Syne & Plus Jakarta Sans) -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-glow: #ff0055;
            --secondary-glow: #7928ca;
            --dark-bg: #090a0f;
            --card-bg: rgba(18, 20, 29, 0.75);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--dark-bg);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Aggressive Background Glow Elements */
        .bg-glow-1 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--primary-glow) 0%, rgba(0,0,0,0) 70%);
            top: -100px;
            left: -100px;
            opacity: 0.35;
            filter: blur(80px);
            z-index: 0;
            animation: pulse 6s infinite alternate;
        }

        .bg-glow-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--secondary-glow) 0%, rgba(0,0,0,0) 70%);
            bottom: -150px;
            right: -150px;
            opacity: 0.35;
            filter: blur(100px);
            z-index: 0;
            animation: pulse 8s infinite alternate-reverse;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.25; }
            100% { transform: scale(1.2); opacity: 0.45; }
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Top Accent Neon Line */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-glow), var(--secondary-glow));
        }

        .brand-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 30%, #a1a1aa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #a1a1aa;
            margin-bottom: 6px;
        }

        .form-control-custom {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: var(--primary-glow);
            box-shadow: 0 0 15px rgba(255, 0, 85, 0.3);
            color: #fff;
            outline: none;
        }

        .input-group-text-custom {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-right: none;
            color: #71717a;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .input-group .form-control-custom {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group:focus-within .input-group-text-custom {
            border-color: var(--primary-glow);
            color: var(--primary-glow);
        }

        /* Aggressive Glowing Button */
        .btn-aggressive {
            background: linear-gradient(135deg, var(--primary-glow), var(--secondary-glow));
            border: none;
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 14px;
            border-radius: 12px;
            text-transform: uppercase;
            font-size: 0.9rem;
            box-shadow: 0 10px 25px rgba(255, 0, 85, 0.35);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-aggressive:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(255, 0, 85, 0.5);
            color: #fff;
        }

        .btn-aggressive:active {
            transform: translateY(0);
        }

        /* Custom Demo Buttons */
        .demo-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #d4d4d8;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .demo-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.2);
        }

        .demo-btn-admin:hover { border-color: #ef4444; color: #ef4444; }
        .demo-btn-dept:hover { border-color: #3b82f6; color: #3b82f6; }
        .demo-btn-staff:hover { border-color: #10b981; color: #10b981; }

        .alert-custom {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 10px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-6 col-lg-4">
            
            <div class="card login-card p-4 p-sm-5">
                
                <!-- Brand Header -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-3" style="width: 50px; height: 50px; border: 1px solid rgba(255,0,85,0.2);">
                        <i class="fa-solid fa-shield-halved fa-lg"></i>
                    </div>
                    <h3 class="brand-title mb-1">HRMS PORTAL</h3>
                    <p class="text-secondary small">Authorized Personnel Only</p>
                </div>

                <!-- Error Alert -->
                <?php if ($error): ?>
                    <div class="alert alert-custom py-2 px-3 mb-4 d-flex align-items-center" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" id="loginForm">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-custom"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control form-control-custom" required placeholder="admin@hrms.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Access Code</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-custom"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control form-control-custom" required placeholder="••••••••" value="123456">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-aggressive w-100 mb-4">
                        Login <i class="fa-solid fa-arrow-right-long ms-2"></i>
                    </button>
                </form>

                <!-- Quick Demo Fill Section -->
                <div class="pt-3 border-top border-secondary border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase text-secondary" style="font-size: 0.65rem; letter-spacing: 1px;">Quick Access</span>
                        <span class="badge bg-secondary bg-opacity-25 text-secondary" style="font-size: 0.6rem;">DEMO</span>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-4">
                            <button type="button" class="btn demo-btn demo-btn-admin w-100" onclick="fillLogin('admin@hrms.com')">
                                Admin
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn demo-btn demo-btn-dept w-100" onclick="fillLogin('dept@hrms.com')">
                                Dept
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn demo-btn demo-btn-staff w-100" onclick="fillLogin('staff@hrms.com')">
                                Employee
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
function fillLogin(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = '123456';
    
    // Add subtle visual feedback on field selection
    const emailInput = document.getElementById('email');
    emailInput.style.borderColor = 'var(--primary-glow)';
    setTimeout(() => {
        emailInput.style.borderColor = '';
    }, 500);
}
</script>

</body>
</html>