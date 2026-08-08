<?php 
require_once 'config/db.php';
include_once 'includes/header.php';

$role = $_SESSION['user_role'] ?? 'Employee';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $first_name = trim($_POST['first_name']);
        $last_name  = trim($_POST['last_name']);
        $email      = trim($_POST['email']);
        $salary     = (float)$_POST['salary'];
        $dept_id    = (int)$_POST['dept_id'];
        $hire_date  = $_POST['hire_date'];

        $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, email, salary, dept_id, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdis", $first_name, $last_name, $email, $salary, $dept_id, $hire_date);
        $stmt->execute();
        $msg = "Employee registered successfully!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE employees SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Employee deleted!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'hard_delete' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Employee permanently deleted!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE employees SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Employee restored successfully!";
    }
}

$departments = $conn->query("SELECT * FROM departments WHERE is_deleted = 0");

// Role-based visibility logic
if ($role === 'Admin') {
    $employees = $conn->query("SELECT e.*, d.dept_name FROM employees e JOIN departments d ON e.dept_id = d.id ORDER BY e.is_deleted ASC, e.id DESC");
} else {
    $employees = $conn->query("SELECT e.*, d.dept_name FROM employees e JOIN departments d ON e.dept_id = d.id WHERE e.is_deleted = 0 ORDER BY e.id DESC");
}
?>

<?php if ($msg): ?>
    <div class="alert alert-info alert-dismissible fade show"><?= $msg ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Add Employee</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-2">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Salary</label>
                        <input type="number" step="0.01" name="salary" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Department</label>
                        <select name="dept_id" class="form-select" required>
                            <option value="">Select Dept</option>
                            <?php while ($d = $departments->fetch_assoc()): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Employee Records</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Dept</th>
                            <th>Salary</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($e = $employees->fetch_assoc()): ?>
                        <tr class="<?= $e['is_deleted'] ? 'table-danger' : '' ?>">
                            <td><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></td>
                            <td><?= htmlspecialchars($e['email']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($e['dept_name']) ?></span></td>
                            <td>$<?= number_format($e['salary'], 2) ?></td>
                            <td>
                                <?php if (!$e['is_deleted']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="soft_delete">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <button class="btn btn-sm btn-outline-warning">Delete</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($role === 'Admin' && $e['is_deleted']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <button class="btn btn-sm btn-outline-success">Restore</button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('PERMANENTLY delete employee record?');">
                                        <input type="hidden" name="action" value="hard_delete">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
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