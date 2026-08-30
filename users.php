<?php 
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Language setup (fallback to header settings)
$lang = $_SESSION['lang'] ?? 'en';

// Page level translations
$u_trans = [
    'en' => [
        'access_denied'      => 'Access Denied. Admin privileges required.',
        'msg_added'          => 'New account (%s) created successfully!',
        'err_added'          => 'Error adding user: Email might already exist or DB query failed.',
        'msg_updated'        => 'User account updated successfully!',
        'err_updated'        => 'Error updating user account.',
        'err_self_delete'    => 'You cannot delete your own active Admin account!',
        'msg_soft_delete'    => 'User account moved to trash (soft deleted).',
        'msg_restored'       => 'User account restored successfully!',
        'msg_perm_delete'    => 'User account permanently deleted!',
        'title_create'       => 'Create User Account',
        'title_directory'    => 'User Accounts Directory',
        'label_name'         => 'Full Name',
        'label_email'        => 'Email Address',
        'label_password'     => 'Password',
        'label_new_password' => 'New Password',
        'pass_help'          => '(Leave blank to keep unchanged)',
        'label_role'         => 'Assigned Role',
        'btn_create'         => 'Create Account',
        'btn_edit'           => 'Edit',
        'btn_delete'         => 'Delete',
        'btn_restore'        => 'Restore',
        'btn_perm_delete'    => 'Permanent Delete',
        'btn_update'         => 'Update User',
        'btn_cancel'         => 'Cancel',
        'badge_deleted'      => 'Deleted',
        'badge_you'          => '(You)',
        'role_admin'         => 'System Admin',
        'role_dept'          => 'Department In-Charge',
        'role_emp'           => 'Employee',
        'th_name'            => 'Name',
        'th_email'           => 'Email',
        'th_role'            => 'Role',
        'th_created'         => 'Created',
        'th_action'          => 'Action',
        'no_users'           => 'No user accounts found.',
        'edit_modal_title'   => 'Edit User Account',
        'confirm_trash'      => 'Move this user to trash?',
        'confirm_perm'       => 'Permanently delete this user?'
    ],
    'bn' => [
        'access_denied'      => 'প্রবেশাধিকার নেই। শুধুমাত্র অ্যাডমিনদের জন্য প্রযোজ্য।',
        'msg_added'          => 'নতুন অ্যাকাউন্ট (%s) সফলভাবে তৈরি করা হয়েছে!',
        'err_added'          => 'ব্যবহারকারী যোগ করতে ব্যর্থ: ইমেইল ইতোমধ্যে ব্যবহৃত হতে পারে বা ডিবি ত্রুটি।',
        'msg_updated'        => 'ব্যবহারকারীর অ্যাকাউন্ট তথ্য সফলভাবে আপডেট করা হয়েছে!',
        'err_updated'        => 'ব্যবহারকারীর অ্যাকাউন্ট আপডেট করতে সমস্যা হয়েছে।',
        'err_self_delete'    => 'আপনি নিজের সক্রিয় অ্যাডমিন অ্যাকাউন্ট মুছে ফেলতে পারবেন না!',
        'msg_soft_delete'    => 'ব্যবহারকারী অ্যাকাউন্ট ট্র্যাশে সরানো হয়েছে (সফট ডিলিট)।',
        'msg_restored'       => 'ব্যবহারকারী অ্যাকাউন্ট সফলভাবে পুনরুদ্ধার করা হয়েছে!',
        'msg_perm_delete'    => 'ব্যবহারকারী অ্যাকাউন্ট স্থায়ীভাবে মুছে ফেলা হয়েছে!',
        'title_create'       => 'নতুন অ্যাকাউন্ট তৈরি করুন',
        'title_directory'    => 'ইউজার অ্যাকাউন্ট ডিরেক্টরি',
        'label_name'         => 'পূর্ণ নাম',
        'label_email'        => 'ইমেইল ঠিকানা',
        'label_password'     => 'পাসওয়ার্ড',
        'label_new_password' => 'নতুন পাসওয়ার্ড',
        'pass_help'          => '(পরিবর্তন না করতে চাইলে ফাঁকা রাখুন)',
        'label_role'         => 'অর্পিত রোল (Role)',
        'btn_create'         => 'অ্যাকাউন্ট তৈরি করুন',
        'btn_edit'           => 'সম্পাদনা',
        'btn_delete'         => 'মুছুন',
        'btn_restore'        => 'পুনরুদ্ধার',
        'btn_perm_delete'    => 'স্থায়ীভাবে মুছুন',
        'btn_update'         => 'আপডেট করুন',
        'btn_cancel'         => 'বাতিল',
        'badge_deleted'      => 'মুছে ফেলা হয়েছে',
        'badge_you'          => '(আপনি)',
        'role_admin'         => 'সিস্টেম অ্যাডমিন',
        'role_dept'          => 'ডিপার্টমেন্ট ইন-চার্জ',
        'role_emp'           => 'কর্মচারী',
        'th_name'            => 'নাম',
        'th_email'           => 'ইমেইল',
        'th_role'            => 'রোল',
        'th_created'         => 'তৈরির তারিখ',
        'th_action'          => 'অ্যাকশন',
        'no_users'           => 'কোনো ইউজার অ্যাকাউন্ট পাওয়া যায়নি।',
        'edit_modal_title'   => 'ইউজার অ্যাকাউন্ট সংশোধন',
        'confirm_trash'      => 'আপনি কি এই ব্যবহারকারীকে ট্র্যাশে পাঠাতে চান?',
        'confirm_perm'       => 'আপনি কি নিশ্চিতভাবে এই ব্যবহারকারীকে স্থায়ীভাবে মুছে ফেলতে চান?'
    ]
];

