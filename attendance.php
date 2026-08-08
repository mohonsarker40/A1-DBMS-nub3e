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
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE attendance_logs SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Attendance deleted!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'hard_delete' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM attendance_logs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Attendance permanently deleted!";
    }
}

$employees = $conn->query("SELECT id, first_name, last_name FROM employees WHERE is_deleted = 0");

if ($role === 'Admin') {
    $logs = $conn->query("SELECT a.*, e.first_name, e.last_name FROM attendance_logs a JOIN employees e ON a.emp_id = e.id ORDER BY a.log_date DESC, a.log_time DESC");
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
                            <?php while ($emp = $employees->fetch_assoc()): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                            <?php endwhile; ?>
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
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
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

<?php include_once 'includes/footer.php'; ?>