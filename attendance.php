<?php 
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_role'] ?? 'Employee';
$lang = $_SESSION['lang'] ?? 'en';

// Page Level Translation Dictionary
$att_trans = [
    'en' => [
        'msg_added'         => 'Attendance logged successfully!',
        'msg_updated'       => 'Attendance log updated successfully!',
        'msg_deleted'       => 'Attendance deleted!',
        'msg_restored'      => 'Attendance log restored successfully!',
        'msg_perm_deleted'  => 'Attendance log permanently deleted!',
        'title_add'         => 'Log Attendance',
        'title_records'     => 'Attendance Records',
        'label_employee'    => 'Employee',
        'label_date'        => 'Date',
        'label_time'        => 'Time',
        'label_status'      => 'Status',
        'select_employee'  => '-- Select Employee --',
        'status_present'    => 'Present',
        'status_absent'     => 'Absent',
        'status_leave'      => 'Leave',
        'btn_submit'        => 'Submit Log',
        'btn_edit'          => 'Edit',
        'btn_delete'        => 'Delete',
        'btn_restore'       => 'Restore',
        'btn_perm_delete'   => 'Permanent Delete',
        'btn_update'        => 'Update Log',
        'btn_cancel'        => 'Cancel',
        'th_datetime'       => 'Date & Time',
        'th_employee'       => 'Employee',
        'th_status'         => 'Status',
        'th_action'         => 'Action',
        'badge_deleted'     => 'Deleted',
        'no_records'        => 'No attendance logs found.',
        'modal_title'       => 'Edit Attendance Record',
        'confirm_trash'     => 'Move this log to trash?',
        'confirm_perm'      => 'Are you sure you want to PERMANENTLY delete this attendance log? This action cannot be undone!'
    ],
    'bn' => [
        'msg_added'         => 'উপস্থিতি সফলভাবে অন্তর্ভুক্ত করা হয়েছে!',
        'msg_updated'       => 'উপস্থিতির তথ্য সফলভাবে আপডেট করা হয়েছে!',
        'msg_deleted'       => 'উপস্থিতির তথ্য মুছে ফেলা হয়েছে!',
        'msg_restored'      => 'উপস্থিতির তথ্য সফলভাবে পুনরুদ্ধার করা হয়েছে!',
        'msg_perm_deleted'  => 'উপস্থিতির তথ্য স্থায়ীভাবে মুছে ফেলা হয়েছে!',
        'title_add'         => 'উপস্থিতি এন্ট্রি করুন',
        'title_records'     => 'উপস্থিতির ইতিহাস',
        'label_employee'    => 'কর্মচারী',
        'label_date'        => 'তারিখ',
        'label_time'        => 'সময়',
        'label_status'      => 'স্ট্যাটাস',
        'select_employee'  => '-- কর্মচারী নির্বাচন করুন --',
        'status_present'    => 'উপস্থিত (Present)',
        'status_absent'     => 'অনুপস্থিত (Absent)',
        'status_leave'      => 'ছুটি (Leave)',
        'btn_submit'        => 'জমা দিন',
        'btn_edit'          => 'সম্পাদনা',
        'btn_delete'        => 'মুছুন',
        'btn_restore'       => 'পুনরুদ্ধার',
        'btn_perm_delete'   => 'স্থায়ীভাবে মুছুন',
        'btn_update'        => 'আপডেট করুন',
        'btn_cancel'        => 'বাতিল',
        'th_datetime'       => 'তারিখ ও সময়',
        'th_employee'       => 'কর্মচারী',
        'th_status'         => 'স্ট্যাটাস',
        'th_action'         => 'অ্যাকশন',
        'badge_deleted'     => 'মুছে ফেলা হয়েছে',
        'no_records'        => 'কোনো উপস্থিতির রেকর্ড পাওয়া যায়নি।',
        'modal_title'       => 'উপস্থিতির তথ্য সংশোধন করুন',
        'confirm_trash'     => 'আপনি কি এই উপস্থিতির রেকর্ড ট্র্যাশে পাঠাতে চান?',
        'confirm_perm'      => 'আপনি কি নিশ্চিতভাবে এই উপস্থিতির তথ্য স্থায়ীভাবে মুছে ফেলতে চান? এটি আর ফিরিয়ে আনা যাবে না!'
    ]
];

