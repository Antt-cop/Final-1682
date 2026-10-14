<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name  = 'ghn_settings';
$page_title = 'GHN Settings - Admin Panel';
include __DIR__ . '/header.php';

$message  = '';
$msg_type = '';

// ─── Handle POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_shop') {
        $shop_id   = trim($_POST['shop_id']   ?? '');
        $token     = trim($_POST['token']      ?? '');
        $shop_name = trim($_POST['shop_name']  ?? 'Dong Khoi Store');

        if ($shop_id && $token) {
            // Single store GHN config – user_id IS NULL (system/admin)
            $existing = $conn->query("SELECT id FROM ghn_shops WHERE user_id IS NULL LIMIT 1");

            if ($existing && $existing->num_rows > 0) {
                $rec_id = $existing->fetch_assoc()['id'];
                $stmt   = $conn->prepare("UPDATE ghn_shops SET shop_id=?, token=?, shop_name=?, is_default=1 WHERE id=?");
                $stmt->bind_param("sssi", $shop_id, $token, $shop_name, $rec_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO ghn_shops (user_id, shop_id, token, shop_name, is_default) VALUES (NULL, ?, ?, ?, 1)");
                $stmt->bind_param("sss", $shop_id, $token, $shop_name);
            }

            if ($stmt->execute()) {
                $message  = "GHN configuration saved successfully.";
                $msg_type = 'success';
            } else {
                $message  = "DB Error: " . $conn->error;
                $msg_type = 'danger';
            }
        } else {
            $message  = "Please enter Shop ID and Token.";
            $msg_type = 'warning';
        }
    }

    if ($action === 'clear_shop') {
        $conn->query("DELETE FROM ghn_shops WHERE user_id IS NULL");
        $message  = "GHN configuration cleared.";
        $msg_type = 'success';
    }
}

// ─── Fetch current store GHN config (system / admin – user_id IS NULL) ───────
$shop = null;
$res  = $conn->query("SELECT * FROM ghn_shops WHERE user_id IS NULL LIMIT 1");
if ($res && $res->num_rows > 0) {
    $shop = $res->fetch_assoc();
}
// Fallback: pick any default if no system shop exists
if (!$shop) {
    $res2 = $conn->query("SELECT * FROM ghn_shops WHERE is_default=1 LIMIT 1");
    if ($res2 && $res2->num_rows > 0) { $shop = $res2->fetch_assoc(); }
}
?>

