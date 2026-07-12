<?php
// admin_homestay.php - Admin Verification & Moderation Desk
require_once 'toyyibpay_config.php';

// Verify Admin role session
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$message = "";

// --- MONTHLY PERFORMANCE REPORT GENERATOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_generate_report'])) {
    $owner_id = intval($_POST['owner_id']);
    $month = $_POST['report_month'];
    
    // Fetch homestay IDs for this owner
    $h_stmt = $pdo->prepare("SELECT id, name FROM homestays WHERE user_id = ?");
    $h_stmt->execute([$owner_id]);
    $stays = $h_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($stays)) {
        $stay_ids = array_column($stays, 'id');
        $in_clause = implode(',', array_fill(0, count($stay_ids), '?'));
        
        // Count bookings for this owner
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE homestay_id IN ($in_clause) AND booking_status != 'Cancelled'");
        $stmt_count->execute($stay_ids);
        $b_count = $stmt_count->fetchColumn();
        
        // Completed bookings count
        $stmt_comp = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE homestay_id IN ($in_clause) AND booking_status = 'Completed'");
        $stmt_comp->execute($stay_ids);
        $c_count = $stmt_comp->fetchColumn();
        
        // Cancelled bookings count
        $stmt_canc = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE homestay_id IN ($in_clause) AND booking_status = 'Cancelled'");
        $stmt_canc->execute($stay_ids);
        $can_count = $stmt_canc->fetchColumn();
        
        // Total revenue
        $stmt_rev = $pdo->prepare("SELECT SUM(total_price) FROM bookings WHERE homestay_id IN ($in_clause) AND payment_status = 'Paid'");
        $stmt_rev->execute($stay_ids);
        $rev = $stmt_rev->fetchColumn() ?: 0.00;
        
        $commission = $rev * 0.10;
        $earnings = $rev * 0.90;
        
        // Average rating & review counts
        $stmt_revs = $pdo->prepare("SELECT COUNT(*), AVG(rating) FROM homestay_reviews WHERE homestay_id IN ($in_clause)");
        $stmt_revs->execute($stay_ids);
        $revs_data = $stmt_revs->fetch();
        $reviews_count = $revs_data['COUNT(*)'] ?: 0;
        $avg_rating = $revs_data['AVG(rating)'] ?: 4.5;
        
        // Most popular homestay
        $stmt_pop = $pdo->prepare("SELECT h.name, COUNT(b.id) as b_count 
                                   FROM bookings b 
                                   JOIN homestays h ON b.homestay_id = h.id 
                                   WHERE b.homestay_id IN ($in_clause)
                                   GROUP BY b.homestay_id 
                                   ORDER BY b_count DESC LIMIT 1");
        $stmt_pop->execute($stay_ids);
        $pop_data = $stmt_pop->fetch();
        $popular_homestay = $pop_data ? $pop_data['name'] : 'N/A';
        
        // Occupancy Rate (Simulated metric based on bookings)
        $occupancy_rate = min(100.00, floatval(($b_count * 15)));
        
        // Save to owner_reports table
        $chk = $pdo->prepare("SELECT id FROM owner_reports WHERE owner_id = ? AND report_month = ?");
        $chk->execute([$owner_id, $month]);
        $existing_id = $chk->fetchColumn();
        
        if ($existing_id) {
            $upd = $pdo->prepare("UPDATE owner_reports SET bookings_count = ?, completed_count = ?, cancelled_count = ?, total_revenue = ?, commission = ?, earnings = ?, avg_rating = ?, reviews_count = ?, popular_homestay = ?, occupancy_rate = ? WHERE id = ?");
            $upd->execute([$b_count, $c_count, $can_count, $rev, $commission, $earnings, $avg_rating, $reviews_count, $popular_homestay, $occupancy_rate, $existing_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO owner_reports (owner_id, report_month, bookings_count, completed_count, cancelled_count, total_revenue, commission, earnings, avg_rating, reviews_count, popular_homestay, occupancy_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$owner_id, $month, $b_count, $c_count, $can_count, $rev, $commission, $earnings, $avg_rating, $reviews_count, $popular_homestay, $occupancy_rate]);
        }
        
        $message = "Monthly performance report for $month has been generated and sent to owner dashboard.";
    } else {
        $message = "Cannot generate report: Owner has no registered properties.";
    }
}

// --- REVIEW MODERATION HANDLERS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_delete_review'])) {
        $rev_id = intval($_POST['review_id']);
        
        $stmt = $pdo->prepare("DELETE FROM homestay_reviews WHERE id = ?");
        $stmt->execute([$rev_id]);
        
        $message = "Traveller review has been deleted permanently.";
    } elseif (isset($_POST['action_toggle_review_visibility'])) {
        $rev_id = intval($_POST['review_id']);
        
        $stmt = $pdo->prepare("UPDATE homestay_reviews SET is_hidden = 1 - is_hidden WHERE id = ?");
        $stmt->execute([$rev_id]);
        
        $message = "Review visibility status toggled successfully.";
    } elseif (isset($_POST['action_reply_review'])) {
        $rev_id = intval($_POST['review_id']);
        $reply = trim($_POST['reply_text']);
        
        $stmt = $pdo->prepare("UPDATE homestay_reviews SET admin_reply = ? WHERE id = ?");
        $stmt->execute([$reply, $rev_id]);
        
        $message = "Admin response posted successfully.";
    }
}

