<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'customers';
$page_title = 'Manage Customers - Admin Panel';
include __DIR__ . '/header.php';

$message  = '';
$msg_type = '';

// ─── Handle POST actions ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if ($act === 'add') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = password_hash(trim($_POST['password'] ?: '123456'), PASSWORD_DEFAULT);
        $role     = $_POST['role'] ?? 'buyer_canteen';
        $inst     = trim($_POST['institution_name'] ?? '');
        $phone    = trim($_POST['contact_phone'] ?? '');
        $address  = trim($_POST['shipping_address'] ?? '');
        $status   = $_POST['status'] ?? 'active';

        if ($username && $email) {
            $check = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
            $check->bind_param('ss', $email, $username);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $message  = "Email or Username already exists.";
                $msg_type = 'warning';
            } else {
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, institution_name, contact_phone, shipping_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssss', $username, $email, $password, $role, $inst, $phone, $address, $status);
                if ($stmt->execute()) {
                    $message  = "Customer added successfully.";
                    $msg_type = 'success';
                } else {
                    $message  = "Error: " . $conn->error;
                    $msg_type = 'danger';
                }
            }
        } else {
            $message  = "Username and Email cannot be empty.";
            $msg_type = 'warning';
        }
    }

    if ($act === 'edit') {
        $id       = intval($_POST['id']);
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = $_POST['role'] ?? 'buyer_canteen';
        $inst     = trim($_POST['institution_name'] ?? '');
        $phone    = trim($_POST['contact_phone'] ?? '');
        $address  = trim($_POST['shipping_address'] ?? '');
        $status   = $_POST['status'] ?? 'active';
        
        if ($username && $email && $id > 0) {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, institution_name=?, contact_phone=?, shipping_address=?, status=? WHERE id=?");
            $stmt->bind_param('sssssssi', $username, $email, $role, $inst, $phone, $address, $status, $id);
            if ($stmt->execute()) {
                $message  = "Customer information updated successfully.";
                $msg_type = 'success';
                
                if (!empty($_POST['password'])) {
                    $pwd = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt_pwd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                    $stmt_pwd->bind_param('si', $pwd, $id);
                    $stmt_pwd->execute();
                }
            } else {
                $message  = "Error: " . $conn->error;
                $msg_type = 'danger';
            }
        }
    }

    if ($act === 'delete') {
        $id = intval($_POST['id']);
        $check = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE user_id = $id");
        $cnt = $check ? $check->fetch_assoc()['cnt'] : 0;
        if ($cnt > 0) {
            $message  = "Cannot delete this customer because they have $cnt orders.";
            $msg_type = 'danger';
        } else {
            if ($conn->query("DELETE FROM users WHERE id = $id")) {
                $message  = "Customer deleted successfully.";
                $msg_type = 'success';
            } else {
                $message  = "Error: " . $conn->error;
                $msg_type = 'danger';
            }
        }
    }

    if ($act === 'toggle_status') {
        $id         = intval($_POST['id']);
        $new_status = $_POST['new_status'];
        // ensure valid status
        if (in_array($new_status, ['active', 'banned', 'approved'])) {
            $conn->query("UPDATE users SET status='$new_status' WHERE id=$id");
            $message  = "Customer status updated successfully.";
            $msg_type = 'info';
        }
    }
}