<style>
.ghn-card        { border: 1px solid #dee2e6; border-radius: 12px; overflow: hidden; }
.ghn-card-header { background: linear-gradient(135deg,#1a1a2e 0%,#16213e 100%); color:#fff; padding: 20px 24px; }
.token-field     { font-family: monospace; letter-spacing: .05em; }
.status-pill     { border-radius: 50px; padding: 4px 14px; font-size: 13px; font-weight: 600; }
.guide-card      { background: linear-gradient(135deg,#0f2027,#203a43,#2c5364); color:#fff; border-radius:12px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-shipping-fast mr-2 text-success"></i> GHN Settings</h2>
    <?php if ($shop): ?>
    <span class="badge badge-pill badge-success status-pill">
        <i class="fas fa-check-circle mr-1"></i> Configured
    </span>
    <?php else: ?>
    <span class="badge badge-pill badge-secondary status-pill">
        <i class="fas fa-exclamation-circle mr-1"></i> Not Configured
    </span>
    <?php endif; ?>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $msg_type ?> alert-dismissible fade show">
    <i class="fas fa-<?= $msg_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> mr-2"></i>
    <?= htmlspecialchars($message) ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">

        <!-- Main GHN Config Card -->
        <div class="card ghn-card shadow-sm mb-4">
            <div class="ghn-card-header d-flex align-items-center">
                <div class="mr-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;background:rgba(255,255,255,0.15);">
                        <i class="fas fa-store fa-lg text-warning"></i>
                    </div>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold">GHN Store Configuration</h5>
                    <small style="opacity:.75;">Dong Khoi Store – Giao Hang Nhanh API</small>
                </div>
                <?php if ($shop): ?>
                <span class="ml-auto badge badge-info px-3 py-2">
                    <i class="fas fa-link mr-1"></i> Connected
                </span>
                <?php endif; ?>
            </div>
            <div class="card-body p-4">
                <form method="POST" id="ghnForm">
                    <input type="hidden" name="action" value="save_shop">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Display Name (Shop Name)</label>
                                <input type="text" name="shop_name" class="form-control"
                                       value="<?= htmlspecialchars($shop['shop_name'] ?? 'Dong Khoi Store') ?>"
                                       placeholder="e.g. Dong Khoi Store">
                                <small class="text-muted">Friendly display name (optional)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Shop ID <span class="text-danger">*</span></label>
                                <input type="text" name="shop_id" id="shop_id" class="form-control"
                                       value="<?= htmlspecialchars($shop['shop_id'] ?? '') ?>"
                                       placeholder="e.g. 201586" required>
                                <small class="text-muted">Retrieved from GHN Portal &rarr; Settings &rarr; Store Information (Shop ID). <strong class="text-warning">Note: Do not use Client ID!</strong></small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">API Token <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="token" id="ghn_token"
                                   class="form-control token-field"
                                   value="<?= htmlspecialchars($shop['token'] ?? '') ?>"
                                   placeholder="GHN API Token" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('ghn_token', this)" title="Show/Hide token">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Get from GHN Portal &rarr; Settings &rarr; API</small>
                    </div>

                    <div class="d-flex align-items-center mt-4" style="gap:10px; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Save Configuration
                        </button>
                        <?php if ($shop): ?>
                        <button type="button" class="btn btn-outline-info" onclick="testGHN(<?= $shop['id'] ?>, this)">
                            <i class="fas fa-wifi mr-1"></i> Test Connection
                        </button>
                        <span class="ghn-test-result small"></span>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($shop): ?>
                <hr>
                <form method="POST" class="mt-2" onsubmit="return confirm('Delete all GHN configuration? Shipping function will stop working.')">
                    <input type="hidden" name="action" value="clear_shop">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash mr-1"></i> Delete GHN Configuration
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Summary -->
        <?php if ($shop): ?>
        <div class="card border-success shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row text-center">
                    <div class="col-4 border-right">
                        <div class="h5 mb-0 text-success font-weight-bold"><?= htmlspecialchars($shop['shop_name'] ?? '—') ?></div>
                        <small class="text-muted">GHN Store Name</small>
                    </div>
                    <div class="col-4 border-right">
                        <div class="h5 mb-0 text-primary font-weight-bold"><?= htmlspecialchars($shop['shop_id']) ?></div>
                        <small class="text-muted">Shop ID</small>
                    </div>
                    <div class="col-4">
                        <div class="h5 mb-0 text-success font-weight-bold">
                            <i class="fas fa-check-circle"></i> Active
                        </div>
                        <small class="text-muted">Status</small>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>GHN not configured!</strong> Order push to GHN will not work until you set up Shop ID and Token.
        </div>
        <?php endif; ?>

    </div>

    <div class="col-lg-4">
        <!-- Guide Card -->
        <div class="guide-card p-4 mb-4">
            <h6 class="font-weight-bold text-warning mb-3">
                <i class="fas fa-book mr-2"></i> How to Get GHN API Credentials
            </h6>
            <ol class="pl-3 mb-0" style="line-height: 2;">
                <li>Log in to <a href="https://5sao.ghn.vn" target="_blank" class="text-warning font-weight-bold">GHN Portal</a></li>
                <li>Go to <em>Settings &rarr; Store Information</em></li>
                <li>Copy <strong>Shop ID</strong> (e.g. <code>201586</code> &mdash; <em>do not copy Client ID</em>)</li>
                <li>Go to <em>Settings &rarr; Token Management &rarr; API Token</em></li>
                <li>Copy <strong>API Token</strong></li>
                <li>Paste into the form and click <strong>Save Configuration</strong></li>
            </ol>
        </div>

        <!-- What GHN is used for -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="m-0"><i class="fas fa-info-circle text-info mr-2"></i> What is GHN used for?</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0" style="line-height: 2.2; font-size: 14px;">
                    <li><i class="fas fa-check text-success mr-2"></i> Calculate shipping fees at checkout</li>
                    <li><i class="fas fa-check text-success mr-2"></i> Create shipping labels &amp; push orders</li>
                    <li><i class="fas fa-check text-success mr-2"></i> Get tracking codes for customers</li>
                    <li><i class="fas fa-check text-success mr-2"></i> Update status via Webhook</li>
                </ul>
            </div>
        </div>

        <?php if ($shop): ?>
        <div class="card border-info shadow-sm">
            <div class="card-body text-center py-3">
                <a href="<?= BASE_URL ?>/admin/orders.php?status=processing" class="btn btn-outline-info btn-sm btn-block">
                    <i class="fas fa-paper-plane mr-1"></i> View Orders to Push to GHN
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePwd(inputId, btn) {
    var inp  = document.getElementById(inputId);
    var icon = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}

function testGHN(shopDbId, btn) {
    var $btn    = $(btn);
    var $result = $btn.closest('.d-flex').find('.ghn-test-result');
    $btn.prop('disabled', true);
    $result.html('<i class="fas fa-spinner fa-spin text-muted"></i> Testing connection...');
    $.post('<?= BASE_URL ?>/app/handlers/ghn_settings.php', {
        action: 'test_connection',
        shop_id: shopDbId
    }, function(res) {
        try {
            var r = JSON.parse(res);
            if (r.status === 'success') {
                $result.html('<span class="text-success"><i class="fas fa-check-circle"></i> Connection OK!</span>');
            } else {
                $result.html('<span class="text-danger"><i class="fas fa-times-circle"></i> ' + r.message + '</span>');
            }
        } catch(e) {
            $result.html('<span class="text-danger">Response error</span>');
        }
        $btn.prop('disabled', false);
    }).fail(function() {
        $result.html('<span class="text-danger">Cannot connect to server</span>');
        $btn.prop('disabled', false);
    });
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
