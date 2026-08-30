<?php 
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_role'] ?? 'Employee';

// Handle POST Requests with PRG (Post/Redirect/Get) Pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $emp_id   = (int)$_POST['emp_id'];
        $log_date = $_POST['log_date'];
        $log_time = $_POST['log_time']; 
        $status   = $_POST['status'];

        $stmt = $conn->prepare("INSERT INTO attendance_logs (emp_id, log_date, log_time, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $emp_id, $log_date, $log_time, $status);
        $stmt->execute();
        $_SESSION['flash_msg'] = "Attendance logged successfully!";

    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id       = (int)$_POST['id'];
        $emp_id   = (int)$_POST['emp_id'];
        $log_date = $_POST['log_date'];
        $log_time = $_POST['log_time'];
        $status   = $_POST['status'];

        $stmt = $conn->prepare("UPDATE attendance_logs SET emp_id = ?, log_date = ?, log_time = ?, status = ? WHERE id = ?");
        $stmt->bind_param("isssi", $emp_id, $log_date, $log_time, $status, $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "Attendance log updated successfully!";

    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE attendance_logs SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "Attendance deleted!";

    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE attendance_logs SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "Attendance log restored successfully!";
    }

    header("Location: attendance.php");
    exit;
}

// Flash Message Handling
$msg = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_msg']);

// Fetch active employees
$employees = $conn->query("SELECT id, first_name, last_name FROM employees WHERE is_deleted = 0 ORDER BY first_name ASC");
$emp_list = [];
while ($emp = $employees->fetch_assoc()) {
    $emp_list[] = $emp;
}

// Fetch Attendance Logs
if ($role === 'Admin') {
    $logs = $conn->query("SELECT a.*, e.first_name, e.last_name FROM attendance_logs a JOIN employees e ON a.emp_id = e.id ORDER BY a.is_deleted ASC, a.log_date DESC, a.log_time DESC");
} else {
    $logs = $conn->query("SELECT a.*, e.first_name, e.last_name FROM attendance_logs a JOIN employees e ON a.emp_id = e.id WHERE a.is_deleted = 0 ORDER BY a.log_date DESC, a.log_time DESC");
}

include_once 'includes/header.php';
?>

<!-- CSS Fix for Backdrop Blocking Issue -->
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

    /* Modal Styling with Built-in Dark Dimmer Background */
    .modal {
        background: rgba(0, 0, 0, 0.75) !important;
    }
    .modal-content {
        background: #0f172a !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        border-radius: 14px;
        color: #ffffff !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
    }
    .modal-header, .modal-footer {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

/* Table Styling & Remove White Hover Effect */
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

/* Remove Bootstrap Default White Hover Completely */
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

/* Light subtile hover effect for better UI (Optional) */
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
    <!-- Log Attendance Form -->
    <div class="col-md-4">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-clock me-2 text-primary"></i>Log Attendance
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="emp_id" class="form-select" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($emp_list as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="log_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Time</label>
                        <input type="time" name="log_time" value="<?= date('H:i') ?>" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select fw-semibold" required>
                            <option value="Present" class="text-success">Present</option>
                            <option value="Absent" class="text-danger">Absent</option>
                            <option value="Leave" class="text-warning">Leave</option>
                        </select>
                    </div>

                    <button class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Submit Log
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance Records Table -->
    <div class="col-md-8">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-list me-2 text-info"></i>Attendance Records
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs->num_rows > 0): ?>
                            <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr class="<?= $log['is_deleted'] ? 'opacity-50' : '' ?>">
                                <td>
                                    <div class="fw-bold text-white"><?= date('d M, Y', strtotime($log['log_date'])) ?></div>
                                    <small style="color: #94a3b8;"><i class="fa-regular fa-clock me-1"></i><?= date('h:i A', strtotime($log['log_time'])) ?></small>
                                </td>
                                <td class="fw-semibold text-white">
                                    <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?>
                                </td>
                                <td>
                                    <?php if ($log['status'] === 'Present'): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1">Present</span>
                                    <?php elseif ($log['status'] === 'Absent'): ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1">Absent</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 px-2 py-1">Leave</span>
                                    <?php endif; ?>

                                    <?php if ($log['is_deleted']): ?>
                                        <span class="badge bg-secondary ms-1">Deleted</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <?php if (!$log['is_deleted']): ?>
                                            <!-- Edit Button -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info edit-btn" 
                                                    data-id="<?= $log['id'] ?>"
                                                    data-emp="<?= $log['emp_id'] ?>"
                                                    data-date="<?= $log['log_date'] ?>"
                                                    data-time="<?= $log['log_time'] ?>"
                                                    data-status="<?= $log['status'] ?>">
                                                Edit
                                            </button>

                                            <!-- Soft Delete Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                                <button class="btn btn-sm btn-outline-warning">Delete</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($role === 'Admin' && $log['is_deleted']): ?>
                                            <!-- Restore Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                                <button class="btn btn-sm btn-outline-success">Restore</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary">No attendance logs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white" id="editAttendanceModalLabel">
                    <i class="fa-solid fa-pen-to-square text-info me-2"></i>Edit Attendance Record
                </h5>
                <button type="button" class="btn-close btn-close-white closeModal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_log_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="emp_id" id="edit_emp_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($emp_list as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="log_date" id="edit_log_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Time</label>
                        <input type="time" name="log_time" id="edit_log_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Leave">Leave</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm closeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Update Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Standalone JS Modal Trigger System -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('editAttendanceModal');

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

    // Open Modal
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_log_id').value   = this.getAttribute('data-id');
            document.getElementById('edit_emp_id').value   = this.getAttribute('data-emp');
            document.getElementById('edit_log_date').value = this.getAttribute('data-date');
            document.getElementById('edit_log_time').value = this.getAttribute('data-time');
            document.getElementById('edit_status').value   = this.getAttribute('data-status');

            showModal();
        });
    });

    // Close Modal via Cancel or Cross 'X' Button
    const closeBtns = document.querySelectorAll('.closeModal');
    closeBtns.forEach(btn => {
        btn.addEventListener('click', hideModal);
    });

    // Close Modal when clicking outside content area
    window.addEventListener('click', function (e) {
        if (e.target === modalEl) {
            hideModal();
        }
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>