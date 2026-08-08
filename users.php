<?php
require_once 'config/db.php';
include_once 'includes/header.php';

if ($_SESSION['user_role'] !== 'Admin') {
    echo "<div class='alert alert-danger'>Access Denied. Admin privileges required.</div>";
    include_once 'includes/footer.php';
    exit();
}

$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $name      = trim($_POST['name']);
        $email     = trim($_POST['email']);
        $password  = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $user_role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $user_role);
        
        if ($stmt->execute()) {
            $msg = "New account ($user_role) created successfully!";
        } else {
            $error = "Error: Email might already exist.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $id = (int)$_POST['id'];
        if ($id === (int)$_SESSION['user_id']) {
            $error = "You cannot delete your own active Admin account!";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $msg = "User account removed!";
        }
    }
}

$users = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
?>

<?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">Create User Account</div>
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
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assigned Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Department">Department In-Charge</option>
                            <option value="Employee">Employee</option>
                            <option value="Admin">System Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">User Accounts Directory</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php 
                                    $badge = $u['role'] === 'Admin' ? 'bg-danger' : ($u['role'] === 'Department' ? 'bg-info text-dark' : 'bg-secondary');
                                ?>
                                <span class="badge <?= $badge ?>"><?= $u['role'] ?></span>
                            </td>
                            <td><small class="text-muted"><?= date('Y-m-d', strtotime($u['created_at'])) ?></small></td>
                            <td>
                                <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user account?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <small class="text-muted">(You)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>