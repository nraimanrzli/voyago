<?php
require_once 'toyyibpay_config.php';

// Only admins
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: index.php'); exit;
}

// Create host_settlements table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS host_settlements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    host_id INT NOT NULL,
    host_name VARCHAR(255) NOT NULL,
    booking_ids TEXT NOT NULL COMMENT 'Comma-separated booking IDs included',
    total_gross DECIMAL(10,2) NOT NULL,
    platform_cut DECIMAL(10,2) NOT NULL,
    host_payout DECIMAL(10,2) NOT NULL,
    installment_plan TINYINT(1) DEFAULT 0 COMMENT '0=full, 1=installment',
    installments_total INT DEFAULT 1,
    installments_paid INT DEFAULT 0,
    status ENUM('Pending','Partial','Settled') DEFAULT 'Pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$pdo->exec("CREATE TABLE IF NOT EXISTS host_settlement_installments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    settlement_id INT NOT NULL,
    installment_no INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATETIME NULL,
    status ENUM('Pending','Paid') DEFAULT 'Pending',
    UNIQUE KEY uq_settlement_inst (settlement_id, installment_no)
) ENGINE=InnoDB");

// Mark booking IDs as settled when a settlement is settled
$b_cols = $pdo->query("DESCRIBE bookings")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('settlement_id', $b_cols)) {
    try { $pdo->exec("ALTER TABLE bookings ADD COLUMN `settlement_id` INT NULL"); } catch (PDOException $e) {}
}

// --- HANDLE: Transfer All Pending Transactions for an Owner ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_transfer_all_owner'])) {
    $owner_id = intval($_POST['owner_id']);
    if ($owner_id > 0) {
        $owner_stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
        $owner_stmt->execute([$owner_id]);
        $owner_data = $owner_stmt->fetch(PDO::FETCH_ASSOC);
        $owner_name = $owner_data ? $owner_data['fullname'] : 'Owner';

        $pending_stmt = $pdo->prepare("SELECT b.id, b.total_price
            FROM bookings b
            JOIN homestays h ON b.homestay_id = h.id
            WHERE h.user_id = ?
              AND b.payment_status = 'Paid'
              AND b.booking_status != 'Cancelled'
              AND b.settlement_id IS NULL");
        $pending_stmt->execute([$owner_id]);
        $pending_bookings = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($pending_bookings)) {
            $booking_ids = array_column($pending_bookings, 'id');
            $gross = array_sum(array_map('floatval', array_column($pending_bookings, 'total_price')));
            $commission = round($gross * 0.10, 2);
            $payout = round($gross * 0.90, 2);
            $booking_ids_csv = implode(',', $booking_ids);

            $ins_s = $pdo->prepare("INSERT INTO host_settlements
                (host_id, host_name, booking_ids, total_gross, platform_cut, host_payout, installment_plan, installments_total, installments_paid, status, notes)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $ins_s->execute([
                $owner_id, $owner_name, $booking_ids_csv,
                $gross, $commission, $payout, 0, 1, 1, 'Settled', 'Transferred by admin'
            ]);
            $settlement_id = $pdo->lastInsertId();
            $pdo->prepare("UPDATE bookings SET settlement_id = ? WHERE id IN ($booking_ids_csv)")->execute([$settlement_id]);
        }
    }
    header("Location: admin_commission.php?transferred=1");
    exit;
}


