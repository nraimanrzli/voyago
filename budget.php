<?php
// budget.php
require_once('toyyibpay_config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Switch active booking via GET param
if (isset($_GET['switch_booking_id'])) {
    $_SESSION['active_booking_id'] = intval($_GET['switch_booking_id']);
    header("Location: budget.php");
    exit();
}

// Fetch all paid bookings for this traveller for the dropdown
$stmt_all_bookings = $pdo->prepare(
    "SELECT b.id, b.booking_no, h.name as stay_name, h.state, h.district, h.category, b.check_in, b.check_out, b.total_price
     FROM bookings b
     JOIN homestays h ON b.homestay_id = h.id
     WHERE b.user_id = ? AND b.payment_status = 'Paid' AND b.booking_status != 'Cancelled'
     ORDER BY b.id DESC"
);
$stmt_all_bookings->execute([$user_id]);
$all_paid_bookings = $stmt_all_bookings->fetchAll(PDO::FETCH_ASSOC);

// Determine active booking
$active_booking = null;
$active_booking_id = null;

if (isset($_SESSION['active_booking_id'])) {
    foreach ($all_paid_bookings as $b) {
        if ($b['id'] == $_SESSION['active_booking_id']) {
            $active_booking = $b;
            $active_booking_id = $b['id'];
            break;
        }
    }
}

// Default to most recent paid booking if none in session
if (!$active_booking && !empty($all_paid_bookings)) {
    $active_booking = $all_paid_bookings[0];
    $active_booking_id = $active_booking['id'];
    $_SESSION['active_booking_id'] = $active_booking_id;
}

// Build active_trip shape for UI compatibility (using booking data)
if ($active_booking) {
    // Fetch total_budget from bookings table
    $bgt_stmt = $pdo->prepare("SELECT total_budget FROM bookings WHERE id = ?");
    $bgt_stmt->execute([$active_booking_id]);
    $bgt_row = $bgt_stmt->fetch(PDO::FETCH_ASSOC);
    $saved_budget = ($bgt_row && $bgt_row['total_budget'] > 0) ? floatval($bgt_row['total_budget']) : $active_booking['total_price'] * 1.5;

    $active_trip = [
        'id'           => $active_booking_id,
        'name'         => $active_booking['stay_name'] . ' (Booking #' . ($active_booking['booking_no'] ?: $active_booking_id) . ')',
        'state'        => $active_booking['state'],
        'district'     => $active_booking['district'],
        'category'     => $active_booking['category'],
        'total_budget' => $saved_budget,
        'check_in'     => $active_booking['check_in'],
        'check_out'    => $active_booking['check_out'],
        'total_price'  => $active_booking['total_price'],
    ];
    // Use booking ID as the key for all trip tables
    $homestay_id = $active_booking_id;
} else {
    // Fallback demo when no paid bookings exist
    $active_trip = [
        'id'           => 0,
        'name'         => 'No Active Booking Found',
        'state'        => '',
        'district'     => 'Please complete a booking first',
        'category'     => '',
        'total_budget' => 2000.00,
        'check_in'     => '',
        'check_out'    => '',
        'total_price'  => 0,
    ];
    $homestay_id = 0;
}

// --- KEMASKINI BAJET (STEP 1) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_set_budget']) && $homestay_id > 0) {
    $total_budget = floatval($_POST['total_budget']);
    $stmt = $pdo->prepare("UPDATE bookings SET total_budget = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$total_budget, $homestay_id, $user_id]);
    header("Location: budget.php?success=budget");
    exit();
}

// --- 1. TAMBAH PERBELANJAAN (STEP 3-B) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_expense']) && $homestay_id > 0) {
    $title    = trim($_POST['exp_name']);
    $amount   = floatval($_POST['exp_amount']);
    $category = $_POST['exp_category'];
    $payer    = $_POST['exp_payer'];

    $stmt = $pdo->prepare("INSERT INTO trip_expenses (homestay_id, title, amount, category, payer) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$homestay_id, $title, $amount, $category, $payer]);
    header("Location: budget.php?success=expense");
    exit();
}

// --- 2. MUAT NAIK RESIT DOKUMEN (STEP 2) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_doc']) && $homestay_id > 0) {
    $doc_type = $_POST['doc_type'];
    if (isset($_FILES['doc_file']) && $_FILES['doc_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/docs/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename    = time() . '_' . basename($_FILES['doc_file']['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $target_path)) {
            $stmt = $pdo->prepare("INSERT INTO trip_documents (homestay_id, doc_type, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$homestay_id, $doc_type, $target_path]);
            header("Location: budget.php?success=doc");
            exit();
        }
    }
}