$ut = $u_trans[$lang] ?? $u_trans['en'];

// Redirect Non-Admin Users Immediately
if (($_SESSION['user_role'] ?? '') !== 'Admin') {
    $_SESSION['flash_error'] = $ut['access_denied'];
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

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_deleted) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("ssss", $name, $email, $password, $user_role);
        
        if ($stmt->execute()) {
            $_SESSION['flash_msg'] = sprintf($ut['msg_added'], $user_role);
        } else {
            $_SESSION['flash_error'] = $ut['err_added'];
        }

    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
        $id        = (int)$_POST['id'];
        $name      = trim($_POST['name']);
        $email     = trim($_POST['email']);
        $user_role = $_POST['role'];
        $password  = trim($_POST['password']);

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $user_role, $hashed_password, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $user_role, $id);
        }

        if ($stmt->execute()) {
            if ($id === (int)$_SESSION['user_id']) {
                $_SESSION['user_role'] = $user_role;
            }
            $_SESSION['flash_msg'] = $ut['msg_updated'];
        } else {
            $_SESSION['flash_error'] = $ut['err_updated'];
        }

    } elseif (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        $id = (int)$_POST['id'];
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = $ut['err_self_delete'];
        } else {
            $stmt = $conn->prepare("UPDATE users SET is_deleted = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['flash_msg'] = $ut['msg_soft_delete'];
        }

    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore_user') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE users SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $ut['msg_restored'];

    } elseif (isset($_POST['action']) && $_POST['action'] === 'permanent_delete') {
        $id = (int)$_POST['id'];
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = $ut['err_self_delete'];
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['flash_msg'] = $ut['msg_perm_delete'];
        }
    }

    header("Location: users.php");
    exit;
}

