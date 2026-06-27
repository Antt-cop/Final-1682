<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'product_approvals';
$page_title = 'Product Approvals - Admin Panel';
include __DIR__ . '/header.php';

// Fetch pending products
$products = [];
$prod_res = $conn->query("
    SELECT p.*, c.name as category_name, u.username as seller_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN users u ON p.seller_id = u.id
    WHERE p.approval_status = 'pending'
    ORDER BY p.id ASC
");
if ($prod_res) {
    while ($row = $prod_res->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Pending Product Approvals</h2>
</div>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h4 class="m-0"><i class="fas fa-clipboard-list"></i> Needs Review (<?= count($products) ?>)</h4>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Seller</th>
                        <th>Product</th>
                        <th>Type/Category</th>
                        <th>Details</th>
                        <th>Safety Certificate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(empty($products)): ?>
                        <tr><td colspan="7" class="text-center py-4">No pending products to review.</td></tr>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['seller_name'] ?? 'Unknown') ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($p['image_url']): ?>
                                            <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                                            <span class="text-muted">$<?= number_format($p['base_price'], 2) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['category_name']) ?><br>
                                    <small class="badge badge-secondary"><?= $p['storage_type'] === 'non_storable' ? 'Pre-order' : 'Storable' ?></small>
                                </td>
                                <td>
                                    <small><strong>Origin:</strong> <?= htmlspecialchars($p['origin'] ?? 'N/A') ?><br>
                                    <strong>Prod:</strong> <?= $p['production_date'] ? htmlspecialchars($p['production_date']) : 'N/A' ?><br>
                                    <strong>Exp:</strong> <?= $p['expiration_date'] ? htmlspecialchars($p['expiration_date']) : 'N/A' ?></small>
                                </td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($p['safety_certificate']): ?>
                                        <a href="<?= htmlspecialchars($p['safety_certificate']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-file-alt"></i> View</a>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                        <span class="text-muted">No File</span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </td>
                                <td>
                                    <form action="/freshshop-master/app/handlers/admin_product_approvals_handler.php" method="POST" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this product?');" title="Approve"><i class="fas fa-check"></i></button>
                                    </form>
                                    
                                    <button type="button" class="btn btn-sm btn-danger" onclick="openRefuseModal(<?= $p['id'] ?>)" title="Refuse"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Refuse Modal -->
<div class="modal fade" id="refuseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Refuse Product</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/freshshop-master/app/handlers/admin_product_approvals_handler.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="refuse">
                    <input type="hidden" name="product_id" id="refuseProductId" value="">
                    <p>Please provide a reason for refusing this product. The seller will see this reason.</p>
                    <div class="form-group">
                        <label>Rejection Reason *</label>
                        <textarea class="form-control" name="rejection_reason" rows="3" required placeholder="E.g., Invalid certificate, unclear origin..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Refusal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRefuseModal(id) {
    document.getElementById('refuseProductId').value = id;
    $('#refuseModal').modal('show');
}
</script>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } include __DIR__ . '/footer.php'; ?>