// --- 3. TAMBAH MEMORI GAMBAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_memory']) && $homestay_id > 0) {
    $caption = trim($_POST['mem_caption']);
    if (isset($_FILES['mem_image']) && $_FILES['mem_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/memories/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename    = time() . '_' . basename($_FILES['mem_image']['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['mem_image']['tmp_name'], $target_path)) {
            $stmt = $pdo->prepare("INSERT INTO trip_memories (homestay_id, file_path, caption) VALUES (?, ?, ?)");
            $stmt->execute([$homestay_id, $target_path, $caption]);
            header("Location: budget.php?success=memory");
            exit();
        }
    }
}

// --- 4. TAMBAH KAWAN TRIPMATE (STEP 3-A) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_mate']) && $homestay_id > 0) {
    $mate_name = trim($_POST['mate_name']);
    $mate_debt = floatval($_POST['mate_debt']);
    $stmt = $pdo->prepare("INSERT INTO trip_mates (homestay_id, name, debt_amount) VALUES (?, ?, ?)");
    $stmt->execute([$homestay_id, $mate_name, $mate_debt]);
    header("Location: budget.php?success=mate");
    exit();
}

// --- 5. FINISH CURRENT TRIP ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_finish_trip']) && $homestay_id > 0) {
    $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Completed', finished_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$homestay_id, $user_id]);
    unset($_SESSION['active_booking_id']);
    header("Location: budget.php?success=finished");
    exit();
}

// --- AMBIL SEMUA DATA LIVE BERDASARKAN BOOKING AKTIF ---
$budget_limit = floatval($active_trip['total_budget']);

