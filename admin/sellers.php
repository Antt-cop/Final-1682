<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'sellers';
$page_title = 'Manage Sellers - Admin Panel';
include __DIR__ . '/header.php';

// Get all sellers
$query = "SELECT id, username, email, institution_name, contact_phone, certificate_path, status, created_at, registration_attempts FROM users WHERE role = 'distributor' ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Sellers</h2>
</div>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['msg']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Institution / Name</th>
                        <th>Email & Phone</th>
                        <th>Certificate</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if ($result && $result->num_rows > 0): ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['institution_name'] ?: 'N/A') ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['username']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['email']) ?><br>
                                    <small><i class="fas fa-phone"></i> <?= htmlspecialchars($row['contact_phone'] ?: 'N/A') ?></small>
                                </td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if ($row['certificate_path']): 
                                        $paths = json_decode($row['certificate_path'], true);
                                        if (!is_array($paths)) {
                                            $paths = [$row['certificate_path']];
                                        }
                                    ?>
                                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach ($paths as $idx => $p): ?>
                                            <a href="<?= htmlspecialchars($p) ?>" target="_blank" class="btn btn-sm btn-outline-info mb-1">
                                                <i class="fas fa-file-download"></i> View <?= count($paths) > 1 ? ($idx + 1) : '' ?>
                                            </a><br>
                                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                        <span class="text-muted">None</span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } 
                                        $status_class = 'secondary';
                                        if ($row['status'] == 'pending') $status_class = 'warning';
                                        elseif ($row['status'] == 'approved') $status_class = 'success';
                                        elseif ($row['status'] == 'rejected') $status_class = 'danger';
                                    ?>
                                    <span class="badge badge-<?= $status_class ?> px-2 py-1"><?= strtoupper($row['status']) ?></span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if ($row['status'] == 'rejected'): ?>
                                        <br><small class="text-danger">Attempts: <?= $row['registration_attempts'] ?>/3</small>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if ($row['status'] == 'pending'): ?>
                                        <form action="/freshshop-master/app/handlers/admin_seller_handler.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this seller?')"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal<?= $row['id'] ?>"><i class="fas fa-times"></i> Reject</button>
                                        
                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?= $row['id'] ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form action="/freshshop-master/app/handlers/admin_seller_handler.php" method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Seller Registration</h5>
                                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                            <div class="form-group">
                                                                <label>Reason for Rejection <span class="text-danger">*</span></label>
                                                                <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Please enter the reason for rejection..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Confirm Reject</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>No actions</button>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </td>
                            </tr>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endwhile; ?>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                        <tr><td colspan="6" class="text-center py-4">No sellers found.</td></tr>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } include __DIR__ . '/footer.php'; ?>

