<?php
/**
 * BillingStatementService.php
 * SCM Consolidated Billing & Statement of Account (SOA) Service
 * Reconciles periodic institutional releases and urgent ad-hoc orders into unified settlements.
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/CreditManagementService.php';

class BillingStatementService {
    private $conn;
    private $creditService;

    public function __construct($conn = null) {
        $this->conn = $conn ?: getDbConnection();
        $this->creditService = new CreditManagementService($this->conn);
    }

    /**
     * Get all billing statements for a specific buyer
     */
    public function getStatementsByUser(int $userId): array {
        $stmt = $this->conn->prepare("
            SELECT s.*, 
                   COUNT(o.id) AS total_orders_count,
                   DATEDIFF(s.due_date, CURDATE()) AS days_until_due
            FROM billing_statements s
            LEFT JOIN orders o ON o.statement_id = s.id
            WHERE s.user_id = ?
            GROUP BY s.id
            ORDER BY s.id DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $statements = [];
        while ($row = $res->fetch_assoc()) {
            $statements[] = $row;
        }
        return $statements;
    }

    /**
     * Get detailed Statement of Account with attached orders and customer profile
     */
    public function getStatementDetails(int $statementId): ?array {
        $stmt = $this->conn->prepare("
            SELECT s.*, 
                   u.username, u.email, u.institution_name, u.contact_phone, u.shipping_address,
                   a.agreement_code, a.title AS agreement_title
            FROM billing_statements s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN standing_agreements a ON s.agreement_id = a.id
            WHERE s.id = ?
        ");
        $stmt->bind_param("i", $statementId);
        $stmt->execute();
        $res = $stmt->get_result();
        $statement = $res->fetch_assoc();
        if (!$statement) return null;

        // Fetch associated orders
        $orderStmt = $this->conn->prepare("
            SELECT o.*, b.batch_code, b.scheduled_date AS batch_delivery_date
            FROM orders o
            LEFT JOIN delivery_batches b ON o.batch_id = b.id
            WHERE o.statement_id = ?
            ORDER BY o.created_at ASC
        ");
        $orderStmt->bind_param("i", $statementId);
        $orderStmt->execute();
        $orderRes = $orderStmt->get_result();
        $orders = [];

        while ($o = $orderRes->fetch_assoc()) {
            // Fetch items
            $itemStmt = $this->conn->prepare("
                SELECT oi.*, p.name AS product_name, p.sku, p.unit_type, p.box_unit
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $itemStmt->bind_param("i", $o['id']);
            $itemStmt->execute();
            $itemRes = $itemStmt->get_result();
            $items = [];
            while ($item = $itemRes->fetch_assoc()) {
                $items[] = $item;
            }
            $o['items'] = $items;
            $orders[] = $o;
        }

        $statement['orders'] = $orders;
        return $statement;
    }

    /**
     * Generate a new cycle Statement of Account for unsettled orders
     */
    public function generateCycleStatement(int $userId, string $cycleStart, string $cycleEnd, ?int $agreementId = null): ?int {
        // Query unbilled deferred orders
        $stmt = $this->conn->prepare("
            SELECT id, total_amount, deposit_amount
            FROM orders
            WHERE user_id = ?
              AND payment_mode IN ('deferred_to_cycle', 'partial_deposit')
              AND statement_id IS NULL
              AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $userId, $cycleStart, $cycleEnd);
        $stmt->execute();
        $res = $stmt->get_result();
        $orders = [];
        $totalDeferred = 0.0;

        while ($row = $res->fetch_assoc()) {
            $orders[] = $row;
            $unsettled = (float)$row['total_amount'] - (float)$row['deposit_amount'];
            $totalDeferred += max(0.0, $unsettled);
        }

        if (empty($orders)) {
            return null; // No unbilled orders found for this cycle
        }

        $code = sprintf("SOA-%s-%s", date('Ym'), strtoupper(substr(uniqid(), -4)));
        $dueDate = date('Y-m-d', strtotime('+10 days'));

        $insertStmt = $this->conn->prepare("
            INSERT INTO billing_statements 
            (statement_code, user_id, agreement_id, cycle_start, cycle_end, total_amount, paid_amount, due_date, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, 0.00, ?, 'issued', 'Consolidated periodic statement of account.')
        ");
        $insertStmt->bind_param("siissds", $code, $userId, $agreementId, $cycleStart, $cycleEnd, $totalDeferred, $dueDate);
        if (!$insertStmt->execute()) {
            return null;
        }

        $statementId = $this->conn->insert_id;

        // Attach orders to statement
        $orderIds = array_column($orders, 'id');
        $inClause = implode(',', array_map('intval', $orderIds));
        $this->conn->query("UPDATE orders SET statement_id = $statementId WHERE id IN ($inClause)");

        return $statementId;
    }

    /**
     * Settle Statement of Account (e.g. Paid via MoMo or Bank Wire)
     */
    public function settleStatement(int $statementId, string $paymentMethod, float $amount): bool {
        $stmt = $this->conn->prepare("SELECT * FROM billing_statements WHERE id = ?");
        $stmt->bind_param("i", $statementId);
        $stmt->execute();
        $statement = $stmt->get_result()->fetch_assoc();
        if (!$statement) return false;

        $newPaid = (float)$statement['paid_amount'] + $amount;
        $total = (float)$statement['total_amount'];
        $status = ($newPaid >= $total) ? 'settled' : 'partially_paid';
        $now = date('Y-m-d H:i:s');

        $updateStmt = $this->conn->prepare("
            UPDATE billing_statements 
            SET paid_amount = ?, status = ?, payment_method = ?, settled_at = ? 
            WHERE id = ?
        ");
        $updateStmt->bind_param("dsssi", $newPaid, $status, $paymentMethod, $now, $statementId);
        if ($updateStmt->execute()) {
            // Release debt on the user's credit profile
            $this->creditService->releaseDebt((int)$statement['user_id'], $amount);

            // Mark attached orders as paid if fully settled
            if ($status === 'settled') {
                $this->conn->query("UPDATE orders SET payment_status = 'paid' WHERE statement_id = $statementId");
            }
            return true;
        }

        return false;
    }
}