// Handle Approve / Reject Form Actions (New Stay applications)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_homestay'])) {
        $id = intval($_POST['homestay_id']);
        
        $stmt = $pdo->prepare("UPDATE homestays SET approval_status = 'Published' WHERE id = ?");
        $stmt->execute([$id]);
        
        // Notify host
        $h_stmt = $pdo->prepare("SELECT user_id, name FROM homestays WHERE id = ?");
        $h_stmt->execute([$id]);
        $h_data = $h_stmt->fetch(PDO::FETCH_ASSOC);
        if ($h_data) {
            $msg = "Congratulations! Your listing request for '" . $h_data['name'] . "' has been approved and is now Published.";
            $n_stmt = $pdo->prepare("INSERT INTO owner_notifications (owner_id, message) VALUES (?, ?)");
            $n_stmt->execute([$h_data['user_id'], $msg]);
        }
        
        $message = "Listing successfully approved and is now Published!";
    } elseif (isset($_POST['reject_homestay'])) {
        $id = intval($_POST['homestay_id']);
        $reason = isset($_POST['reject_reason']) ? trim($_POST['reject_reason']) : 'Verification documents are invalid or incomplete.';
        
        $stmt = $pdo->prepare("UPDATE homestays SET approval_status = 'Rejected', reject_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $id]);
        
        // Notify host
        $h_stmt = $pdo->prepare("SELECT user_id, name FROM homestays WHERE id = ?");
        $h_stmt->execute([$id]);
        $h_data = $h_stmt->fetch(PDO::FETCH_ASSOC);
        if ($h_data) {
            $msg = "Your listing request for '" . $h_data['name'] . "' was rejected by Admin. Reason: " . $reason;
            $n_stmt = $pdo->prepare("INSERT INTO owner_notifications (owner_id, message) VALUES (?, ?)");
            $n_stmt->execute([$h_data['user_id'], $msg]);
        }
        
        $message = "Listing request rejected. Host notification has been sent.";
    }
}

// Fetch stats summary
$stmtStats = $pdo->query("SELECT approval_status, COUNT(*) as count FROM homestays GROUP BY approval_status");
$statsData = $stmtStats->fetchAll(PDO::FETCH_KEY_PAIR);
$total_listings = array_sum($statsData);
$approved_listings = ($statsData['Published'] ?? 0) + ($statsData['Live'] ?? 0) + ($statsData['Approved'] ?? 0);
$pending_listings = $statsData['Pending Approval'] ?? 0;

// Filters
$search = $_GET['search'] ?? '';
$filter_state = $_GET['filter_state'] ?? '';
$filter_category = $_GET['filter_category'] ?? '';
$filter_status = $_GET['filter_status'] ?? 'Pending Approval'; // Default to show pending review first

