<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'messages';
$page_title = 'My Messages - Dong Khoi Store';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Auto-create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$user_id = intval($_SESSION['user_id']);

// Mark messages as read when viewing a conversation
$conv_with = isset($_GET['with']) ? intval($_GET['with']) : 0;
if ($conv_with > 0) {
    $conn->query("UPDATE messages SET is_read = 1 WHERE receiver_id = $user_id AND sender_id = $conv_with");
}

// Fetch all conversations (grouped by the other party)
$conv_sql = "
    SELECT 
        other_user,
        u.username,
        u.institution_name,
        MAX(m.created_at) as last_time,
        SUM(CASE WHEN m.receiver_id = $user_id AND m.is_read = 0 THEN 1 ELSE 0 END) as unread
    FROM (
        SELECT IF(sender_id = $user_id, receiver_id, sender_id) as other_user, id, created_at, receiver_id, is_read
        FROM messages
        WHERE sender_id = $user_id OR receiver_id = $user_id
    ) m
    JOIN users u ON u.id = m.other_user
    GROUP BY other_user
    ORDER BY last_time DESC
";
$conv_res = $conn->query($conv_sql);
$conversations = [];
if ($conv_res) {
    while ($row = $conv_res->fetch_assoc()) $conversations[] = $row;
}

// Fetch messages in current conversation
$thread = [];
if ($conv_with > 0) {
    $t_stmt = $conn->prepare("
        SELECT m.*, u.username as sender_name, u.institution_name as sender_inst
        FROM messages m JOIN users u ON u.id = m.sender_id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $t_stmt->bind_param('iiii', $user_id, $conv_with, $conv_with, $user_id);
    $t_stmt->execute();
    $t_res = $t_stmt->get_result();
    while ($row = $t_res->fetch_assoc()) $thread[] = $row;

    // Get the other user info
    $ou_stmt = $conn->prepare("SELECT id, username, institution_name FROM users WHERE id = ? LIMIT 1");
    $ou_stmt->bind_param('i', $conv_with);
    $ou_stmt->execute();
    $other_user = $ou_stmt->get_result()->fetch_assoc();
}

include INCLUDES_PATH . '/header.php';
?>

    <!-- Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>My Messages</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Messages</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row" style="min-height: 500px;">

            <!-- Conversation List -->
            <div class="col-md-4 border-right pr-0">
                <div class="bg-white border rounded-left" style="overflow:hidden;">
                    <div class="p-3 border-bottom" style="background:#f8f9fa;">
                        <h6 class="mb-0"><i class="fas fa-inbox mr-2 text-success"></i> Conversations</h6>
                    </div>
                    <?php if (empty($conversations)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-comment-slash fa-2x mb-2"></i>
                            <p>No conversations yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $c): ?>
                        <?php $display = htmlspecialchars($c['institution_name'] ?: $c['username']); ?>
                        <?php $initials = substr(strtoupper($c['username']), 0, 2); ?>
                        <a href="?with=<?= $c['other_user'] ?>"
                           class="d-flex align-items-center p-3 border-bottom text-dark <?= $conv_with == $c['other_user'] ? 'bg-light' : '' ?>"
                           style="text-decoration:none;">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-3 flex-shrink-0"
                                 style="width:42px;height:42px;font-weight:bold;font-size:13px;">
                                <?= $initials ?>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between">
                                    <strong style="font-size:14px;"><?= $display ?></strong>
                                    <?php if ($c['unread'] > 0): ?>
                                    <span class="badge badge-danger" style="font-size:11px;"><?= $c['unread'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted"><?= date('d/m H:i', strtotime($c['last_time'])) ?></small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Message Thread -->
            <div class="col-md-8 pl-0">
                <div class="bg-white border rounded-right d-flex flex-column" style="height:100%;min-height:500px;">
                    <?php if ($conv_with > 0 && !empty($other_user)): ?>
                        <!-- Thread Header -->
                        <div class="p-3 border-bottom d-flex align-items-center" style="background:#f8f9fa;">
                            <?php $ou_display = htmlspecialchars($other_user['institution_name'] ?: $other_user['username']); ?>
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-3"
                                 style="width:38px;height:38px;font-weight:bold;font-size:12px;">
                                <?= substr(strtoupper($other_user['username']), 0, 2) ?>
                            </div>
                            <strong><?= $ou_display ?></strong>
                        </div>

                        <!-- Messages -->
                        <div id="msg-thread" class="flex-grow-1 p-3" style="overflow-y:auto;max-height:380px;background:#fafafa;">
                            <?php foreach ($thread as $msg): ?>
                            <?php $is_mine = ($msg['sender_id'] == $user_id); ?>
                            <div class="d-flex <?= $is_mine ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
                                <div style="max-width:70%;">
                                    <?php if (!empty($msg['subject']) && $msg['subject'] !== ''): ?>
                                    <small class="text-muted d-block <?= $is_mine ? 'text-right' : '' ?>"><?= htmlspecialchars($msg['subject']) ?></small>
                                    <?php endif; ?>
                                    <div class="px-3 py-2 rounded"
                                         style="<?= $is_mine ? 'background:#d4edda;color:#155724;' : 'background:#fff;border:1px solid #dee2e6;' ?>">
                                        <?= nl2br(htmlspecialchars($msg['body'])) ?>
                                    </div>
                                    <small class="text-muted d-block <?= $is_mine ? 'text-right' : '' ?>" style="font-size:11px;">
                                        <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Reply Box -->
                        <div class="p-3 border-top">
                            <form id="reply-form">
                                <input type="hidden" name="receiver_id" value="<?= $conv_with ?>">
                                <div class="input-group">
                                    <textarea name="body" class="form-control" rows="2"
                                              placeholder="Type your message..." required
                                              style="resize:none;"></textarea>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="reply-feedback" class="mt-1"></div>
                            </form>
                        </div>

                    <?php else: ?>
                        <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted flex-column">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p>Select a conversation to view messages</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<script>
// Auto-scroll to bottom of thread
var thread = document.getElementById('msg-thread');
if (thread) thread.scrollTop = thread.scrollHeight;

// Reply form AJAX
$('#reply-form').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('button[type=submit]');
    btn.prop('disabled', true);
    $.ajax({
        url: '<?= BASE_URL ?>/app/ajax/send_message.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                location.reload();
            } else {
                $('#reply-feedback').html('<small class="text-danger">' + res.message + '</small>');
                btn.prop('disabled', false);
            }
        },
        error: function() {
            $('#reply-feedback').html('<small class="text-danger">Error sending message.</small>');
            btn.prop('disabled', false);
        }
    });
});
</script>
