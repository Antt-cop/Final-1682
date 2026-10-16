<?php
/**
 * StandingAgreementService.php
 * SCM Standing Supply Agreements & Periodic Delivery Release Management
 * Implements SAP Sales Scheduling Agreements (EDI 830/862) and cut-off consolidation rules.
 */

require_once __DIR__ . '/../../bootstrap.php';

class StandingAgreementService {
    private $conn;

    public function __construct($conn = null) {
        $this->conn = $conn ?: getDbConnection();
    }

    /**
     * Get the active standing agreement for a given user
     */
    public function getActiveAgreement(int $userId): ?array {
        $stmt = $this->conn->prepare("
            SELECT * FROM standing_agreements 
            WHERE user_id = ? AND status = 'active' 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }

    /**
     * Get upcoming scheduled batches for an agreement
     */
    public function getUpcomingBatches(int $agreementId, int $limit = 6): array {
        $stmt = $this->conn->prepare("
            SELECT b.*, 
                   COUNT(o.id) AS consolidated_order_count,
                   COALESCE(SUM(o.total_amount), 0.00) AS total_batch_value
            FROM delivery_batches b
            LEFT JOIN orders o ON o.batch_id = b.id
            WHERE b.agreement_id = ? AND b.status IN ('scheduled', 'packing')
            GROUP BY b.id
            ORDER BY b.scheduled_date ASC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $agreementId, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $batches = [];
        while ($row = $res->fetch_assoc()) {
            $batches[] = $row;
        }
        return $batches;
    }

    /**
     * Find next candidate batch for ad-hoc order consolidation
     * Must be in status 'scheduled' and before cut_off_time
     */
    public function getNextConsolidationBatch(int $userId): ?array {
        $agreement = $this->getActiveAgreement($userId);
        if (!$agreement) {
            return null;
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("
            SELECT b.*,
                   TIMESTAMPDIFF(MINUTE, ?, b.cut_off_time) AS minutes_to_cutoff,
                   TIMESTAMPDIFF(HOUR, ?, b.cut_off_time) AS hours_to_cutoff
            FROM delivery_batches b
            WHERE b.agreement_id = ? 
              AND b.status = 'scheduled'
              AND b.cut_off_time > ?
            ORDER BY b.scheduled_date ASC
            LIMIT 1
        ");
        $stmt->bind_param("ssis", $now, $now, $agreement['id'], $now);
        $stmt->execute();
        $res = $stmt->get_result();
        $batch = $res->fetch_assoc();

        if ($batch) {
            $batch['agreement_title'] = $agreement['title'];
            $batch['agreement_code']  = $agreement['agreement_code'];
        }

        return $batch ?: null;
    }

    /**
     * Get detailed batch info including consolidated orders and items
     */
    public function getBatchDetails(int $batchId): ?array {
        $stmt = $this->conn->prepare("
            SELECT b.*, a.title AS agreement_title, a.agreement_code, a.user_id,
                   u.username, u.institution_name, u.contact_phone, u.shipping_address
            FROM delivery_batches b
            JOIN standing_agreements a ON b.agreement_id = a.id
            JOIN users u ON a.user_id = u.id
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $batchId);
        $stmt->execute();
        $res = $stmt->get_result();
        $batch = $res->fetch_assoc();
        if (!$batch) return null;

        // Fetch consolidated orders
        $orderStmt = $this->conn->prepare("
            SELECT id, total_amount, status, order_type, payment_mode, deposit_amount, created_at, is_urgent
            FROM orders
            WHERE batch_id = ?
            ORDER BY id ASC
        ");
        $orderStmt->bind_param("i", $batchId);
        $orderStmt->execute();
        $orderRes = $orderStmt->get_result();
        $orders = [];
        $totalItems = 0;
        $totalValue = 0.0;

        while ($o = $orderRes->fetch_assoc()) {
            // Fetch items
            $itemStmt = $this->conn->prepare("
                SELECT oi.*, p.name AS product_name, p.sku, p.unit_type, p.box_unit, p.conversion_rate, p.storage_zone
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
                $totalItems += (int)$item['quantity'];
            }
            $o['items'] = $items;
            $orders[] = $o;
            $totalValue += (float)$o['total_amount'];
        }

        $batch['orders']      = $orders;
        $batch['total_items'] = $totalItems;
        $batch['total_value'] = $totalValue;

        return $batch;
    }

    /**
     * Generate future delivery releases (batches) automatically
     */
    public function generateScheduleBatches(int $agreementId, int $weeksAhead = 4): int {
        $stmt = $this->conn->prepare("SELECT * FROM standing_agreements WHERE id = ?");
        $stmt->bind_param("i", $agreementId);
        $stmt->execute();
        $agr = $stmt->get_result()->fetch_assoc();
        if (!$agr || $agr['status'] !== 'active') return 0;

        $deliveryDays = array_map('strtolower', array_map('trim', explode(',', $agr['delivery_days'])));
        $dayMap = [
            'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7
        ];

        $targetDayNums = [];
        foreach ($deliveryDays as $d) {
            if (isset($dayMap[$d])) {
                $targetDayNums[] = $dayMap[$d];
            }
        }
        if (empty($targetDayNums)) {
            $targetDayNums = [1, 4]; // Default Mon, Thu
        }

        $createdCount = 0;
        $startDate = new DateTime();
        $endDate = (clone $startDate)->modify("+$weeksAhead weeks");
        $contractEnd = new DateTime($agr['end_date']);
        if ($endDate > $contractEnd) {
            $endDate = $contractEnd;
        }

        $current = clone $startDate;
        while ($current <= $endDate) {
            $dayOfWeek = (int)$current->format('N'); // 1 (Mon) to 7 (Sun)
            if (in_array($dayOfWeek, $targetDayNums)) {
                $dateStr = $current->format('Y-m-d');

                // Check if batch already exists for this agreement & date
                $checkStmt = $this->conn->prepare("SELECT id FROM delivery_batches WHERE agreement_id = ? AND scheduled_date = ?");
                $checkStmt->bind_param("is", $agreementId, $dateStr);
                $checkStmt->execute();
                if ($checkStmt->get_result()->num_rows === 0) {
                    $weekNum = $current->format('W');
                    $dayChar = strtoupper(substr($current->format('D'), 0, 1));
                    $batchCode = sprintf("REL-%s-W%s-%s", $current->format('Y'), $weekNum, $dayChar);

                    // Cut-off time: 18:00 of the preceding calendar day
                    $cutOff = (clone $current)->modify("-1 day")->setTime(18, 0, 0);
                    $cutOffStr = $cutOff->format('Y-m-d H:i:s');

                    $insertStmt = $this->conn->prepare("
                        INSERT INTO delivery_batches 
                        (batch_code, agreement_id, scheduled_date, cut_off_time, status, tracking_ref)
                        VALUES (?, ?, ?, ?, 'scheduled', 'PENDING-DISPATCH')
                    ");
                    $insertStmt->bind_param("siss", $batchCode, $agreementId, $dateStr, $cutOffStr);
                    if ($insertStmt->execute()) {
                        $createdCount++;
                    }
                }
            }
            $current->modify('+1 day');
        }

        return $createdCount;
    }

    /**
     * Update delivery batch status
     */
    public function updateBatchStatus(int $batchId, string $status, ?string $trackingRef = null): bool {
        $now = date('Y-m-d H:i:s');
        if ($status === 'dispatched') {
            $stmt = $this->conn->prepare("UPDATE delivery_batches SET status = ?, tracking_ref = COALESCE(?, tracking_ref), dispatched_at = ? WHERE id = ?");
            $stmt->bind_param("sssi", $status, $trackingRef, $now, $batchId);
        } elseif ($status === 'delivered') {
            $stmt = $this->conn->prepare("UPDATE delivery_batches SET status = ?, delivered_at = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $now, $batchId);
        } else {
            $stmt = $this->conn->prepare("UPDATE delivery_batches SET status = ?, tracking_ref = COALESCE(?, tracking_ref) WHERE id = ?");
            $stmt->bind_param("ssi", $status, $trackingRef, $batchId);
        }
        return $stmt->execute();
    }
}
