<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

echo "Seeding B2B demo accounts and agreements..." . PHP_EOL;

// 1. Update B2B users
$conn->query("UPDATE users SET 
    institution_name = 'Greenwich Vietnam Canteen - District 7 Campus',
    credit_limit = 35000000.00,
    current_debt = 8500000.00,
    security_deposit = 10000000.00,
    shipping_address = '20 Cong Hoa Street, Ward 12, Tan Binh District, HCMC'
    WHERE id = 5");

$conn->query("UPDATE users SET 
    institution_name = 'FPT University Central Dining Hall',
    credit_limit = 25000000.00,
    current_debt = 2500000.00,
    security_deposit = 5000000.00,
    shipping_address = 'High-Tech Park, Thu Duc City, HCMC'
    WHERE id = 6");

// 2. Clear old demo agreements if any
$conn->query("DELETE FROM standing_agreements WHERE agreement_code LIKE 'AGR-2026-%'");

// 3. Create Standing Agreement for User 5
$stmt = $conn->prepare("INSERT INTO standing_agreements 
    (agreement_code, user_id, title, frequency, delivery_days, billing_cycle, start_date, end_date, recurring_budget, notes, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$code = 'AGR-2026-GW01';
$userId = 5;
$title = 'Greenwich Academic Term 1 Bulk Canteen Provisioning Agreement';
$freq = 'weekly';
$days = 'mon,thu';
$cycle = 'net_15';
$start = '2026-10-01';
$end = '2026-12-31';
$budget = 15000000.00;
$notes = 'Deliver early morning before 07:30 AM to Central Kitchen loading bay.';
$status = 'active';

$stmt->bind_param("sissssssdss", $code, $userId, $title, $freq, $days, $cycle, $start, $end, $budget, $notes, $status);
$stmt->execute();
$agrId = $conn->insert_id;
echo "Created Standing Agreement ID: $agrId" . PHP_EOL;

// 4. Create Delivery Batches for this agreement
// Batch 1: Delivered
$conn->query("INSERT INTO delivery_batches 
    (batch_code, agreement_id, scheduled_date, cut_off_time, status, tracking_ref, notes, dispatched_at, delivered_at)
    VALUES ('REL-2026-W41-M', $agrId, '2026-10-12', '2026-10-11 18:00:00', 'delivered', 'GHN-REL-882101', 'Regular term release', '2026-10-12 06:00:00', '2026-10-12 07:15:00')");

// Batch 2: Next scheduled release (Monday)
$conn->query("INSERT INTO delivery_batches 
    (batch_code, agreement_id, scheduled_date, cut_off_time, status, tracking_ref, notes)
    VALUES ('REL-2026-W42-M', $agrId, '2026-10-19', '2026-10-18 18:00:00', 'scheduled', 'PENDING-DISPATCH', 'Scheduled term batch #42')");

// Batch 3: Later release (Thursday)
$conn->query("INSERT INTO delivery_batches 
    (batch_code, agreement_id, scheduled_date, cut_off_time, status, tracking_ref, notes)
    VALUES ('REL-2026-W42-T', $agrId, '2026-10-22', '2026-10-21 18:00:00', 'scheduled', 'PENDING-DISPATCH', 'Scheduled term batch #43')");

echo "Seeded 3 delivery batches." . PHP_EOL;

// 5. Create Billing Statement (Statement of Account) for Cycle 1
$conn->query("DELETE FROM billing_statements WHERE statement_code LIKE 'SOA-2026-%'");
$stmt2 = $conn->prepare("INSERT INTO billing_statements 
    (statement_code, user_id, agreement_id, cycle_start, cycle_end, total_amount, paid_amount, due_date, status, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$soaCode = 'SOA-2026-10-H1';
$cStart = '2026-10-01';
$cEnd = '2026-10-15';
$totalAmt = 8500000.00;
$paidAmt = 0.00;
$dueDate = '2026-10-25';
$soaStatus = 'issued';
$soaNotes = 'B2B Consolidated Statement of Account for Oct Cycle 1 (Deliveries Oct 01 - Oct 15).';

$stmt2->bind_param("siissddsss", $soaCode, $userId, $agrId, $cStart, $cEnd, $totalAmt, $paidAmt, $dueDate, $soaStatus, $soaNotes);
$stmt2->execute();
$soaId = $conn->insert_id;
echo "Created Billing Statement ID: $soaId ($soaCode)" . PHP_EOL;

echo "Seeding completed successfully!" . PHP_EOL;
