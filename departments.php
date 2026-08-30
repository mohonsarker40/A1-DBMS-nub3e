<?php 
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_role'] ?? 'Employee';

// Handle POST Requests with PRG (Post/Redirect/Get) Pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['dept_name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO departments (dept_name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $_SESSION['flash_msg'] = "Department added successfully!";
        } 
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['dept_name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE departments SET dept_name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            $stmt->execute();
            $_SESSION['flash_msg'] = "Department updated successfully!";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE departments SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "Department deleted!";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && $role === 'Admin') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE departments SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_msg'] = "Department restored successfully!";
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
                <i class="fa-solid fa-building-user me-2 text-primary"></i>Add Department
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-4">
                        <label class="form-label">Department Name</label>
                        <input type="text" name="dept_name" class="form-control" required placeholder="e.g. Finance or IT">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-plus me-1"></i> Save Department
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Department Directory Table -->
    <div class="col-md-8">
        <div class="card bg-dark text-white border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3">
                <i class="fa-solid fa-sitemap me-2 text-info"></i>Department Directory
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Department Name</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($departments && $departments->num_rows > 0): ?>
                            <?php while ($row = $departments->fetch_assoc()): ?>
                            <tr class="<?= $row['is_deleted'] ? 'opacity-50' : '' ?>">
                                <td class="fw-bold text-secondary">#<?= $row['id'] ?></td>
                                <td class="fw-semibold text-white"><?= htmlspecialchars($row['dept_name']) ?></td>
                                <td>
                                    <?php if ($row['is_deleted']): ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1">Deleted</span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1">Active</span>
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
                                                Edit
                                            </button>

                                            <!-- Soft Delete -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="soft_delete">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button class="btn btn-sm btn-outline-warning">Delete</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($role === 'Admin' && $row['is_deleted']): ?>
                                            <!-- Restore Option -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button class="btn btn-sm btn-outline-success">Restore</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary">No departments found.</td>
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
                    <i class="fa-solid fa-pen-to-square text-info me-2"></i>Edit Department
                </h5>
                <button type="button" class="btn-close btn-close-white closeModal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" name="dept_name" id="edit_dept_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm closeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Update</button>
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