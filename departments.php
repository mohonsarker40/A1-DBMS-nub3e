<?php 
require_once 'config/db.php';
include_once 'includes/header.php';

$role = $_SESSION['user_role'] ?? 'Employee';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['dept_name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO departments (dept_name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $msg = "Department added successfully!";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE departments SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Department deleted!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'hard_delete' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Department permanently deleted!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE departments SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Department restored successfully!";
    }
}

// Admins see all departments; Non-Admins only see active departments (is_deleted = 0)
if ($role === 'Admin') {
    $departments = $conn->query("SELECT * FROM departments ORDER BY is_deleted ASC, id DESC");
} else {
    $departments = $conn->query("SELECT * FROM departments WHERE is_deleted = 0 ORDER BY id DESC");
}
?>

<?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Add Department</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" name="dept_name" class="form-control" required placeholder="e.g. Finance">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Department Directory</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $departments->fetch_assoc()): ?>
                        <tr class="<?= $row['is_deleted'] ? 'table-danger' : '' ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['dept_name']) ?></td>
                            <td>
                                <?php if ($row['is_deleted']): ?>
                                    <span class="badge bg-danger">Deleted</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$row['is_deleted']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="soft_delete">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button class="btn btn-sm btn-outline-warning">Delete</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($role === 'Admin' && $row['is_deleted']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button class="btn btn-sm btn-outline-success">Restore</button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('PERMANENTLY delete this department?');">
                                        <input type="hidden" name="action" value="hard_delete">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button class="btn btn-sm btn-danger">Hard Delete</button>
                                    </form>
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