$query = "SELECT h.*, u.fullname, u.email, u.phone 
          FROM homestays h 
          JOIN users u ON h.user_id = u.id 
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.fullname LIKE ? OR h.name LIKE ? OR u.email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}
if (!empty($filter_state) && $filter_state !== 'All States') {
    $query .= " AND h.state = ?";
    $params[] = $filter_state;
}
if (!empty($filter_category) && $filter_category !== 'All Categories') {
    $query .= " AND h.category = ?";
    $params[] = $filter_category;
}
if (!empty($filter_status) && $filter_status !== 'All Status') {
    if ($filter_status === 'Published') {
        $query .= " AND (h.approval_status = 'Published' OR h.approval_status = 'Live' OR h.approval_status = 'Approved')";
    } else {
        $query .= " AND h.approval_status = ?";
        $params[] = $filter_status;
    }
}

$query .= " ORDER BY h.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$moderation_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin bookings grouped by owner & homestay
$stmt_admin_bkg = $pdo->query("
    SELECT b.*, u.fullname as guest_name, h.name as stay_name, h.id as stay_id,
           o.fullname as owner_name, o.id as owner_id,
           r.rating, r.feedback_text, r.id as review_id, r.is_hidden, r.admin_reply
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN homestays h ON b.homestay_id = h.id
    JOIN users o ON h.user_id = o.id
    LEFT JOIN homestay_reviews r ON b.id = r.booking_id
    ORDER BY o.fullname, h.name, b.id DESC
");
$admin_bookings = $stmt_admin_bkg->fetchAll(PDO::FETCH_ASSOC);

$grouped_bookings = [];
foreach ($admin_bookings as $bkg) {
    $ow = $bkg['owner_name'];
    $st = $bkg['stay_name'];
    if (!isset($grouped_bookings[$ow])) {
        $grouped_bookings[$ow] = [];
    }
    if (!isset($grouped_bookings[$ow][$st])) {
        $grouped_bookings[$ow][$st] = [];
    }
    $grouped_bookings[$ow][$st][] = $bkg;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> Admin Portal - Homestay Moderation Desk</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css?v=<?= time() ?>">
    <style>
        body {
            background: linear-gradient(rgba(10, 25, 18, 0.85), rgba(5, 15, 10, 0.95)), no-repeat center center fixed;
            background-size: cover;
            color: #ffffff;
        }
        .admin-workspace-container {
            background: #021a11;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 30px;
            margin: 20px auto;
            max-width: 1300px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        .stats-summary .stat-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 20px;
            flex: 1;
            position: relative;
        }
        .stat-card h4 {
            color: #f4cb66;
            margin-bottom: 8px;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .stat-card h2 {
            font-size: 1.8rem;
            color: white;
        }
        .homestay-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            display: flex;
            gap: 24px;
            align-items: flex-start;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        .homestay-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.2);
            background: rgba(255, 255, 255, 0.08);
        }
        .homestay-img {
            width: 260px;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .homestay-info {
            flex: 1;
        }
        .homestay-title {
            font-size: 1.35rem;
            color: #ffffff;
            margin-bottom: 6px;
            font-family: 'Playfair Display', serif;
        }
        .homestay-owner {
            color: #f4cb66;
            font-size: 0.88rem;
            margin-bottom: 12px;
        }
        .homestay-details p {
            margin: 6px 0;
            color: rgba(255,255,255,0.85);
            font-size: 0.92rem;
        }
        .action-btns {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            flex-wrap: wrap;
            align-items: center;
        }
        .btn-approve, .btn-reject, .btn-view {
            padding: 9px 18px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.25s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }
        .btn-approve {
            background: #10b981;
            color: white;
        }
        .btn-approve:hover {
            background: #059669;
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .btn-reject {
            background: #ef4444;
            color: white;
        }
        .btn-reject:hover {
            background: #dc2626;
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
        }
        .btn-view {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .btn-view:hover {
            background: rgba(255,255,255,0.2);
        }
        .message-alert {
            background: rgba(16, 185, 129, 0.15);
            border-left: 4px solid #10b981;
            color: #10b981;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav__header">
            <div class="nav__logo"><a href="admin.php" class="logo"><?= htmlspecialchars($system_name) ?><span>.</span></a></div>
        </div>
        <ul class="nav__links" id="nav-links">
            <li><a href="booking_history.php">Booking History</a></li>
            <li><a href="add_attractions.php">Add Attractions</a></li>
            <li><a href="admin_commission.php">Admin Commission</a></li>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="admin_homestay.php" style="color: #f4cb66; font-weight: 600;">Homestay Approvals</a></li>
            <li><a href="index.php?action=logout" style="background: #ef4444; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">LOGOUT</a></li>
        </ul>
    </nav>

    <div class="admin-workspace-container">
        <header class="admin-workspace-header" style="margin-bottom: 25px;">
            <div class="header-titles">
                <h1 style="font-family: 'Playfair Display', serif; font-size: 2.2rem; color: #ffffff;">Homestay Moderation Desk</h1>
                <p style="color: rgba(255,255,255,0.7); font-size: 0.95rem;">Verify documents and moderate homestay listing activations on <?= htmlspecialchars($system_name) ?>.</p>
            </div>
        </header>

        <!-- Stats Section -->
        <div class="stats-summary" style="display: flex; gap: 20px; margin-bottom: 25px;">
            <div class="stat-card">
                <h4>Total Listings</h4>
                <h2><?php echo $total_listings; ?></h2>
                <i class="ri-home-line" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 2rem; color: rgba(255,255,255,0.05);"></i>
            </div>
            <div class="stat-card">
                <h4>Approved & Published</h4>
                <h2><?php echo $approved_listings; ?></h2>
                <i class="ri-checkbox-circle-line" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 2rem; color: rgba(255,255,255,0.05);"></i>
            </div>
            <div class="stat-card" style="border-left: 4px solid #f4cb66;">
                <h4>Pending Verification</h4>
                <h2><?php echo $pending_listings; ?></h2>
                <i class="ri-time-line" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 2rem; color: rgba(255,255,255,0.05);"></i>
            </div>
        </div>

        <!-- Search Filters Row -->
        <div style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 25px;">
            <form action="admin_homestay.php" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                <input type="text" name="search" placeholder="Search by property or host name..." value="<?php echo htmlspecialchars($search); ?>" style="flex:1; min-width:240px; padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(0,0,0,0.2); color: white; outline: none;">
                
                <select name="filter_state" style="padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(14,58,32,0.9); color: white;">
                    <option value="">All States</option>
                    <?php foreach(["Terengganu","Perak","Selangor","Pulau Pinang","Pahang","Johor","Sabah","Sarawak","Kedah","Kelantan"] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php if($filter_state==$st) echo 'selected'; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="filter_category" style="padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(14,58,32,0.9); color: white;">
                    <option value="">All Categories</option>
                    <option value="Chalet" <?php if($filter_category=='Chalet') echo 'selected'; ?>>Chalet</option>
                    <option value="Apartment" <?php if($filter_category=='Apartment') echo 'selected'; ?>>Apartment</option>
                    <option value="Villa" <?php if($filter_category=='Villa') echo 'selected'; ?>>Villa</option>
                    <option value="Other" <?php if($filter_category=='Other') echo 'selected'; ?>>Other</option>
                </select>

                <select name="filter_status" style="padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(14,58,32,0.9); color: white;">
                    <option value="All Status" <?php if($filter_status=='All Status') echo 'selected'; ?>>All Statuses</option>
                    <option value="Pending Approval" <?php if($filter_status=='Pending Approval') echo 'selected'; ?>>Pending Moderation</option>
                    <option value="Published" <?php if($filter_status=='Published') echo 'selected'; ?>>Published (Live)</option>
                    <option value="Registered" <?php if($filter_status=='Registered') echo 'selected'; ?>>Registered</option>
                    <option value="Rejected" <?php if($filter_status=='Rejected') echo 'selected'; ?>>Rejected</option>
                </select>

                <button type="submit" style="padding: 10px 24px; background: #f4cb66; color: #0e3a20; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s;">Filter Search</button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message-alert"><i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Moderation List -->
        <?php if (empty($moderation_list)): ?>
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); padding: 50px; border-radius: 20px; text-align: center;">
                <i class="ri-inbox-line" style="font-size: 3.5rem; color: #f4cb66; margin-bottom: 12px; display: block;"></i>
                <h3>No Properties Found</h3>
                <p style="color: rgba(255,255,255,0.6);">There are no pending listings matching your filters.</p>
            </div>
        <?php else: ?>
            <div class="homestays-list">
                <?php foreach ($moderation_list as $homestay): ?>
                    <div class="homestay-card">
                        <img src="<?php echo !empty($homestay['cover_image']) ? htmlspecialchars($homestay['cover_image']) : 'images/default_place.jpg'; ?>" alt="Cover Image" class="homestay-img" onerror="this.src='images/default_place.jpg';">
                        
                        <div class="homestay-info">
                            <h3 class="homestay-title"><?php echo htmlspecialchars($homestay['name']); ?></h3>
                            <div class="homestay-owner"><i class="ri-user-star-line"></i> Host: <?php echo htmlspecialchars($homestay['fullname']); ?> (<?php echo htmlspecialchars($homestay['phone']); ?> | <?php echo htmlspecialchars($homestay['email']); ?>)</div>
                            
                            <div class="homestay-details">
                                <p><strong><i class="ri-map-pin-line"></i> Location:</strong> <?php echo htmlspecialchars($homestay['address']); ?>, <?php echo htmlspecialchars($homestay['district']); ?>, <?php echo htmlspecialchars($homestay['state']); ?></p>
                                <p><strong><i class="ri-price-tag-3-line"></i> Price Model:</strong> RM <?php echo number_format($homestay['price_per_night'], 2); ?>/night (<?php echo htmlspecialchars($homestay['pricing_type']); ?>)</p>
                                <p><strong><i class="ri-home-line"></i> Facilities:</strong> 
                                    <?php 
                                    $facs = json_decode($homestay['facilities'], true);
                                    if (is_array($facs)) {
                                        echo implode(', ', array_map('htmlspecialchars', $facs));
                                    } else {
                                        echo htmlspecialchars($homestay['facilities']);
                                    }
                                    ?>
                                </p>
                                <p>
                                    <strong><i class="ri-wallet-3-line"></i> RM29 Payment Reference:</strong> 
                                    <span style="font-weight: 700; color: <?php echo $homestay['payment_status'] == 'Paid' ? '#10b981' : '#ef4444'; ?>">
                                        <?php echo htmlspecialchars($homestay['payment_status']); ?> (Invoice #VYG-2026-00<?php echo $homestay['id']; ?>)
                                    </span>
                                </p>
                                <p>
                                    <strong><i class="ri-shield-check-line"></i> Moderation Status:</strong> 
                                    <span style="font-weight: 700; color: <?php echo ($homestay['approval_status'] == 'Published' || $homestay['approval_status'] == 'Live') ? '#10b981' : ($homestay['approval_status'] == 'Rejected' ? '#ef4444' : '#f4cb66'); ?>">
                                        <?php echo htmlspecialchars($homestay['approval_status']); ?>
                                    </span>
                                </p>
                            </div>
                            
                            <div class="action-btns">
                                <a href="#" class="btn-view" onclick="event.preventDefault(); openDocsModal('<?php echo htmlspecialchars($homestay['cover_image'] ?? ''); ?>', '<?php echo htmlspecialchars($homestay['ic_copy'] ?? ''); ?>', '<?php echo htmlspecialchars($homestay['utility_bill'] ?? ''); ?>', '<?php echo htmlspecialchars($homestay['ssm_doc'] ?? ''); ?>', '<?php echo htmlspecialchars($homestay['business_license'] ?? ''); ?>', '<?php echo htmlspecialchars($homestay['ownership_proof'] ?? ''); ?>', '<?php echo htmlspecialchars($homestay['facility_images'] ?? '[]'); ?>')"><i class="ri-eye-line"></i> View Uploads & Files</a>
                                
                                <?php if ($homestay['approval_status'] === 'Pending Approval'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="homestay_id" value="<?php echo $homestay['id']; ?>">
                                        <button type="submit" name="approve_homestay" class="btn-approve"><i class="ri-check-line"></i> Approve Listing</button>
                                    </form>
                                    
                                    <form method="POST" style="display:inline; margin-left: 8px;">
                                        <input type="hidden" name="homestay_id" value="<?php echo $homestay['id']; ?>">
                                        <input type="text" name="reject_reason" placeholder="Reason for rejection..." required style="padding: 9px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.3); color: white; font-size: 0.85rem; outline: none; width: 200px;">
                                        <button type="submit" name="reject_homestay" class="btn-reject"><i class="ri-close-line"></i> Reject</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ==================== ADMIN BOOKING MANAGEMENT DESK ==================== -->
        <header class="admin-workspace-header" style="margin-top: 50px; margin-bottom: 20px;">
            <div class="header-titles">
                <h2 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #ffffff;">Global Bookings Management Desk</h2>
                <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">View, reply, or moderate traveler feedback and generate monthly performance reports for hosts.</p>
            </div>
        </header>

        <div style="background: rgba(255, 255, 255, 0.05); padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 40px;">
            <?php if (empty($grouped_bookings)): ?>
                <p style="color: rgba(255,255,255,0.5); text-align: center; font-style: italic; padding: 20px;">No bookings registered on the platform yet.</p>
            <?php else: ?>
                <?php foreach ($grouped_bookings as $owner_name => $stays): ?>
                    <div style="margin-bottom: 30px; border-bottom: 1px dashed rgba(255,255,255,0.15); padding-bottom: 25px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(14, 58, 32, 0.4); padding: 12px 20px; border-radius: 12px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                            <h3 style="color: #f4cb66; margin: 0; font-size: 1.15rem;"><i class="ri-user-star-line"></i> Host: <?php echo htmlspecialchars($owner_name); ?></h3>
                            
                            <!-- Monthly Performance Report Form -->
                            <?php
                            reset($stays);
                            $first_stay = current($stays);
                            $owner_id = $first_stay[0]['owner_id'];
                            ?>
                            <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                                <input type="hidden" name="owner_id" value="<?php echo $owner_id; ?>">
                                <select name="report_month" required style="padding: 6px 12px; border-radius: 6px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); color: white; font-size: 0.85rem; outline: none;">
                                    <option value="July 2026">July 2026</option>
                                    <option value="August 2026">August 2026</option>
                                    <option value="September 2026">September 2026</option>
                                    <option value="October 2026">October 2026</option>
                                </select>
                                <button type="submit" name="action_generate_report" style="background: #f4cb66; color: #0e3a20; font-weight: 700; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-chart-line"></i> Send Report</button>
                            </form>
                        </div>
                        
                        <?php foreach ($stays as $stay_name => $bkgs): ?>
                            <div style="margin-left: 20px; margin-bottom: 20px;">
                                <h4 style="color: #ffffff; margin-bottom: 10px; font-size: 1.05rem;"><i class="ri-home-4-line"></i> Property: <?php echo htmlspecialchars($stay_name); ?></h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                                    <?php foreach ($bkgs as $b): 
                                        $today = date('Y-m-d');
                                        $b_status = $b['booking_status'];
                                        $p_status = $b['payment_status'];
                                        
                                        if ($p_status !== 'Paid') {
                                            $p_status = 'Awaiting Payment';
                                            $b_status = 'Pending';
                                        } else {
                                            if ($today < $b['check_in']) {
                                                $b_status = 'Upcoming';
                                            } elseif ($today >= $b['check_in'] && $today <= $b['check_out']) {
                                                $b_status = 'Checked In';
                                            } else {
                                                $b_status = 'Completed';
                                            }
                                        }
                                    ?>
                                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 15px; display: flex; flex-direction: column; justify-content: space-between;">
                                            <div>
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                    <span style="font-weight: 700; font-size: 0.85rem; color: #f4cb66;">#<?php echo $b['booking_no'] ?: 'VYG-BKG-'.$b['id']; ?></span>
                                                    <span style="font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; background: <?php echo $b_status === 'Completed' ? 'rgba(16,185,129,0.2)' : 'rgba(244,203,102,0.2)'; ?>; color: <?php echo $b_status === 'Completed' ? '#10b981' : '#f4cb66'; ?>;">
                                                        <?php echo htmlspecialchars($b_status); ?>
                                                    </span>
                                                </div>
                                                <p style="margin: 4px 0; font-size: 0.85rem;"><strong>Guest:</strong> <?php echo htmlspecialchars($b['guest_name']); ?></p>
                                                <p style="margin: 4px 0; font-size: 0.85rem;"><strong>Dates:</strong> <?php echo $b['check_in']; ?> to <?php echo $b['check_out']; ?></p>
                                                <p style="margin: 4px 0; font-size: 0.85rem;"><strong>Total Paid:</strong> RM <?php echo number_format($b['total_price'], 2); ?></p>
                                                
                                                <!-- Review Display -->
                                                <div style="margin-top: 10px; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 10px;">
                                                    <?php if ($b['rating']): ?>
                                                        <p style="margin: 2px 0; color: #f4cb66; font-size: 0.8rem;">
                                                            <?php echo str_repeat('⭐', $b['rating']); ?>
                                                            <span style="color: rgba(255,255,255,0.5);">(<?php echo $b['rating']; ?>/5 Stars)</span>
                                                        </p>
                                                        <p style="margin: 4px 0; font-style: italic; font-size: 0.82rem; background: rgba(0,0,0,0.25); padding: 6px; border-radius: 6px; color: #fff;">
                                                            "<?php echo htmlspecialchars($b['feedback_text']); ?>"
                                                            <?php if ($b['is_hidden']): ?>
                                                                <br><strong style="color: #ef4444; font-size: 0.72rem;">[HIDDEN FROM OWNER]</strong>
                                                            <?php endif; ?>
                                                        </p>
                                                        <?php if ($b['admin_reply']): ?>
                                                            <p style="margin: 4px 0 0 10px; font-size: 0.78rem; color: #10b981; font-weight: 500;">
                                                                <i class="ri-reply-line"></i> Admin Reply: "<?php echo htmlspecialchars($b['admin_reply']); ?>"
                                                            </p>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <p style="margin: 4px 0; color: rgba(255,255,255,0.4); font-size: 0.8rem; font-style: italic;">No Review Yet</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Review Management Actions -->
                                            <?php if ($b['rating']): ?>
                                                <div style="margin-top: 12px; display: flex; gap: 6px; flex-wrap: wrap;">
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="review_id" value="<?php echo $b['review_id']; ?>">
                                                        <button type="submit" name="action_toggle_review_visibility" class="btn-view" style="padding: 4px 8px; font-size: 0.72rem;">
                                                            <i class="<?php echo $b['is_hidden'] ? 'ri-eye-line' : 'ri-eye-off-line'; ?>"></i> <?php echo $b['is_hidden'] ? 'Show Review' : 'Hide Review'; ?>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this traveler review permanently?');">
                                                        <input type="hidden" name="review_id" value="<?php echo $b['review_id']; ?>">
                                                        <button type="submit" name="action_delete_review" class="btn-reject" style="padding: 4px 8px; font-size: 0.72rem;">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                        </button>
                                                    </form>
                                                    
                                                    <!-- Reply Input Trigger -->
                                                    <button onclick="triggerReplyModal(<?php echo $b['review_id']; ?>, '<?php echo htmlspecialchars(addslashes($b['guest_name'])); ?>')" class="btn-approve" style="padding: 4px 8px; font-size: 0.72rem; background: #3b82f6;">
                                                        <i class="ri-reply-line"></i> Reply
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Documents Modal -->
    <div id="docsModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6);">
        <div style="background-color: rgba(14, 58, 32, 0.98); backdrop-filter: blur(16px); margin: 3% auto; padding: 25px; border: 1px solid rgba(255,255,255,0.15); width: 85%; max-width: 900px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 12px; margin-bottom: 20px;">
                <h3 style="margin: 0; font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #f4cb66;"><i class="ri-folder-open-line"></i> Verification Documents & Upload Sheets</h3>
                <span onclick="closeDocsModal()" style="color: #aaa; font-size: 30px; font-weight: bold; cursor: pointer;">&times;</span>
            </div>
            
            <div id="modal-content-area" style="display: flex; flex-direction: column; gap: 20px; max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                <!-- Dynamically loaded by JS -->
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div id="replyModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
        <div style="background-color: rgba(14, 58, 32, 0.98); backdrop-filter: blur(16px); padding: 25px; border: 1px solid rgba(255,255,255,0.15); width: 90%; max-width: 450px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); color: white;">
            <h3 style="margin-top:0; color:#f4cb66; font-family:'Playfair Display', serif;"><i class="ri-reply-line"></i> Post Reply to Guest Review</h3>
            <p id="reply-guest-name" style="font-size:0.85rem; color:rgba(255,255,255,0.7); margin-bottom: 12px;"></p>
            
            <form method="POST" action="admin_homestay.php">
                <input type="hidden" name="review_id" id="reply-review-id">
                <div style="margin-bottom: 15px;">
                    <textarea name="reply_text" rows="4" placeholder="Type your reply here..." required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.3); color: white; font-family: inherit; font-size: 0.88rem; outline: none; resize: none;"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeReplyModal()" style="padding: 8px 16px; border-radius: 8px; border: none; background: rgba(255,255,255,0.1); color: white; cursor: pointer;">Cancel</button>
                    <button type="submit" name="action_reply_review" style="padding: 8px 18px; border-radius: 8px; border: none; background: #f4cb66; color: #0e3a20; font-weight: 700; cursor: pointer;">Post Reply</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDocsModal(cover, ic, utility, ssm, license, ownership, facilityImagesJson) {
            const modal = document.getElementById('docsModal');
            const contentArea = document.getElementById('modal-content-area');
            
            contentArea.innerHTML = '';
            
            // Build visual docs list
            const docs = [
                { name: 'Cover Exterior Photo', path: cover },
                { name: 'Owner Identity Card (IC Copy)', path: ic },
                { name: 'Utility Bill (Address Verification)', path: utility },
                { name: 'SSM Company Certificate', path: ssm },
                { name: 'Local Authority License', path: license },
                { name: 'Proof of Ownership', path: ownership }
            ];
            
            let html = '<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">';
            docs.forEach(doc => {
                if (doc.path) {
                    const ext = doc.path.split('.').pop().toLowerCase();
                    html += `<div style="border: 1px solid rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; background: rgba(255,255,255,0.03);">`;
                    html += `<h4 style="margin-top: 0; color: #f4cb66; font-size:0.9rem; margin-bottom:10px;">${doc.name}</h4>`;
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                        html += `<img src="${doc.path}" style="width: 100%; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">`;
                    } else {
                        html += `<a href="${doc.path}" target="_blank" style="display: inline-flex; align-items:center; gap:6px; padding: 10px 16px; background: #f4cb66; color: #0e3a20; text-decoration: none; border-radius: 6px; font-weight: 700; font-size:0.8rem;"><i class="ri-external-link-line"></i> Open File</a>`;
                    }
                    html += `</div>`;
                } else {
                    html += `<div style="border: 1px solid rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; background: rgba(0,0,0,0.15);">`;
                    html += `<h4 style="margin-top: 0; color: rgba(255,255,255,0.4); font-size:0.9rem;">${doc.name}</h4>`;
                    html += `<p style="color: rgba(255,255,255,0.3); font-style: italic; margin: 0; font-size:0.85rem;">Not uploaded</p>`;
                    html += `</div>`;
                }
            });
            html += '</div>';

            // Decode facility images
            let facImages = [];
            try {
                facImages = JSON.parse(facilityImagesJson);
            } catch(e) {
                facImages = [];
            }

            html += `<div style="margin-top: 25px; border-top: 1px dashed rgba(255,255,255,0.15); padding-top: 20px;">`;
            html += `<h4 style="color: #f4cb66; font-family:'Playfair Display', serif; margin-bottom: 12px; font-size: 1.15rem;"><i class="ri-gallery-line"></i> Uploaded Facility Photos (Enforced Min 3)</h4>`;
            
            if (Array.isArray(facImages) && facImages.length > 0) {
                html += `<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px;">`;
                facImages.forEach(img => {
                    html += `
                        <div style="border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; overflow:hidden;">
                            <img src="${img}" style="width: 100%; height: 120px; object-fit: cover; display:block;">
                        </div>
                    `;
                });
                html += `</div>`;
            } else {
                html += `<p style="color: rgba(255,255,255,0.4); font-style: italic;">No facility photos found.</p>`;
            }
            html += `</div>`;
            
            contentArea.innerHTML = html;
            modal.style.display = "block";
        }

        function closeDocsModal() {
            document.getElementById('docsModal').style.display = "none";
        }

        function triggerReplyModal(reviewId, guestName) {
            document.getElementById('reply-review-id').value = reviewId;
            document.getElementById('reply-guest-name').innerText = "Replying to feedback from " + guestName;
            document.getElementById('replyModal').style.display = 'flex';
        }

        function closeReplyModal() {
            document.getElementById('replyModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('docsModal');
            const replyModal = document.getElementById('replyModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
            if (event.target == replyModal) {
                replyModal.style.display = "none";
            }
        }
    </script>
</body>
</html>