// Flash Message Handling
$msg = $_SESSION['flash_msg'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_error']);

// Fetch All Users (Sorted: Active first, then Soft-Deleted)
$users = $conn->query("SELECT id, name, email, role, created_at, is_deleted FROM users ORDER BY is_deleted ASC, id DESC");

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

    .modal-backdrop {
        display: none !important;
    }

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
                <i class="fa-solid fa-user-plus me-2 text-primary"></i><?= $ut['title_create'] ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="mb-2">
                        <label class="form-label"><?= $ut['label_name'] ?></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Sarah Connor" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $ut['label_email'] ?></label>
                        <input type="email" name="email" class="form-control" placeholder="user@hrms.com" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $ut['label_password'] ?></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><?= $ut['label_role'] ?></label>
                        <select name="role" class="form-select" required>
                            <option value="Employee"><?= $ut['role_emp'] ?></option>
                            <option value="Department"><?= $ut['role_dept'] ?></option>
                            <option value="Admin"><?= $ut['role_admin'] ?></option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-user-shield me-1"></i> <?= $ut['btn_create'] ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Accounts Directory -->
    <div class="col-md-8">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-users-gear me-2 text-info"></i><?= $ut['title_directory'] ?>
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th><?= $ut['th_name'] ?></th>
                            <th><?= $ut['th_email'] ?></th>
                            <th><?= $ut['th_role'] ?></th>
                            <th><?= $ut['th_created'] ?></th>
                            <th class="text-end"><?= $ut['th_action'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && $users->num_rows > 0): ?>
                            <?php while ($u = $users->fetch_assoc()): ?>
                            <tr class="<?= $u['is_deleted'] ? 'opacity-50' : '' ?>">
                                <td class="fw-semibold text-white">
                                    <?= htmlspecialchars($u['name']) ?>
                                    <?php if ($u['is_deleted']): ?>
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;"><?= $ut['badge_deleted'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-secondary"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'Admin'): ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1"><?= $ut['role_admin'] ?></span>
                                    <?php elseif ($u['role'] === 'Department'): ?>
                                        <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-2 py-1"><?= $ut['role_dept'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25 px-2 py-1"><?= $ut['role_emp'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-secondary"><i class="fa-regular fa-calendar me-1"></i><?= date('d M, Y', strtotime($u['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <?php if (!$u['is_deleted']): ?>
                                            <!-- Edit Button -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info edit-user-btn"
                                                    data-id="<?= $u['id'] ?>"
                                                    data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>"
                                                    data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
                                                    data-role="<?= $u['role'] ?>">
                                                <?= $ut['btn_edit'] ?>
                                            </button>

                                            <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                                <!-- Soft Delete Form -->
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $ut['confirm_trash'] ?>');">
                                                    <input type="hidden" name="action" value="soft_delete">
                                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-warning"><?= $ut['btn_delete'] ?></button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-2 py-1 align-self-center"><?= $ut['badge_you'] ?></span>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <!-- Restore Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore_user">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <button class="btn btn-sm btn-outline-success"><?= $ut['btn_restore'] ?></button>
                                            </form>

                                            <!-- Permanent Delete Form -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $ut['confirm_perm'] ?>');">
                                                <input type="hidden" name="action" value="permanent_delete">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger"><?= $ut['btn_perm_delete'] ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-secondary"><?= $ut['no_users'] ?></td>
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
                    <i class="fa-solid fa-user-pen text-info me-2"></i><?= $ut['edit_modal_title'] ?>
                </h5>
                <button type="button" class="btn-close btn-close-white closeModal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="id" id="edit_user_id">
                    
                    <div class="mb-3">
                        <label class="form-label"><?= $ut['label_name'] ?></label>
                        <input type="text" name="name" id="edit_user_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $ut['label_email'] ?></label>
                        <input type="email" name="email" id="edit_user_email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $ut['label_new_password'] ?> <small class="text-muted fw-normal"><?= $ut['pass_help'] ?></small></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $ut['label_role'] ?></label>
                        <select name="role" id="edit_user_role" class="form-select" required>
                            <option value="Employee"><?= $ut['role_emp'] ?></option>
                            <option value="Department"><?= $ut['role_dept'] ?></option>
                            <option value="Admin"><?= $ut['role_admin'] ?></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm closeModal"><?= $ut['btn_cancel'] ?></button>
                    <button type="submit" class="btn btn-primary btn-sm px-3"><?= $ut['btn_update'] ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

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

    const closeBtns = document.querySelectorAll('.closeModal');
    closeBtns.forEach(btn => {
        btn.addEventListener('click', hideModal);
    });

    window.addEventListener('click', function (e) {
        if (e.target === modalEl) {
            hideModal();
        }
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>