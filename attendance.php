<?php 
require_once 'config/db.php';

$msg = "";
$role = $_SESSION['user_role'] ?? 'Employee';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $emp_id   = (int)$_POST['emp_id'];
        $log_date = $_POST['log_date'];
        $log_time = $_POST['log_time'];
        $status   = $_POST['status'];

        $stmt = $conn->prepare("INSERT INTO attendance_logs (emp_id, log_date, log_time, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $emp_id, $log_date, $log_time, $status);
        $stmt->execute();
        $msg = "Attendance logged successfully!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id       = (int)$_POST['id'];
        $emp_id   = (int)$_POST['emp_id'];
        $log_date = $_POST['log_date'];
        $log_time = $_POST['log_time'];
        $status   = $_POST['status'];

        $stmt = $conn->prepare("UPDATE attendance_logs SET emp_id = ?, log_date = ?, log_time = ?, status = ? WHERE id = ?");
        $stmt->bind_param("isssi", $emp_id, $log_date, $log_time, $status, $id);
        $stmt->execute();
        $msg = "Attendance log updated successfully!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE attendance_logs SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Attendance deleted!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE attendance_logs SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Attendance log restored successfully!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'hard_delete' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM attendance_logs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Attendance permanently deleted!";
    }
}

// Fetch active employees for Add & Edit dropdowns
$employees = $conn->query("SELECT id, first_name, last_name FROM employees WHERE is_deleted = 0");
$emp_list = [];
while ($emp = $employees->fetch_assoc()) {
    $emp_list[] = $emp;
}

if ($role === 'Admin') {
    $logs = $conn->query("SELECT a.*, e.first_name, e.last_name FROM attendance_logs a JOIN employees e ON a.emp_id = e.id ORDER BY a.is_deleted ASC, a.log_date DESC, a.log_time DESC");
} else {
    $logs = $conn->query("SELECT a.*, e.first_name, e.last_name FROM attendance_logs a JOIN employees e ON a.emp_id = e.id WHERE a.is_deleted = 0 ORDER BY a.log_date DESC, a.log_time DESC");
}

include_once 'includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Log Attendance</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-2">
                        <label class="form-label">Employee</label>
                        <select name="emp_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($emp_list as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="log_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Time</label>
                        <input type="time" name="log_time" value="<?= date('H:i') ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Leave">Leave</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">Submit Log</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Attendance Records</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                        <tr class="<?= $log['is_deleted'] ? 'table-danger' : '' ?>">
                            <td><?= $log['log_date'] ?> <small class="text-muted"><?= $log['log_time'] ?></small></td>
                            <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></td>
                            <td>
                                <span class="badge <?= $log['status'] === 'Present' ? 'bg-success' : ($log['status'] === 'Absent' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                    <?= $log['status'] ?>
                                </span>
                                <?php if ($log['is_deleted']): ?>
                                    <span class="badge bg-dark">Deleted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$log['is_deleted']): ?>
                                    <!-- Edit Button (Triggers Modal) -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary edit-btn" 
                                            data-id="<?= $log['id'] ?>"
                                            data-emp="<?= $log['emp_id'] ?>"
                                            data-date="<?= $log['log_date'] ?>"
                                            data-time="<?= $log['log_time'] ?>"
                                            data-status="<?= $log['status'] ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAttendanceModal">
                                        Edit
                                    </button>

                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                        <button class="btn btn-sm btn-outline-warning">Delete</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($role === 'Admin' && $log['is_deleted']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                        <button class="btn btn-sm btn-outline-success">Restore</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($role === 'Admin'): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('PERMANENTLY delete this log?');">
                                        <input type="hidden" name="action" value="hard_delete">
                                        <input type="hidden" name="id" value="<?= $log['id'] ?>">
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

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_log_id">
                    
                    <div class="mb-2">
                        <label class="form-label">Employee</label>
                        <select name="emp_id" id="edit_emp_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($emp_list as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="log_date" id="edit_log_date" class="form-control" required>
                    </div>
                    <div class="mb-2">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript to populate Attendance Modal Data -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-btn');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_log_id').value   = this.getAttribute('data-id');
            document.getElementById('edit_emp_id').value   = this.getAttribute('data-emp');
            document.getElementById('edit_log_date').value = this.getAttribute('data-date');
            document.getElementById('edit_log_time').value = this.getAttribute('data-time');
            document.getElementById('edit_status').value   = this.getAttribute('data-status');
        });
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>