<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name  = 'categories';
$page_title = 'Manage Categories - Admin Panel';
include __DIR__ . '/header.php';

$message  = '';
$msg_type = '';

// ─── Handle POST actions ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if ($act === 'add') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name) {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, status, created_by) VALUES (?, ?, 'approved', ?)");
            $stmt->bind_param('ssi', $name, $desc, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $message  = "Category \"$name\" created successfully.";
                $msg_type = 'success';
            } else {
                $message  = "Error: " . $conn->error;
                $msg_type = 'danger';
            }
        } else {
            $message  = "Category name cannot be empty.";
            $msg_type = 'warning';
        }
    }

    if ($act === 'edit') {
        $id   = intval($_POST['id']);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name && $id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
            $stmt->bind_param('ssi', $name, $desc, $id);
            if ($stmt->execute()) {
                $message  = "Category updated successfully.";
                $msg_type = 'success';
            } else {
                $message  = "Error: " . $conn->error;
                $msg_type = 'danger';
            }
        }
    }

    if ($act === 'delete') {
        $id = intval($_POST['id']);
        // Check if any products use this category
        $check = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE category_id = $id");
        $cnt = $check ? $check->fetch_assoc()['cnt'] : 0;
        if ($cnt > 0) {
            $message  = "Cannot delete this category because $cnt products are using it.";
            $msg_type = 'danger';
        } else {
            if ($conn->query("DELETE FROM categories WHERE id = $id")) {
                $message  = "Category deleted successfully.";
                $msg_type = 'success';
            } else {
                $message  = "Error: " . $conn->error;
                $msg_type = 'danger';
            }
        }
    }

    if ($act === 'toggle_status') {
        $id         = intval($_POST['id']);
        $new_status = $_POST['new_status'] === 'approved' ? 'approved' : 'inactive';
        $conn->query("UPDATE categories SET status='$new_status' WHERE id=$id");
        $message  = "Category status updated successfully.";
        $msg_type = 'info';
    }
}

// ─── Fetch categories ─────────────────────────────────────────────────────
$categories = [];
$res = $conn->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count
    FROM categories c
    ORDER BY c.status DESC, c.name ASC
");
if ($res) {
    while ($row = $res->fetch_assoc()) { $categories[] = $row; }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tags mr-2"></i> Manage Categories</h2>
    <button class="btn btn-admin" data-toggle="modal" data-target="#addCatModal">
        <i class="fas fa-plus"></i> Add Category
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $msg_type ?> alert-dismissible fade show">
    <?= htmlspecialchars($message) ?>
    <button class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="m-0"><i class="fas fa-list mr-2"></i> All Categories (<?= count($categories) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No categories yet.</td></tr>
                    <?php else: foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                        <td><small class="text-muted"><?= htmlspecialchars($cat['description'] ?? '—') ?></small></td>
                        <td>
                            <span class="badge badge-<?= $cat['product_count'] > 0 ? 'primary' : 'secondary' ?>">
                                <?= $cat['product_count'] ?> products
                            </span>
                        </td>
                        <td>
                            <?php if ($cat['status'] === 'approved'): ?>
                                <span class="badge badge-success px-2">Active</span>
                            <?php elseif ($cat['status'] === 'pending'): ?>
                                <span class="badge badge-warning text-dark px-2">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-secondary px-2">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info mr-1"
                                    onclick="openEditModal(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>', '<?= htmlspecialchars(addslashes($cat['description'] ?? '')) ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($cat['status'] !== 'approved'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <input type="hidden" name="new_status" value="approved">
                                <button type="submit" class="btn btn-sm btn-success mr-1" title="Activate">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if ($cat['product_count'] == 0): ?>
                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete category \"<?= htmlspecialchars($cat['name']) ?>\"?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Add New Category</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Dry Food, Beverages...">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the category..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin"><i class="fas fa-save mr-1"></i> Create Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editCatId">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Category</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editCatName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Description</label>
                        <textarea name="description" id="editCatDesc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white"><i class="fas fa-save mr-1"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, desc) {
    document.getElementById('editCatId').value   = id;
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatDesc').value = desc;
    $('#editCatModal').modal('show');
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
