<?php 
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_role'] ?? 'Employee';
$lang = $_SESSION['lang'] ?? 'en';

// Page Level Translation Dictionary
$dept_trans = [
    'en' => [
        'msg_added'         => 'Department added successfully!',
        'msg_updated'       => 'Department updated successfully!',
        'msg_deleted'       => 'Department deleted!',
        'msg_restored'      => 'Department restored successfully!',
        'msg_perm_deleted'  => 'Department permanently deleted from database!',
        'title_add'         => 'Add Department',
        'title_directory'   => 'Department Directory',
        'label_name'        => 'Department Name',
        'placeholder_name'  => 'e.g. Finance or IT',
        'btn_save'          => 'Save Department',
        'btn_edit'          => 'Edit',
        'btn_delete'        => 'Delete',
        'btn_restore'       => 'Restore',
        'btn_perm_delete'   => 'Permanent Delete',
        'btn_update'        => 'Update',
        'btn_cancel'        => 'Cancel',
        'th_id'             => 'ID',
        'th_name'           => 'Department Name',
        'th_status'         => 'Status',
        'th_action'         => 'Action',
        'status_deleted'    => 'Deleted',
        'status_active'     => 'Active',
        'no_depts'          => 'No departments found.',
        'modal_title'       => 'Edit Department',
        'confirm_trash'     => 'Move this department to trash?',
        'confirm_perm'      => 'Are you sure you want to PERMANENTLY delete this department? This cannot be undone!'
    ],
    'bn' => [
        'msg_added'         => 'ডিপার্টমেন্ট সফলভাবে যোগ করা হয়েছে!',
        'msg_updated'       => 'ডিপার্টমেন্ট সফলভাবে আপডেট করা হয়েছে!',
        'msg_deleted'       => 'ডিপার্টমেন্ট মুছে ফেলা হয়েছে!',
        'msg_restored'      => 'ডিপার্টমেন্ট সফলভাবে পুনরুদ্ধার করা হয়েছে!',
        'msg_perm_deleted'  => 'ডেটাবেস থেকে ডিপার্টমেন্ট স্থায়ীভাবে মুছে ফেলা হয়েছে!',
        'title_add'         => 'নতুন ডিপার্টমেন্ট যোগ করুন',
        'title_directory'   => 'ডিপার্টমেন্ট ডিরেক্টরি',
        'label_name'        => 'ডিপার্টমেন্টের নাম',
        'placeholder_name'  => 'যেমন: অর্থ বা আইটি',
        'btn_save'          => 'ডিপার্টমেন্ট সেভ করুন',
        'btn_edit'          => 'সম্পাদনা',
        'btn_delete'        => 'মুছুন',
        'btn_restore'       => 'পুনরুদ্ধার',
        'btn_perm_delete'   => 'স্থায়ীভাবে মুছুন',
        'btn_update'        => 'আপডেট করুন',
        'btn_cancel'        => 'বাতিল',
        'th_id'             => 'আইডি',
        'th_name'           => 'ডিপার্টমেন্টের নাম',
        'th_status'         => 'স্ট্যাটাস',
        'th_action'         => 'অ্যাকশন',
        'status_deleted'    => 'মুছে ফেলা হয়েছে',
        'status_active'     => 'সক্রিয়',
        'no_depts'          => 'কোনো ডিপার্টমেন্ট পাওয়া যায়নি।',
        'modal_title'       => 'ডিপার্টমেন্ট সংশোধন করুন',
        'confirm_trash'     => 'আপনি কি এই ডিপার্টমেন্টকে ট্র্যাশে পাঠাতে চান?',
        'confirm_perm'      => 'আপনি কি নিশ্চিতভাবে এই ডিপার্টমেন্টকে স্থায়ীভাবে মুছে ফেলতে চান? এটি আর ফিরিয়ে আনা যাবে না!'
    ]
];

$dt = $dept_trans[$lang] ?? $dept_trans['en'];

// Handle POST Requests with PRG (Post/Redirect/Get) Pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['dept_name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO departments (dept_name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $_SESSION['flash_msg'] = $dt['msg_added'];
        } 
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['dept_name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE departments SET dept_name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            $stmt->execute();
            $_SESSION['flash_msg'] = $dt['msg_updated'];
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE departments SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $dt['msg_deleted'];
    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE departments SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $dt['msg_restored'];
    } elseif (isset($_POST['action']) && $_POST['action'] === 'permanent_delete' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = $dt['msg_perm_deleted'];
    }

    // Redirect to self to prevent form resubmission on page refresh
    header("Location: departments.php");
    exit;
}

