<?php 
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect Non-Admin Users Immediately
if (($_SESSION['user_role'] ?? '') !== 'Admin') {
    $_SESSION['flash_error'] = "Access Denied. Admin privileges required.";
    header("Location: index.php");
    exit();
}

// Handle POST Requests with PRG Pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $name      = trim($_POST['name']);
        $email     = trim($_POST['email']);
        $password  = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $user_role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $user_role);
        
        if ($stmt->execute()) {
            $_SESSION['flash_msg'] = "New account ($user_role) created successfully!";
        } else {
            $_SESSION['flash_error'] = "Error: Email might already exist.";
        }

    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
        $id        = (int)$_POST['id'];
        $name      = trim($_POST['name']);
        $email     = trim($_POST['email']);
        $user_role = $_POST['role'];
        $password  = trim($_POST['password']);

        // Check if password field is filled
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $user_role, $hashed_password, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $user_role, $id);
        }

        if ($stmt->execute()) {
            // Update session if editing own account
            if ($id === (int)$_SESSION['user_id']) {
                $_SESSION['user_role'] = $user_role;
            }
            $_SESSION['flash_msg'] = "User account updated successfully!";
        } else {
            $_SESSION['flash_error'] = "Error updating user account.";
        }

    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $id = (int)$_POST['id'];
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = "You cannot delete your own active Admin account!";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['flash_msg'] = "User account removed!";
        }
    }

    // Redirect to self to prevent form resubmission on page refresh
    header("Location: users.php");
    exit;
}

// Flash Message Handling
$msg = $_SESSION['flash_msg'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_error']);

// Fetch All Users
$users = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");

include_once 'includes/header.php';
?>

<!-- Custom CSS for Dark UI, Table, Inputs & Modal Fix -->
<style>
    .form-control, .form-select {
        background-color: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        border-radius: 8px;
        padding: 8px 12px;
    }
    .form-control:focus, .form-select:focus {
        background-color: rgba(255, 255, 255, 0.08) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
    }
    .form-select option {
        background-color: #1e293b;
        color: #ffffff;
    }
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #cbd5e1;
    }

    /* Force Remove Backdrop overlay issue */
    .modal-backdrop {
        display: none !important;
    }

    /* Modal Styling with Built-in Dimmed Background */
    .modal {
        background: rgba(0, 0, 0, 0.75) !important;
    }
    .modal-content {
        background: #0f172a !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        border-radius: 14px;
        color: #ffffff !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
    }
    .modal-header, .modal-footer {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    /* Custom Dark Table Styles */
    .table-dark-custom {
        color: #ffffff !important;
        margin-bottom: 0;
        background-color: transparent !important;
    }
    .table-dark-custom th {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #94a3b8 !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        padding: 12px 16px;
    }
    .table-dark-custom td {
        background-color: transparent !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        padding: 12px 16px;
        vertical-align: middle;
        color: #ffffff !important;
    }

    /* Turn Off White Hover Completely */
    .table-dark-custom tbody tr,
    .table-dark-custom tbody tr:hover,
    .table-dark-custom tbody tr td,
    .table-dark-custom tbody tr:hover td {
        background-color: transparent !important;
        background: transparent !important;
        color: #ffffff !important;
        --bs-table-accent-bg: transparent !important;
        --bs-table-hover-bg: transparent !important;
        --bs-table-bg: transparent !important;
    }

    /* Subtile Smooth Hover */
    .table-dark-custom tbody tr:hover td {
        background-color: rgba(255, 255, 255, 0.03) !important;
    }
</style>

<!-- Flash Alerts -->
<?php if ($msg): ?>
    <div class="alert alert-success bg-success bg-opacity-25 border border-success text-white alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2 text-success"></i><?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger bg-danger bg-opacity-25 border border-danger text-white alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2 text-danger"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Create User Account Form -->
    <div class="col-md-4">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-user-plus me-2 text-primary"></i>Create User Account
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="mb-2">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Sarah Connor" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="user@hrms.com" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Assigned Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Employee">Employee</option>
                            <option value="Department">Department In-Charge</option>
                            <option value="Admin">System Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-user-shield me-1"></i> Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Accounts Directory -->
    <div class="col-md-8">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-users-gear me-2 text-info"></i>User Accounts Directory
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && $users->num_rows > 0): ?>
                            <?php while ($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-semibold text-white"><?= htmlspecialchars($u['name']) ?></td>
                                <td class="text-secondary"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'Admin'): ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1">System Admin</span>
                                    <?php elseif ($u['role'] === 'Department'): ?>
                                        <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-2 py-1">Department</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25 px-2 py-1">Employee</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-secondary"><i class="fa-regular fa-calendar me-1"></i><?= date('d M, Y', strtotime($u['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info edit-user-btn"
                                                data-id="<?= $u['id'] ?>"
                                                data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>"
                                                data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
                                                data-role="<?= $u['role'] ?>">
                                            Edit
                                        </button>

                                        <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                            <!-- Delete Form -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user account?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <button class="btn btn-sm btn-outline-warning">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-2 py-1 align-self-center">(You)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-secondary">No user accounts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white" id="editUserModalLabel">
                    <i class="fa-solid fa-user-pen text-info me-2"></i>Edit User Account
                </h5>
                <button type="button" class="btn-close btn-close-white closeModal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="id" id="edit_user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="edit_user_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="edit_user_email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password <small class="text-muted fw-normal">(Leave blank to keep unchanged)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Assigned Role</label>
                        <select name="role" id="edit_user_role" class="form-select" required>
                            <option value="Employee">Employee</option>
                            <option value="Department">Department In-Charge</option>
                            <option value="Admin">System Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm closeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Standalone JavaScript Modal Handler -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('editUserModal');

    function showModal() {
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function hideModal() {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // Populate and open Modal
    const editBtns = document.querySelectorAll('.edit-user-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_user_id').value   = this.getAttribute('data-id');
            document.getElementById('edit_user_name').value = this.getAttribute('data-name');
            document.getElementById('edit_user_email').value = this.getAttribute('data-email');
            document.getElementById('edit_user_role').value  = this.getAttribute('data-role');

            showModal();
        });
    });

    // Close Modal via Cancel or Cross 'X' Button
    const closeBtns = document.querySelectorAll('.closeModal');
    closeBtns.forEach(btn => {
        btn.addEventListener('click', hideModal);
    });

    // Close Modal when clicking backdrop area
    window.addEventListener('click', function (e) {
        if (e.target === modalEl) {
            hideModal();
        }
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>