// --- Fetch Data ---
$statement_stmt = $pdo->query("
    SELECT b.id, b.booking_no, b.created_at, b.total_price, b.settlement_id,
           u.fullname as guest_name, h.name as stay_name,
           ho.id as host_id, ho.fullname as host_name,
           IFNULL(s.created_at, '') as transferred_at
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN homestays h ON b.homestay_id = h.id
    JOIN users ho ON h.user_id = ho.id
    LEFT JOIN host_settlements s ON b.settlement_id = s.id
    WHERE b.payment_status = 'Paid' AND b.booking_status != 'Cancelled'
    ORDER BY ho.fullname, b.created_at ASC
");
$statement_transactions = $statement_stmt->fetchAll(PDO::FETCH_ASSOC);

$owner_groups = [];
foreach ($statement_transactions as $tx) {
    $host_id = intval($tx['host_id']);
    $gross = floatval($tx['total_price']);
    $commission = round($gross * 0.10, 2);
    $net = round($gross - $commission, 2);
    $status = $tx['settlement_id'] ? 'Transferred' : 'Pending Transfer';

    if (!isset($owner_groups[$host_id])) {
        $owner_groups[$host_id] = [
            'host_id' => $host_id,
            'host_name' => $tx['host_name'],
            'transactions' => [],
            'pending_net' => 0.0,
            'transferred_net' => 0.0,
            'total_net' => 0.0
        ];
    }

    if ($status === 'Pending Transfer') {
        $owner_groups[$host_id]['pending_net'] += $net;
    } else {
        $owner_groups[$host_id]['transferred_net'] += $net;
    }
    $owner_groups[$host_id]['total_net'] += $net;

    $owner_groups[$host_id]['transactions'][] = array_merge($tx, [
        'commission' => $commission,
        'net_amount' => $net,
        'status_label' => $status
    ]);
}

$pending_bookings_count = count(array_filter($statement_transactions, fn($tx) => empty($tx['settlement_id'])));

// Metrics
$all_gross = $pdo->query("SELECT SUM(total_price) FROM bookings")->fetchColumn() ?: 0;
$total_commission = round($all_gross * 0.10, 2);
$settled_count = 0;
foreach ($owner_groups as $og) {
    $settled_count += count(array_filter($og['transactions'], fn($t) => $t['status_label'] === 'Transferred'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> Admin - Owner Bank Statements</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/admin_commission.css?v=<?= time() ?>">
    <style>
        .host-group-header { background: rgba(244,203,102,0.08); border-left: 3px solid #f4cb66; padding: 8px 14px; font-size:0.8rem; font-weight:700; color:#f4cb66; letter-spacing:0.04em; text-transform:uppercase; }
        .settlement-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius:12px; padding:18px; margin-bottom:16px; }
        .settlement-card.settled { border-color: rgba(16,185,129,0.4); }
        .settlement-card.partial { border-color: rgba(244,203,102,0.4); }
        .inst-row { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.06); font-size:0.82rem; }
        .inst-row:last-child { border-bottom:none; }
        .badge-paid { background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; }
        .badge-pending { background:rgba(249,115,22,0.15); color:#f97316; border:1px solid rgba(249,115,22,0.3); padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; }
        .badge-settled { background:rgba(16,185,129,0.2); color:#10b981; padding:3px 8px; border-radius:6px; font-size:0.72rem; }
        .badge-partial { background:rgba(244,203,102,0.2); color:#f4cb66; padding:3px 8px; border-radius:6px; font-size:0.72rem; }
        .select-bar { position:sticky; bottom:0; background:rgba(10,20,15,0.97); border-top:1px solid rgba(244,203,102,0.3); padding:14px 20px; display:flex; align-items:center; gap:14px; flex-wrap:wrap; z-index:50; display:none; }
        .btn-create-settlement { background: linear-gradient(135deg,#f4cb66,#e8a020); color:#0a2010; font-weight:700; border:none; padding:10px 22px; border-radius:8px; cursor:pointer; font-size:0.9rem; display:flex; align-items:center; gap:6px; }
        .chk-booking { width:16px; height:16px; accent-color:#f4cb66; cursor:pointer; }
    </style>
</head>
<body>
<nav>
    <div class="nav__header">
        <div class="nav__logo"><a href="admin.php" class="logo"><?= htmlspecialchars($system_name) ?><span>.</span></a></div>
        <div class="nav__menu__btn" id="menu-btn"><i class="ri-menu-line"></i></div>
    </div>
    <ul class="nav__links" id="nav-links">
        <li><a href="booking_history.php">Booking History</a></li>
        <li><a href="add_attractions.php">Add Attractions</a></li>
        <li><a href="admin_commission.php" style="color:#d4af37;font-weight:600;">Admin Commission</a></li>
        <li><a href="admin.php">Dashboard</a></li>
        <li><a href="admin_homestay.php">Homestay Approvals</a></li>
        <li><a href="index.php?action=logout" style="background:#e74c3c;color:white;padding:6px 12px;border-radius:4px;font-size:0.85rem;font-weight:bold;text-transform:uppercase;">LOGOUT</a></li>
    </ul>
</nav>

<div class="admin-workspace-container">
    <header class="admin-workspace-header">
        <div class="header-titles">
            <h1>Owner Bank Statements & Commission</h1>
            <p>View completed booking transactions grouped by owner, with net payouts and transfer status.</p>
        </div>
    </header>

    <?php if (isset($_GET['transferred'])): ?>
    <div style="background:rgba(16,185,129,0.15);border:1px solid #10b981;color:#10b981;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:600;">
        <i class="ri-checkbox-circle-line"></i> Pending owner transfers marked as completed.
    </div>
    <?php endif; ?>

    <!-- Metrics -->
    <section class="metrics-grid">
        <div class="metric-card">
            <div class="metric-info">
                <span class="metric-label">Commission Rate</span>
                <h3 class="metric-val">10%</h3>
            </div>
            <div class="metric-icon"><i class="ri-percent-line"></i></div>
        </div>
        <div class="metric-card">
            <div class="metric-info">
                <span class="metric-label">Pending Payout Bookings</span>
                <h3 class="metric-val"><?= $pending_bookings_count ?></h3>
            </div>
            <div class="metric-icon"><i class="ri-time-line"></i></div>
        </div>
        <div class="metric-card">
            <div class="metric-info">
                <span class="metric-label">Settlements Completed</span>
                <h3 class="metric-val"><?= $settled_count ?></h3>
            </div>
            <div class="metric-icon"><i class="ri-check-double-line"></i></div>
        </div>
        <div class="metric-card highlight-card">
            <div class="metric-info">
                <span class="metric-label">Total Platform Net Cut</span>
                <h3 class="metric-val">RM <?= number_format($total_commission, 2) ?></h3>
            </div>
            <div class="metric-icon"><i class="ri-hand-coin-line"></i></div>
        </div>
    </section>

    <!-- OWNER BANK STATEMENTS -->
    <section class="admin-section-card">
        <div class="section-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <h3><i class="ri-bank-card-line"></i> Owner Bank Statements</h3>
                <p style="color:rgba(255,255,255,0.55);margin:0;">Every completed booking is listed as a statement transaction, grouped by owner.</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="text" id="searchUser" placeholder="Search owner, booking, guest..." onkeyup="filterStatementTable()" style="min-width:220px;padding:8px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.06);color:white;">
                <button onclick="exportStatementCSV()" style="background:#27ae60;color:white;border:none;padding:8px 14px;border-radius:6px;font-weight:500;cursor:pointer;font-size:0.85rem;display:flex;align-items:center;gap:6px;">
                    <i class="ri-file-excel-line"></i> Export CSV
                </button>
            </div>
        </div>
        <?php if (empty($owner_groups)): ?>
            <p style="color:rgba(255,255,255,0.5);padding:20px;text-align:center;font-style:italic;">No completed owner transactions found.</p>
        <?php else: ?>
            <?php foreach ($owner_groups as $owner): ?>
                <div style="margin-bottom: 24px; padding: 18px; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; background: rgba(255,255,255,0.04);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                        <div>
                            <div class="host-group-header" style="background:rgba(244,203,102,0.08);border-left:3px solid #f4cb66;padding:10px 14px;border-radius:10px;font-weight:700;color:#f4cb66;">Owner: <?= htmlspecialchars($owner['host_name']) ?></div>
                            <div style="margin-top:12px; color:rgba(255,255,255,0.75); font-size:0.92rem; line-height:1.6;">
                                <strong>Pending:</strong> RM <?= number_format($owner['pending_net'], 2) ?> &nbsp;&bull;&nbsp;
                                <strong>Transferred:</strong> RM <?= number_format($owner['transferred_net'], 2) ?> &nbsp;&bull;&nbsp;
                                <strong>Total:</strong> RM <?= number_format($owner['total_net'], 2) ?>
                            </div>
                        </div>
                        <?php if ($owner['pending_net'] > 0): ?>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="action_transfer_all_owner" value="1">
                            <input type="hidden" name="owner_id" value="<?= $owner['host_id'] ?>">
                            <button type="submit" style="background:#10b981;color:#0a2010;border:none;padding:10px 18px;border-radius:10px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:0.95rem;">
                                <i class="ri-bank-card-line"></i> Transfer All Pending
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive" style="margin-top:18px;">
                        <table class="data-table owner-statement-table" style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Booking ID</th>
                                    <th>Guest Name</th>
                                    <th>Homestay</th>
                                    <th>Gross Amount</th>
                                    <th>Voyago Commission</th>
                                    <th>Net Amount</th>
                                    <th>Status</th>
                                    <th>Running Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $running = 0.0; foreach ($owner['transactions'] as $tx):
                                    $running += $tx['net_amount'];
                                ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($tx['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($tx['booking_no'] ?: 'BKG-' . str_pad($tx['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                                    <td><?= htmlspecialchars($tx['guest_name']) ?></td>
                                    <td><?= htmlspecialchars($tx['stay_name']) ?></td>
                                    <td>RM <?= number_format(floatval($tx['total_price']), 2) ?></td>
                                    <td>RM <?= number_format($tx['commission'], 2) ?></td>
                                    <td>RM <?= number_format($tx['net_amount'], 2) ?></td>
                                    <td><span style="color:<?= $tx['status_label'] === 'Transferred' ? '#10b981' : '#f97316' ?>; font-weight:700;"><?= $tx['status_label'] ?></span></td>
                                    <td>RM <?= number_format($running, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<script src="js/admin.js"></script>
<script>
function filterStatementTable() {
    const q = document.getElementById('searchUser').value.toLowerCase();
    document.querySelectorAll('.owner-statement-table tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function exportStatementCSV() {
    let csv = "data:text/csv;charset=utf-8,Owner,Date,Booking ID,Guest,Homestay,Gross,Commission,Net,Status,Running Balance\n";
    document.querySelectorAll('.owner-statement-table').forEach(table => {
        const ownerLabel = table.closest('div').querySelector('.host-group-header').innerText.replace('Owner: ', '');
        table.querySelectorAll('tbody tr').forEach(row => {
            const cols = row.querySelectorAll('td');
            if (!cols.length) return;
            const values = [ownerLabel];
            cols.forEach(col => values.push('"' + col.innerText.trim().replace(/"/g, '""') + '"'));
            csv += values.join(',') + '\n';
        });
    });
    const link = document.createElement('a');
    link.href = encodeURI(csv);
    link.download = '<?= htmlspecialchars($system_name) ?>_Owner_Statements_' + new Date().toISOString().slice(0,10) + '.csv';
    link.click();
}
</script>
</body>
</html>