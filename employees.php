<?php 
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_role'] ?? 'Employee';
$lang = $_SESSION['lang'] ?? 'en';

// Page Level Translation Dictionary
$emp_trans = [
    'en' => [
        'msg_added'         => 'Employee registered successfully!',
        'msg_updated'       => 'Employee record updated successfully!',
        'msg_deleted'       => 'Employee deleted!',
        'msg_restored'      => 'Employee restored successfully!',
        'msg_perm_deleted'  => 'Employee permanently deleted!',
        'title_add'         => 'Add Employee',
        'title_records'     => 'Employee Records',
        'label_fname'       => 'First Name',
        'label_lname'       => 'Last Name',
        'label_email'       => 'Email',
        'label_salary'      => 'Salary',
        'label_dept'        => 'Department',
        'label_hire_date'   => 'Hire Date',
        'select_dept'       => '-- Select Dept --',
        'btn_register'      => 'Register Employee',
        'btn_edit'          => 'Edit',
        'btn_delete'        => 'Delete',
        'btn_restore'       => 'Restore',
        'btn_perm_delete'   => 'Permanent Delete',
        'btn_update'        => 'Update Record',
        'btn_cancel'        => 'Cancel',
        'th_name'           => 'Name',
        'th_email'          => 'Email',
        'th_dept'           => 'Dept',
        'th_salary'         => 'Salary',
        'th_action'         => 'Action',
        'badge_deleted'     => 'Deleted',
        'no_records'        => 'No employee records found.',
        'modal_title'       => 'Edit Employee Record',
        'confirm_trash'     => 'Move this employee to trash?',
        'confirm_perm'      => 'Are you sure you want to PERMANENTLY delete this employee record? This action cannot be undone!'
    ],
    'bn' => [
        'msg_added'         => 'কর্মচারী সফলভাবে নিবন্ধিত হয়েছে!',
        'msg_updated'       => 'কর্মচারীর তথ্য সফলভাবে আপডেট করা হয়েছে!',
        'msg_deleted'       => 'কর্মচারী মুছে ফেলা হয়েছে!',
        'msg_restored'      => 'কর্মচারী সফলভাবে পুনরুদ্ধার করা হয়েছে!',
        'msg_perm_deleted'  => 'কর্মচারী স্থায়ীভাবে মুছে ফেলা হয়েছে!',
        'title_add'         => 'নতুন কর্মচারী যোগ করুন',
        'title_records'     => 'কর্মচারীদের তালিকা',
        'label_fname'       => 'প্রথম নাম',
        'label_lname'       => 'শেষ নাম',
        'label_email'       => 'ইমেইল',
        'label_salary'      => 'বেতন',
        'label_dept'        => 'ডিপার্টমেন্ট',
        'label_hire_date'   => 'যোগদানের তারিখ',
        'select_dept'       => '-- ডিপার্টমেন্ট নির্বাচন করুন --',
        'btn_register'      => 'কর্মচারী নিবন্ধন করুন',
        'btn_edit'          => 'সম্পাদনা',
        'btn_delete'        => 'মুছুন',
        'btn_restore'       => 'পুনরুদ্ধার',
        'btn_perm_delete'   => 'স্থায়ীভাবে মুছুন',
        'btn_update'        => 'রেকর্ড আপডেট করুন',
        'btn_cancel'        => 'বাতিল',
        'th_name'           => 'নাম',
        'th_email'          => 'ইমেইল',
        'th_dept'           => 'ডিপার্টমেন্ট',
        'th_salary'         => 'বেতন',
        'th_action'         => 'অ্যাকশন',
        'badge_deleted'     => 'মুছে ফেলা হয়েছে',
        'no_records'        => 'কোনো কর্মচারীর রেকর্ড পাওয়া যায়নি।',
        'modal_title'       => 'কর্মচারীর তথ্য সংশোধন করুন',
        'confirm_trash'     => 'আপনি কি এই কর্মচারীকে ট্র্যাশে পাঠাতে চান?',
        'confirm_perm'      => 'আপনি কি নিশ্চিতভাবে এই কর্মচারীর তথ্য স্থায়ীভাবে মুছে ফেলতে চান? এটি আর ফিরিয়ে আনা যাবে না!'
    ]
];