$stmt = $pdo->prepare("SELECT * FROM trip_expenses WHERE homestay_id = ? ORDER BY id DESC");
$stmt->execute([$homestay_id]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_spent = 0;
foreach ($expenses as $exp) { $total_spent += floatval($exp['amount']); }
$remaining_budget = $budget_limit - $total_spent;

$stmt = $pdo->prepare("SELECT * FROM trip_documents WHERE homestay_id = ? ORDER BY id DESC");
$stmt->execute([$homestay_id]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM trip_memories WHERE homestay_id = ? ORDER BY id DESC");
$stmt->execute([$homestay_id]);
$memories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM trip_mates WHERE homestay_id = ? ORDER BY id DESC");
$stmt->execute([$homestay_id]);
$tripmates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// History: bookings marked Completed for this user
$stmt_history = $pdo->prepare(
    "SELECT b.id, b.booking_no, b.finished_at, b.total_budget, b.total_price, h.name as stay_name, h.category
     FROM bookings b
     JOIN homestays h ON b.homestay_id = h.id
     WHERE b.user_id = ? AND b.booking_status = 'Completed'
     ORDER BY b.finished_at DESC"
);
$stmt_history->execute([$user_id]);
$trip_history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

// Fetch installments for active booking (if installment plan)
$installments = [];
if ($active_booking && $active_booking_id) {
    $inst_stmt = $pdo->prepare("SELECT * FROM booking_installments WHERE booking_id = ? ORDER BY installment_no");
    $inst_stmt->execute([$active_booking_id]);
    $installments = $inst_stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> Premium - Smart Budgeting Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/budget.css">
</head>
<body>

    <div class="budget-wrapper-page">
        <main class="main-budget-pane">
            
            <header class="budget-top-header">
                <div>
                    <h2><?= htmlspecialchars($system_name) ?> Smart-Budgeting Hub 🗺️</h2>
                    <p>Manage your trip budget instantly and in real time for your stay destination.</p>
                </div>
                <div class="header-action-buttons">
                    <?php if(!empty($all_paid_bookings)): ?>
                        <select onchange="location = this.value;" class="trip-switcher-dropdown">
                            <?php foreach($all_paid_bookings as $b): ?>
                                <option value="budget.php?switch_booking_id=<?php echo $b['id']; ?>" <?php echo ($b['id'] == $homestay_id) ? 'selected' : ''; ?>>
                                    🗺️ <?php echo htmlspecialchars($b['stay_name']); ?> (#<?php echo $b['booking_no'] ?: $b['id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <a href="dashboard.php" class="btn-back-dashboard">
                        <i class="ri-arrow-left-line"></i> Dashboard
                    </a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to finish this trip? It will be saved in the History section.');">
                        <input type="hidden" name="action_finish_trip" value="1">
                        <button type="submit" class="btn-finish-trip">
                            <i class="ri-flag-line"></i> Finish Trip
                        </button>
                    </form>
                </div>
            </header>

            <div class="ai-suggestion-box">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="suggestion-icon-badge">💡</div>
                    <div>
                        <h4><?= htmlspecialchars($system_name) ?> Live Track Active: <?php echo htmlspecialchars($active_trip['name']); ?></h4>
                        <p>The system is monitoring your current expenses in <?php echo htmlspecialchars($active_trip['district'] . ', ' . $active_trip['state']); ?>.</p>
                    </div>
                </div>
            </div>

            <div class="budget-grid-dashboard">
                
                <div class="dashboard-column-stack">
                    
                    <div class="glass-metric-card">
                        <span class="step-badge">STEP 1</span>
                        <h3><i class="ri-hand-coin-line"></i> Set Budget Limit</h3>
                        <p class="card-description-text">Set your trip budget. If expenses exceed the limit, the card warning color will change automatically.</p>
                        
                        <form method="POST" class="inline-budget-form">
                            <input type="hidden" name="action_set_budget" value="1">
                            <div class="input-currency-wrapper">
                                <span class="currency-label-prefix">RM</span>
                                <input type="number" name="total_budget" value="<?php echo $budget_limit; ?>" required class="dark-input-field">
                            </div>
                            <button type="submit" class="btn-save-budget">Save</button>
                        </form>
                    </div>

                    <div class="glass-metric-card">
                        <span class="step-badge">STEP 2</span>
                        <div class="card-header-flex">
                            <div>
                                <h3 style="margin: 0;"><i class="ri-folder-shield-2-line"></i> Booking Wallet</h3>
                                <p class="card-description-text" style="margin: 2px 0 0 0;">Store flight, hotel, and car rental receipts.</p>
                            </div>
                            <button onclick="openModal('docModal')" class="btn-card-action-add">
                                <i class="ri-add-line"></i> Add
                            </button>
                        </div>

                        <div class="scrollable-list-container">
                            <?php if (empty($documents)): ?>
                                <p class="empty-state-text">No receipts yet. Please upload a new document.</p>
                            <?php else: ?>
                                <?php foreach ($documents as $doc): ?>
                                    <div class="document-item-row">
                                        <div>
                                            <div class="item-title-text"><?php echo htmlspecialchars($doc['doc_type']); ?></div>
                                            <small class="item-subtitle-text">Saved Securely</small>
                                        </div>
                                        <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn-view-document">
                                            <i class="ri-eye-line"></i> View
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="glass-metric-card">
                        <span class="step-badge">STEP 3 (A)</span>
                        <div class="card-header-flex">
                            <div>
                                <h3 style="margin: 0;"><i class="ri-group-line"></i> Tripmates Hub</h3>
                                <p class="card-description-text" style="margin: 2px 0 0 0;">Manage travel companions and refund status records.</p>
                            </div>
                            <button onclick="openModal('mateModal')" class="btn-card-action-add">
                                <i class="ri-add-line"></i> Add Mate
                            </button>
                        </div>

                        <div class="scrollable-list-container">
                            <?php if (empty($tripmates)): ?>
                                <p class="empty-state-text">No travel companions added.</p>
                            <?php else: ?>
                                <?php foreach ($tripmates as $mate): ?>
                                    <div class="tripmate-item-row">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div class="avatar-fallback-circle">
                                                <?php echo strtoupper(substr($mate['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <span class="item-title-text"><?php echo htmlspecialchars($mate['name']); ?></span>
                                                <small class="debt-alert-text">Owes you: RM<?php echo number_format($mate['debt_amount'], 2); ?></small>
                                            </div>
                                        </div>
                                        <button onclick="triggerSettlement('<?php echo htmlspecialchars($mate['name']); ?>', <?php echo $mate['debt_amount']; ?>)" class="btn-settle-debt">
                                            Settle
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($installments)): ?>
                    <div class="glass-metric-card" style="margin-top:0;">
                        <h3 style="margin:0 0 12px 0;"><i class="ri-calendar-schedule-line"></i> Installment Plan</h3>
                        <p class="card-description-text" style="margin:0 0 14px 0;">Track your outstanding installment payments for this booking.</p>
                        <?php foreach ($installments as $inst): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid rgba(255,255,255,0.07); font-size:0.82rem; flex-wrap:wrap; gap:6px;">
                            <span style="color:rgba(255,255,255,0.7); min-width:90px;">Instalment <?php echo $inst['installment_no']; ?></span>
                            <span style="font-weight:600; color:white;">RM <?php echo number_format($inst['amount'],2); ?></span>
                            <span style="color:rgba(255,255,255,0.5); font-size:0.75rem;">Due: <?php echo $inst['due_date']; ?></span>
                            <?php if ($inst['status']==='Paid'): ?>
                                <span style="color:#10b981; font-weight:700; font-size:0.78rem;"><i class="ri-checkbox-circle-line"></i> Paid</span>
                            <?php elseif ($inst['status']==='Overdue'): ?>
                                <span style="color:#ef4444; font-weight:700; font-size:0.78rem;"><i class="ri-error-warning-line"></i> Overdue</span>
                            <?php else: ?>
                                <span style="color:#f97316; font-weight:600; font-size:0.78rem;">Pending</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>

                <div class="dashboard-column-stack">
                    
                    <?php 
                    $is_overlimit = ($total_spent > $budget_limit);
                    $monitor_class = $is_overlimit ? 'glass-metric-card monitor-card overlimit-danger' : 'glass-metric-card monitor-card-safe';
                    ?>
                    <div class="<?php echo $monitor_class; ?>">
                        <h3>Financial Status Monitor</h3>
                        <p class="card-description-text">The automatic calculator updates your remaining budget in real time.</p>
                        
                        <div class="financial-counters-grid">
                            <div class="counter-box">
                                <span class="counter-box-title">Total Expended</span>
                                <h2 style="margin: 5px 0 0 0; font-size: 24px; color: #ff4d4d;">RM <?php echo number_format($total_spent, 2); ?></h2>
                            </div>
                            <div class="counter-box">
                                <span class="counter-box-title">Remaining Buffer</span>
                                <h2 style="margin: 5px 0 0 0; font-size: 24px; color: <?php echo $is_overlimit ? '#ff4d4d' : '#2ecc71'; ?>;">RM <?php echo number_format($remaining_budget, 2); ?></h2>
                            </div>
                        </div>

                        <?php if ($is_overlimit): ?>
                            <div class="danger-warning-alert-bar">
                                ⚠️ WARNING: Expenses have exceeded the budget limit!
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="glass-metric-card">
                        <span class="step-badge">STEP 3 (B)</span>
                        <div class="card-header-flex">
                            <div>
                                <h3 style="margin: 0;"><i class="ri-bill-line"></i> Live Expenses Ledger</h3>
                                <p class="card-description-text" style="margin: 2px 0 0 0;">Add and track your daily expenses in real time.</p>
                            </div>
                            <button onclick="openModal('expenseModal')" class="btn-add-cost-orange">
                                <i class="ri-add-line"></i> Add Cost
                            </button>
                        </div>

                        <div class="scrollable-list-container" style="max-height: 240px;">
                            <?php if (empty($expenses)): ?>
                                <p class="empty-state-text" style="padding: 15px 0;">The ledger is empty. New expenses will appear here.</p>
                            <?php else: ?>
                                <?php foreach ($expenses as $exp): ?>
                                    <div class="expense-ledger-row">
                                        <div>
                                            <strong class="item-title-text"><?php echo htmlspecialchars($exp['title']); ?></strong>
                                            <span class="item-subtitle-text" style="display:block; margin-top:2px;">By: <?php echo htmlspecialchars($exp['payer']); ?> | Tag: <?php echo htmlspecialchars($exp['category']); ?></span>
                                        </div>
                                        <div>
                                            <span class="expense-amount-tag">- RM <?php echo number_format($exp['amount'], 2); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <section class="glass-metric-card" style="margin-top: 25px;">
                <div class="card-header-flex" style="border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 12px; margin-bottom: 18px;">
                    <div>
                        <h3 style="margin: 0;"><i class="ri-camera-lens-line"></i> Memory Hall</h3>
                        <p class="card-description-text" style="margin: 2px 0 0 0;">Capture travel memories with your own photo journal captions.</p>
                    </div>
                    <button onclick="openModal('memoryModal')" class="btn-log-photo-teal">
                        <i class="ri-image-add-line"></i> Log Photo
                    </button>
                </div>

                <div class="memory-grid-layout">
                    <?php if (empty($memories)): ?>
                        <div class="empty-state-text" style="grid-column: 1/-1; padding: 30px 0;">
                            The Memory Hall is empty. Please share your beautiful trip moments here!
                        </div>
                    <?php else: ?>
                        <?php foreach ($memories as $mem): ?>
                            <div class="memory-photo-card">
                                <img src="<?php echo htmlspecialchars($mem['file_path']); ?>" alt="Memory">
                                <p>"<?php echo htmlspecialchars($mem['caption']); ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="glass-metric-card" style="margin-top: 25px;">
                <h3><i class="ri-history-line"></i> Finished Trip History</h3>
                <p class="card-description-text" style="margin-bottom:15px;">All past planning data that was completed through the 'Finish Trip' button.</p>
                
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <?php if (empty($trip_history)): ?>
                        <p class="empty-state-text" style="padding:15px 0;">No past trip records found.</p>
                    <?php else: ?>
                        <?php foreach ($trip_history as $hist): ?>
                            <div class="history-item-row">
                                <div>
                                    <strong style="color:#f4cb66; font-size:14px;"><?php echo htmlspecialchars($hist['stay_name']); ?> <?php echo $hist['booking_no'] ? '(#'.$hist['booking_no'].')' : ''; ?></strong>
                                    <small style="display:block; color:rgba(255,255,255,0.6); font-size:11px; margin-top:2px;">Category: <?php echo htmlspecialchars($hist['category']); ?> | Finished On: <?php echo $hist['finished_at']; ?></small>
                                </div>
                                <div style="text-align:right;">
                                    <strong style="color:white; font-size:14px;">Budget Limit: RM <?php echo number_format($hist['total_budget'], 2); ?></strong>
                                    <span style="display:block; font-size:11px; color:#2ecc71; font-weight:bold; margin-top:2px;"><i class="ri-checkbox-circle-line"></i> Archived</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <div id="expenseModal" class="modal-overlay">
        <div class="modal-glass-content">
            <span class="close-btn" onclick="closeModal('expenseModal')">&times;</span>
            <h3>Add New Live Expense</h3>
            <form method="POST" action="budget.php" style="margin-top: 15px;">
                <input type="hidden" name="action_add_expense" value="1">
                <div class="form-group-field">
                    <label>Expense Name / Item</label>
                    <input type="text" name="exp_name" placeholder="e.g., Dinner at Ikan Bakar" required class="modal-dark-input">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group-field">
                        <label>Amount (RM)</label>
                        <input type="number" step="0.01" name="exp_amount" placeholder="45.00" required class="modal-dark-input">
                    </div>
                    <div class="form-group-field">
                        <label>Category</label>
                        <select name="exp_category" class="modal-dark-input">
                            <option value="Food">Food</option>
                            <option value="Transport">Transport</option>
                            <option value="Shopping">Shopping</option>
                            <option value="Activities">Activities</option>
                        </select>
                    </div>
                </div>
                <div class="form-group-field">
                    <label>Paid By</label>
                    <select name="exp_payer" class="modal-dark-input">
                        <option value="You">You</option>
                        <?php foreach ($tripmates as $mate): ?>
                            <option value="<?php echo htmlspecialchars($mate['name']); ?>"><?php echo htmlspecialchars($mate['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="modal-submit-btn" style="background:#e67e22;">Save Expense</button>
            </form>
        </div>
    </div>

    <div id="mateModal" class="modal-overlay">
        <div class="modal-glass-content">
            <span class="close-btn" onclick="closeModal('mateModal')">&times;</span>
            <h3>Add a Tripmate</h3>
            <form method="POST" action="budget.php" style="margin-top: 15px;">
                <input type="hidden" name="action_add_mate" value="1">
                <div class="form-group-field">
                    <label>Friend's Name</label>
                    <input type="text" name="mate_name" placeholder="e.g., Akmal" required class="modal-dark-input">
                </div>
                <div class="form-group-field">
                    <label>Initial Debt / Split Amount (RM)</label>
                    <input type="number" step="0.01" name="mate_debt" placeholder="0.00" required class="modal-dark-input">
                </div>
                <button type="submit" class="modal-submit-btn" style="background:#9b59b6;">Add Member</button>
            </form>
        </div>
    </div>

    <div id="docModal" class="modal-overlay">
        <div class="modal-glass-content">
            <span class="close-btn" onclick="closeModal('docModal')">&times;</span>
            <h3>Upload Booking Document / Voucher</h3>
            <form method="POST" action="budget.php" enctype="multipart/form-data" style="margin-top: 15px;">
                <input type="hidden" name="action_add_doc" value="1">
                <div class="form-group-field">
                    <label>Voucher / Ticket Type</label>
                    <select name="doc_type" class="modal-dark-input">
                        <option value="Flight Ticket">Flight Ticket</option>
                        <option value="Homestay Voucher">Homestay Voucher</option>
                        <option value="Car Rental Receipt">Car Rental Receipt</option>
                        <option value="Activity Ticket">Activity Ticket</option>
                    </select>
                </div>
                <div class="form-group-field">
                    <label>Choose File (PDF, PNG, JPG)</label>
                    <input type="file" name="doc_file" required style="color:white; margin-top:5px;">
                </div>
                <button type="submit" class="modal-submit-btn" style="background:#3498db;">Upload Document</button>
            </form>
        </div>
    </div>

    <div id="memoryModal" class="modal-overlay">
        <div class="modal-glass-content">
            <span class="close-btn" onclick="closeModal('memoryModal')">&times;</span>
            <h3>Log a Travel Memory Image</h3>
            <form method="POST" action="budget.php" enctype="multipart/form-data" style="margin-top: 15px;">
                <input type="hidden" name="action_add_memory" value="1">
                <div class="form-group-field">
                    <label>Select Picture</label>
                    <input type="file" name="mem_image" accept="image/*" required style="color:white; margin-top:5px;">
                </div>
                <div class="form-group-field">
                    <label>Short Caption / Journal Note</label>
                    <input type="text" name="mem_caption" placeholder="e.g., Beautiful evening walk!" required class="modal-dark-input">
                </div>
                <button type="submit" class="modal-submit-btn" style="background:#1abc9c;">Save Memory</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/budget.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        if(params.get('success') === 'budget') {
            Swal.fire('Budget Updated!', 'The new budget limit was updated successfully.', 'success');
        } else if(params.get('success') === 'expense') {
            Swal.fire('Expense Added!', 'The new live expense was saved successfully to the database.', 'success');
        } else if(params.get('success') === 'doc') {
            Swal.fire('Document Uploaded!', 'The ticket document was uploaded successfully to the Booking Wallet.', 'success');
        } else if(params.get('success') === 'memory') {
            Swal.fire('Memory Logged!', 'The memory photo was saved successfully to the Memory Hall.', 'success');
        } else if(params.get('success') === 'mate') {
            Swal.fire('Tripmate Added!', 'Your travel companion was added successfully to the trip.', 'success');
        } else if(params.get('success') === 'finished') {
            Swal.fire('Trip Finished!', 'This trip has been closed and moved to the History archive.', 'success');
        }
    });
    </script>
</body>
</html>