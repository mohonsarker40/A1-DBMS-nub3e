<?php 
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_role'] ?? 'Employee';

// Handle POST Requests with PRG Pattern
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
        $_SESSION['flash_msg'] = "Employee registered successfully!";

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
        $_SESSION['flash_msg'] = "Employee record updated successfully!";

    } elseif (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE employees SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "Employee deleted!";

    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE employees SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "Employee restored successfully!";
    }

    // Redirect to self to prevent form resubmission
    header("Location: employees.php");
    exit;
}

// Flash Message Handling
$msg = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_msg']);

// Fetch active departments for Add & Edit forms
$departments = $conn->query("SELECT * FROM departments WHERE is_deleted = 0 ORDER BY dept_name ASC");
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

include_once 'includes/header.php';
?>

<!-- Custom Styling for Dark Inputs, Tables and Backdrop Fix -->
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

<!-- Alert Message -->
<?php if ($msg): ?>
    <div class="alert alert-success bg-success bg-opacity-25 border border-success text-white alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2 text-success"></i><?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add Employee Form -->
    <div class="col-md-4">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-user-plus me-2 text-primary"></i>Add Employee
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="John">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="Doe">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Salary</label>
                        <input type="number" step="0.01" name="salary" class="form-control" required placeholder="50000">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Department</label>
                        <select name="dept_id" class="form-select" required>
                            <option value="">-- Select Dept --</option>
                            <?php foreach ($dept_list as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Register Employee
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Employee Directory Table -->
    <div class="col-md-8">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-users me-2 text-info"></i>Employee Records
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Dept</th>
                            <th>Salary</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($employees && $employees->num_rows > 0): ?>
                            <?php while ($e = $employees->fetch_assoc()): ?>
                            <tr class="<?= $e['is_deleted'] ? 'opacity-50' : '' ?>">
                                <td class="fw-semibold text-white">
                                    <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                                </td>
                                <td class="text-secondary"><?= htmlspecialchars($e['email']) ?></td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 text-info border border-info border-opacity-25 px-2 py-1">
                                        <?= htmlspecialchars($e['dept_name']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-white">$<?= number_format($e['salary'], 2) ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <?php if (!$e['is_deleted']): ?>
                                            <!-- Edit Button -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info edit-btn" 
                                                    data-id="<?= $e['id'] ?>"
                                                    data-fname="<?= htmlspecialchars($e['first_name'], ENT_QUOTES) ?>"
                                                    data-lname="<?= htmlspecialchars($e['last_name'], ENT_QUOTES) ?>"
                                                    data-email="<?= htmlspecialchars($e['email'], ENT_QUOTES) ?>"
                                                    data-salary="<?= $e['salary'] ?>"
                                                    data-dept="<?= $e['dept_id'] ?>"
                                                    data-hire="<?= $e['hire_date'] ?>">
                                                Edit
                                            </button>

                                            <!-- Soft Delete Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="soft_delete">
                                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                                <button class="btn btn-sm btn-outline-warning">Delete</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($role === 'Admin' && $e['is_deleted']): ?>
                                            <!-- Restore Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                                <button class="btn btn-sm btn-outline-success">Restore</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-secondary">No employee records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white" id="editEmployeeModalLabel">
                    <i class="fa-solid fa-user-pen text-info me-2"></i>Edit Employee Record
                </h5>
                <button type="button" class="btn-close btn-close-white closeModal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_emp_id">
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
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
                    <button type="button" class="btn btn-secondary btn-sm closeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Standalone JavaScript Modal Handler -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('editEmployeeModal');

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
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_emp_id').value     = this.getAttribute('data-id');
            document.getElementById('edit_first_name').value = this.getAttribute('data-fname');
            document.getElementById('edit_last_name').value  = this.getAttribute('data-lname');
            document.getElementById('edit_email').value      = this.getAttribute('data-email');
            document.getElementById('edit_salary').value     = this.getAttribute('data-salary');
            document.getElementById('edit_dept_id').value    = this.getAttribute('data-dept');
            document.getElementById('edit_hire_date').value  = this.getAttribute('data-hire');

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