$et = $emp_trans[$lang] ?? $emp_trans['en'];

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
        $_SESSION['flash_msg'] = $et['msg_added'];

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
        $_SESSION['flash_msg'] = $et['msg_updated'];

    } elseif (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE employees SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $et['msg_deleted'];

    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE employees SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $et['msg_restored'];

    } elseif (isset($_POST['action']) && $_POST['action'] === 'permanent_delete' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $et['msg_perm_deleted'];
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
                <i class="fa-solid fa-user-plus me-2 text-primary"></i><?= $et['title_add'] ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label"><?= $et['label_fname'] ?></label>
                            <input type="text" name="first_name" class="form-control" required placeholder="John">
                        </div>
                        <div class="col-6">
                            <label class="form-label"><?= $et['label_lname'] ?></label>
                            <input type="text" name="last_name" class="form-control" required placeholder="Doe">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $et['label_email'] ?></label>
                        <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $et['label_salary'] ?></label>
                        <input type="number" step="0.01" name="salary" class="form-control" required placeholder="50000">
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $et['label_dept'] ?></label>
                        <select name="dept_id" class="form-select" required>
                            <option value=""><?= $et['select_dept'] ?></option>
                            <?php foreach ($dept_list as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><?= $et['label_hire_date'] ?></label>
                        <input type="date" name="hire_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> <?= $et['btn_register'] ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Employee Directory Table -->
    <div class="col-md-8">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-users me-2 text-info"></i><?= $et['title_records'] ?>
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th><?= $et['th_name'] ?></th>
                            <th><?= $et['th_email'] ?></th>
                            <th><?= $et['th_dept'] ?></th>
                            <th><?= $et['th_salary'] ?></th>
                            <th class="text-end"><?= $et['th_action'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($employees && $employees->num_rows > 0): ?>
                            <?php while ($e = $employees->fetch_assoc()): ?>
                            <tr class="<?= $e['is_deleted'] ? 'opacity-50' : '' ?>">
                                <td class="fw-semibold text-white">
                                    <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                                    <?php if ($e['is_deleted']): ?>
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;"><?= $et['badge_deleted'] ?></span>
                                    <?php endif; ?>
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
                                                <?= $et['btn_edit'] ?>
                                            </button>

                                            <!-- Soft Delete Form -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $et['confirm_trash'] ?>');">
                                                <input type="hidden" name="action" value="soft_delete">
                                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                                <button class="btn btn-sm btn-outline-warning"><?= $et['btn_delete'] ?></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($role === 'Admin' && $e['is_deleted']): ?>
                                            <!-- Restore Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                                <button class="btn btn-sm btn-outline-success"><?= $et['btn_restore'] ?></button>
                                            </form>

                                            <!-- Permanent Delete (Purge) Form -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $et['confirm_perm'] ?>');">
                                                <input type="hidden" name="action" value="permanent_delete">
                                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger"><?= $et['btn_perm_delete'] ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-secondary"><?= $et['no_records'] ?></td>
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
                    <i class="fa-solid fa-user-pen text-info me-2"></i><?= $et['modal_title'] ?>
                </h5>
                <button type="button" class="btn-close btn-close-white closeModal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_emp_id">
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label"><?= $et['label_fname'] ?></label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label"><?= $et['label_lname'] ?></label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $et['label_email'] ?></label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $et['label_salary'] ?></label>
                        <input type="number" step="0.01" name="salary" id="edit_salary" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label"><?= $et['label_dept'] ?></label>
                        <select name="dept_id" id="edit_dept_id" class="form-select" required>
                            <option value=""><?= $et['select_dept'] ?></option>
                            <?php foreach ($dept_list as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $et['label_hire_date'] ?></label>
                        <input type="date" name="hire_date" id="edit_hire_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm closeModal"><?= $et['btn_cancel'] ?></button>
                    <button type="submit" class="btn btn-primary btn-sm px-3"><?= $et['btn_update'] ?></button>
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