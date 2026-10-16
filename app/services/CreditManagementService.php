<?php
/**
 * CreditManagementService.php
 * Enterprise B2B Trade Credit & Risk Management Service
 * Implements APICS / SAP FSCM credit evaluation, exposure limits, and deferred settlement rules.
 */

require_once __DIR__ . '/../../bootstrap.php';

class CreditManagementService {
    private $conn;

    public function __construct($conn = null) {
        $this->conn = $conn ?: getDbConnection();
    }

    /**
     * Retrieve complete B2B credit profile for a user
     */
    public function getCreditProfile(int $userId): ?array {
        $stmt = $this->conn->prepare("SELECT id, username, email, institution_name, credit_limit, current_debt, security_deposit FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if (!$user) {
            return null;
        }

        $creditLimit = (float)($user['credit_limit'] ?? 0);
        $currentDebt = (float)($user['current_debt'] ?? 0);
        $deposit     = (float)($user['security_deposit'] ?? 0);
        $available   = max(0.0, $creditLimit - $currentDebt);
        $utilization = $creditLimit > 0 ? round(($currentDebt / $creditLimit) * 100, 1) : 0.0;

        return [
            'user_id'             => (int)$user['id'],
            'username'            => $user['username'],
            'institution_name'    => $user['institution_name'] ?: 'B2B Institution',
            'credit_limit'        => $creditLimit,
            'current_debt'        => $currentDebt,
            'security_deposit'    => $deposit,
            'available_credit'    => $available,
            'utilization_percent' => $utilization,
            'is_healthy'          => $utilization < 90.0
        ];
    }

    /**
     * Evaluate credit approval for an ad-hoc or scheduled wholesale order
     * 
     * Rules:
     * 1. If (current_debt + orderTotal) <= credit_limit:
     *    -> APPROVED_ON_CREDIT: 100% deferred to next billing cycle.
     * 2. If orderTotal > available_credit:
     *    -> DEPOSIT_REQUIRED: Minimum 30% deposit or excess coverage required upfront.
     */
    public function evaluateOrderCredit(int $userId, float $orderTotal): array {
        $profile = $this->getCreditProfile($userId);
        if (!$profile) {
            return [
                'status'           => 'REJECTED',
                'reason'           => 'User credit profile not found.',
                'approved'         => false,
                'required_deposit' => $orderTotal,
                'deferred_amount'  => 0.0
            ];
        }

        $available = $profile['available_credit'];

        if ($orderTotal <= $available) {
            // Full trade credit approval
            return [
                'status'           => 'APPROVED_ON_CREDIT',
                'approved'         => true,
                'required_deposit' => 0.0,
                'deferred_amount'  => $orderTotal,
                'available_credit' => $available,
                'remaining_credit' => $available - $orderTotal,
                'message'          => 'Order approved under institutional credit limit. Deferred to next billing statement.'
            ];
        } else {
            // Requires security deposit (30% standard deposit or excess over available limit)
            $excessAmount = $orderTotal - $available;
            $percentageDeposit = round($orderTotal * 0.30, 2);
            $requiredDeposit = max($excessAmount, $percentageDeposit);

            // Cap deposit at total order amount
            $requiredDeposit = min($orderTotal, $requiredDeposit);
            $deferredAmount = $orderTotal - $requiredDeposit;

            return [
                'status'           => 'DEPOSIT_REQUIRED',
                'approved'         => false,
                'required_deposit' => $requiredDeposit,
                'deferred_amount'  => $deferredAmount,
                'available_credit' => $available,
                'remaining_credit' => 0.0,
                'message'          => sprintf(
                    'Order exceeds available credit limit ($%s). A security down-payment of $%s is required. Remaining $%s will be deferred to your next billing cycle.',
                    number_format($available, 2),
                    number_format($requiredDeposit, 2),
                    number_format($deferredAmount, 2)
                )
            ];
        }
    }

    /**
     * Record unsettled trade debt upon order confirmation/dispatch
     */
    public function recordDebt(int $userId, float $amount): bool {
        if ($amount <= 0) return true;
        $stmt = $this->conn->prepare("UPDATE users SET current_debt = current_debt + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $userId);
        return $stmt->execute();
    }

    /**
     * Release unsettled debt upon statement reconciliation/payment
     */
    public function releaseDebt(int $userId, float $amount): bool {
        if ($amount <= 0) return true;
        $stmt = $this->conn->prepare("UPDATE users SET current_debt = GREATEST(0.00, current_debt - ?) WHERE id = ?");
        $stmt->bind_param("di", $amount, $userId);
        return $stmt->execute();
    }

    /**
     * Update institutional credit limit (Admin action)
     */
    public function updateCreditLimit(int $userId, float $newLimit): bool {
        $stmt = $this->conn->prepare("UPDATE users SET credit_limit = ? WHERE id = ?");
        $stmt->bind_param("di", $newLimit, $userId);
        return $stmt->execute();
    }

    /**
     * Adjust escrow security deposit
     */
    public function updateSecurityDeposit(int $userId, float $amount): bool {
        $stmt = $this->conn->prepare("UPDATE users SET security_deposit = ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $userId);
        return $stmt->execute();
    }
}