// Flash Message Handling
$msg = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_msg']);

// Admins see all departments; Non-Admins only see active departments
if ($role === 'Admin') {
    $departments = $conn->query("SELECT * FROM departments ORDER BY is_deleted ASC, id DESC");
} else {
    $departments = $conn->query("SELECT * FROM departments WHERE is_deleted = 0 ORDER BY id DESC");
}

include_once 'includes/header.php';
?>

<!-- Custom CSS for Dark UI, Table & Modal Backdrop Fix -->
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
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #cbd5e1;
    }

    /* Force Remove Default Backdrop overlay issue */
    .modal-backdrop {
        display: none !important;
    }

    /* Modal Styling with Built-in Dimmed Overlay */
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

    /* Table Custom Dark Styling & Hover Off */
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

    /* Completely Remove White Hover Effect */
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

    /* Soft Subtile Hover Effect */
    .table-dark-custom tbody tr:hover td {
        background-color: rgba(255, 255, 255, 0.03) !important;
    }
</style>

<!-- Flash Alert Message -->
<?php if ($msg): ?>
    <div class="alert alert-success bg-success bg-opacity-25 border border-success text-white alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2 text-success"></i><?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add Department Form -->
    <div class="col-md-4">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-building-user me-2 text-primary"></i><?= $dt['title_add'] ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-4">
                        <label class="form-label"><?= $dt['label_name'] ?></label>
                        <input type="text" name="dept_name" class="form-control" required placeholder="<?= $dt['placeholder_name'] ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-plus me-1"></i> <?= $dt['btn_save'] ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Department Directory Table -->
    <div class="col-md-8">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-sitemap me-2 text-info"></i><?= $dt['title_directory'] ?>
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th><?= $dt['th_name'] ?></th>
                            <th><?= $dt['th_status'] ?></th>
                            <th class="text-end"><?= $dt['th_action'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($departments && $departments->num_rows > 0): ?>
                            <?php while ($row = $departments->fetch_assoc()): ?>
                            <tr class="<?= $row['is_deleted'] ? 'opacity-50' : '' ?>">
                                <td class="fw-semibold text-white"><?= htmlspecialchars($row['dept_name']) ?></td>
                                <td>
                                    <?php if ($row['is_deleted']): ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1"><?= $dt['status_deleted'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1"><?= $dt['status_active'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <?php if (!$row['is_deleted']): ?>
                                            <!-- Edit Button -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info edit-btn" 
                                                    data-id="<?= $row['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($row['dept_name'], ENT_QUOTES) ?>">
                                                <?= $dt['btn_edit'] ?>
                                            </button>

                                            <!-- Soft Delete -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $dt['confirm_trash'] ?>');">
                                                <input type="hidden" name="action" value="soft_delete">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button class="btn btn-sm btn-outline-warning"><?= $dt['btn_delete'] ?></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($role === 'Admin' && $row['is_deleted']): ?>
                                            <!-- Restore Option -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button class="btn btn-sm btn-outline-success"><?= $dt['btn_restore'] ?></button>
                                            </form>

                                            <!-- Permanent Delete (Purge) Option -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $dt['confirm_perm'] ?>');">
                                                <input type="hidden" name="action" value="permanent_delete">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger"><?= $dt['btn_perm_delete'] ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary"><?= $dt['no_depts'] ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white" id="editModalLabel">
                    <i class="fa-solid fa-pen-to-square text-info me-2"></i><?= $dt['modal_title'] ?>
                </h5>
                <button type="button" class="btn-close btn-close-white closeModal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label"><?= $dt['label_name'] ?></label>
                        <input type="text" name="dept_name" id="edit_dept_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm closeModal"><?= $dt['btn_cancel'] ?></button>
                    <button type="submit" class="btn btn-primary btn-sm px-3"><?= $dt['btn_update'] ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Standalone JS System for Smooth Modal Control -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('editModal');
    const editIdInput = document.getElementById('edit_id');
    const editNameInput = document.getElementById('edit_dept_name');

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

    // Handle Edit Click
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');

            editIdInput.value = id;
            editNameInput.value = name;

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