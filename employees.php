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
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id         = (int)$_POST['id'];
        $first_name = trim($_POST['first_name']);
        $last_name  = trim($_POST['last_name']);
        $email      = trim($_POST['email']);
        $salary     = (float)$_POST['salary'];
        $dept_id    = (int)$_POST['dept_id'];
        $hire_date  = $_POST['hire_date'];

        $stmt = $conn->prepare("UPDATE employees SET first_name = ?, last_name = ?, email = ?, salary = ?, dept_id = ?, hire_date = ? WHERE id = ?");
        $stmt->bind_param("sssdisi", $first_name, $last_name, $email, $salary, $dept_id, $hire_date, $id);
        $stmt->execute();
        $msg = "Employee record updated successfully!";
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

// Fetch active departments for Add & Edit forms
$departments = $conn->query("SELECT * FROM departments WHERE is_deleted = 0");
$dept_list = [];
while ($d = $departments->fetch_assoc()) {
    $dept_list[] = $d;
}

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
                            <?php foreach ($dept_list as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                            <?php endforeach; ?>
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
                                    <!-- Edit Button (Triggers Modal) -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary edit-btn" 
                                            data-id="<?= $e['id'] ?>"
                                            data-fname="<?= htmlspecialchars($e['first_name'], ENT_QUOTES) ?>"
                                            data-lname="<?= htmlspecialchars($e['last_name'], ENT_QUOTES) ?>"
                                            data-email="<?= htmlspecialchars($e['email'], ENT_QUOTES) ?>"
                                            data-salary="<?= $e['salary'] ?>"
                                            data-dept="<?= $e['dept_id'] ?>"
                                            data-hire="<?= $e['hire_date'] ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editEmployeeModal">
                                        Edit
                                    </button>

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

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_emp_id">
                    
                    <div class="mb-2">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Salary</label>
                        <input type="number" step="0.01" name="salary" id="edit_salary" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Department</label>
                        <select name="dept_id" id="edit_dept_id" class="form-select" required>
                            <option value="">Select Dept</option>
                            <?php foreach ($dept_list as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" id="edit_hire_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript to populate Employee Modal Data -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-btn');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_emp_id').value     = this.getAttribute('data-id');
            document.getElementById('edit_first_name').value = this.getAttribute('data-fname');
            document.getElementById('edit_last_name').value  = this.getAttribute('data-lname');
            document.getElementById('edit_email').value      = this.getAttribute('data-email');
            document.getElementById('edit_salary').value     = this.getAttribute('data-salary');
            document.getElementById('edit_dept_id').value    = this.getAttribute('data-dept');
            document.getElementById('edit_hire_date').value  = this.getAttribute('data-hire');
        });
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>