// ─── Fetch customers ──────────────────────────────────────────────────────
$customers = [];
$res = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) { $customers[] = $row; }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users mr-2"></i> Manage Customers</h2>
    <button class="btn btn-admin" data-toggle="modal" data-target="#addCustomerModal">
        <i class="fas fa-plus"></i> Add Customer
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
        <h5 class="m-0"><i class="fas fa-list mr-2"></i> All Customers (<?= count($customers) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Account Type</th>
                        <th>Contact Info</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No customers yet.</td></tr>
                    <?php else: foreach ($customers as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($c['username']) ?></strong><br>
                            <small class="text-muted"><i class="fas fa-building mr-1"></i><?= htmlspecialchars($c['institution_name'] ?: '—') ?></small>
                        </td>
                        <td>
                            <?php if ($c['role'] === 'distributor'): ?>
                                <span class="badge badge-info"><i class="fas fa-truck mr-1"></i>Distributor</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><i class="fas fa-store mr-1"></i>Canteen (Buyer)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><i class="fas fa-envelope text-muted mr-1"></i><?= htmlspecialchars($c['email']) ?></small><br>
                            <small><i class="fas fa-phone text-muted mr-1"></i><?= htmlspecialchars($c['contact_phone'] ?: '—') ?></small>
                        </td>
                        <td>
                            <?php if ($c['status'] === 'active' || $c['status'] === 'approved'): ?>
                                <span class="badge badge-success px-2">Active</span>
                            <?php elseif ($c['status'] === 'pending'): ?>
                                <span class="badge badge-warning text-dark px-2">Pending</span>
                            <?php elseif ($c['status'] === 'banned' || $c['status'] === 'rejected'): ?>
                                <span class="badge badge-danger px-2"><?= ucfirst($c['status']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-secondary px-2">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info mr-1" title="Edit"
                                    onclick='openEditModal(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <?php if ($c['status'] === 'pending'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="new_status" value="active">
                                <button type="submit" class="btn btn-sm btn-success mr-1" title="Approve account">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($c['status'] === 'active' || $c['status'] === 'approved'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="new_status" value="banned">
                                <button type="submit" class="btn btn-sm btn-warning mr-1" title="Ban account">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($c['status'] === 'banned'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="new_status" value="active">
                                <button type="submit" class="btn btn-sm btn-success mr-1" title="Unban account">
                                    <i class="fas fa-unlock"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete customer &quot;<?= htmlspecialchars(addslashes($c['username'])) ?>&quot;?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Add New Customer</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Default: 123456">
                            <small class="text-muted">Leave blank to use default password: 123456</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Account Type <span class="text-danger">*</span></label>
                            <select name="role" class="form-control" required>
                                <option value="buyer_canteen">Canteen (Buyer)</option>
                                <option value="distributor">Distributor</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Institution / Organization Name</label>
                            <input type="text" name="institution_name" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Phone Number</label>
                            <input type="text" name="contact_phone" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="pending">Pending (Awaiting Approval)</option>
                                <option value="inactive">Inactive (Suspended)</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold">Shipping Address</label>
                            <textarea name="shipping_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin"><i class="fas fa-save mr-1"></i> Save Customer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Customer Information</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">New Password (Optional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Account Type <span class="text-danger">*</span></label>
                            <select name="role" id="edit_role" class="form-control" required>
                                <option value="buyer_canteen">Canteen (Buyer)</option>
                                <option value="distributor">Distributor</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Institution / Organization Name</label>
                            <input type="text" name="institution_name" id="edit_inst" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Phone Number</label>
                            <input type="text" name="contact_phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="active">Active</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending (Awaiting Approval)</option>
                                <option value="inactive">Inactive (Suspended)</option>
                                <option value="banned">Banned</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold">Shipping Address</label>
                            <textarea name="shipping_address" id="edit_address" class="form-control" rows="2"></textarea>
                        </div>
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
function openEditModal(customer) {
    document.getElementById('edit_id').value = customer.id;
    document.getElementById('edit_username').value = customer.username;
    document.getElementById('edit_email').value = customer.email;
    document.getElementById('edit_role').value = customer.role;
    document.getElementById('edit_inst').value = customer.institution_name || '';
    document.getElementById('edit_phone').value = customer.contact_phone || '';
    document.getElementById('edit_address').value = customer.shipping_address || '';
    
    let status = customer.status;
    document.getElementById('edit_status').value = status;
    
    $('#editCustomerModal').modal('show');
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