$at = $att_trans[$lang] ?? $att_trans['en'];

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
        $_SESSION['flash_msg'] = $at['msg_added'];

    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id       = (int)$_POST['id'];
        $emp_id   = (int)$_POST['emp_id'];
        $log_date = $_POST['log_date'];
        $log_time = $_POST['log_time'];
        $status   = $_POST['status'];

        $stmt = $conn->prepare("UPDATE attendance_logs SET emp_id = ?, log_date = ?, log_time = ?, status = ? WHERE id = ?");
        $stmt->bind_param("isssi", $emp_id, $log_date, $log_time, $status, $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $at['msg_updated'];

    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE attendance_logs SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $at['msg_deleted'];

    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE attendance_logs SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $at['msg_restored'];

    } elseif (isset($_POST['action']) && $_POST['action'] === 'permanent_delete' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM attendance_logs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $at['msg_perm_deleted'];
    }

    header("Location: attendance.php");
    exit;
}

// Flash Message Handling
$msg = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_msg']);

// Set Bangladesh Timezone for Default Date & Time
date_default_timezone_set('Asia/Dhaka');

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

    /* Light subtile hover effect for better UI */
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
                <i class="fa-solid fa-clock me-2 text-primary"></i><?= $at['title_add'] ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label"><?= $at['label_employee'] ?></label>
                        <select name="emp_id" class="form-select" required>
                            <option value=""><?= $at['select_employee'] ?></option>
                            <?php foreach ($emp_list as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $at['label_date'] ?></label>
                        <input type="date" name="log_date" value="2026-08-30" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $at['label_time'] ?></label>
                        <input type="time" name="log_time" value="<?= date('H:i') ?>" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><?= $at['label_status'] ?></label>
                        <select name="status" class="form-select fw-semibold" required>
                            <option value="Present" class="text-success"><?= $at['status_present'] ?></option>
                            <option value="Absent" class="text-danger"><?= $at['status_absent'] ?></option>
                            <option value="Leave" class="text-warning"><?= $at['status_leave'] ?></option>
                        </select>
                    </div>

                    <button class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> <?= $at['btn_submit'] ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance Records Table -->
    <div class="col-md-8">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-list me-2 text-info"></i><?= $at['title_records'] ?>
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th><?= $at['th_datetime'] ?></th>
                            <th><?= $at['th_employee'] ?></th>
                            <th><?= $at['th_status'] ?></th>
                            <th class="text-end"><?= $at['th_action'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs && $logs->num_rows > 0): ?>
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
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1"><?= $at['status_present'] ?></span>
                                    <?php elseif ($log['status'] === 'Absent'): ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1"><?= $at['status_absent'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 px-2 py-1"><?= $at['status_leave'] ?></span>
                                    <?php endif; ?>

                                    <?php if ($log['is_deleted']): ?>
                                        <span class="badge bg-secondary ms-1"><?= $at['badge_deleted'] ?></span>
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
                                                <?= $at['btn_edit'] ?>
                                            </button>

                                            <!-- Soft Delete Form -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $at['confirm_trash'] ?>');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                                <button class="btn btn-sm btn-outline-warning"><?= $at['btn_delete'] ?></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($role === 'Admin' && $log['is_deleted']): ?>
                                            <!-- Restore Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                                <button class="btn btn-sm btn-outline-success"><?= $at['btn_restore'] ?></button>
                                            </form>

                                            <!-- Permanent Delete (Purge) Form -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $at['confirm_perm'] ?>');">
                                                <input type="hidden" name="action" value="permanent_delete">
                                                <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger"><?= $at['btn_perm_delete'] ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary"><?= $at['no_records'] ?></td>
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
                    <i class="fa-solid fa-pen-to-square text-info me-2"></i><?= $at['modal_title'] ?>
                </h5>
                <button type="button" class="btn-close btn-close-white closeModal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_log_id">
                    
                    <div class="mb-3">
                        <label class="form-label"><?= $at['label_employee'] ?></label>
                        <select name="emp_id" id="edit_emp_id" class="form-select" required>
                            <option value=""><?= $at['select_employee'] ?></option>
                            <?php foreach ($emp_list as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $at['label_date'] ?></label>
                        <input type="date" name="log_date" id="edit_log_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $at['label_time'] ?></label>
                        <input type="time" name="log_time" id="edit_log_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $at['label_status'] ?></label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="Present"><?= $at['status_present'] ?></option>
                            <option value="Absent"><?= $at['status_absent'] ?></option>
                            <option value="Leave"><?= $at['status_leave'] ?></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm closeModal"><?= $at['btn_cancel'] ?></button>
                    <button type="submit" class="btn btn-primary btn-sm px-3"><?= $at['btn_update'] ?></button>
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