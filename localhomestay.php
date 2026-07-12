<?php
// localhomestay.php - Owner Listing Management Dashboard
require_once 'toyyibpay_config.php';

// Check user login session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$owner_name = isset($_SESSION['user_fullname']) ? $_SESSION['user_fullname'] : 'Host User';

// Clear owner notifications handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_notifications'])) {
    $up_n = $pdo->prepare("UPDATE owner_notifications SET is_read = 1 WHERE owner_id = ?");
    $up_n->execute([$user_id]);
    header("Location: localhomestay.php");
    exit;
}

// Fetch host metrics, stays calendar, and reviews
$stmt_owner_stays = $pdo->prepare("SELECT id, name FROM homestays WHERE user_id = ?");
$stmt_owner_stays->execute([$user_id]);
$owner_stays = $stmt_owner_stays->fetchAll(PDO::FETCH_ASSOC);
$owner_stay_ids = array_column($owner_stays, 'id');

$host_bookings = [];
$booked_dates = [];
$blocked_dates = [];
$avg_rating_all = 4.5;
$latest_reviews = [];
$owner_reports_list = [];

// Ensure homestay_blocked_dates table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS homestay_blocked_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homestay_id INT NOT NULL,
    blocked_date DATE NOT NULL,
    owner_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_block (homestay_id, blocked_date)
)");

// --- HANDLE BLOCK / UNBLOCK DATE POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_toggle_block_date'])) {
    $toggle_date      = $_POST['block_date'] ?? '';
    $toggle_stay_id   = intval($_POST['block_homestay_id'] ?? 0);
    // Security: ensure this stay belongs to the logged-in owner
    if ($toggle_stay_id > 0 && in_array($toggle_stay_id, $owner_stay_ids) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toggle_date)) {
        $chk = $pdo->prepare("SELECT id FROM homestay_blocked_dates WHERE homestay_id = ? AND blocked_date = ?");
        $chk->execute([$toggle_stay_id, $toggle_date]);
        if ($chk->fetch()) {
            // Already blocked → unblock it
            $pdo->prepare("DELETE FROM homestay_blocked_dates WHERE homestay_id = ? AND blocked_date = ?")->execute([$toggle_stay_id, $toggle_date]);
        } else {
            // Not blocked → block it
            $pdo->prepare("INSERT IGNORE INTO homestay_blocked_dates (homestay_id, blocked_date, owner_id) VALUES (?, ?, ?)")->execute([$toggle_stay_id, $toggle_date, $user_id]);
        }
    }
    header("Location: localhomestay.php#calendar-section");
    exit;
}

// Determine which stay to show on calendar
// Priority: stay with most FUTURE bookings → any stay with bookings → first stay
$calendar_stay_id = !empty($owner_stay_ids) ? $owner_stay_ids[0] : 0;
if (!empty($owner_stay_ids)) {
    $in_def = implode(',', array_fill(0, count($owner_stay_ids), '?'));
    $today_cal = date('Y-m-d');
    // First: try stay with the most upcoming (future) bookings
    $def_stmt = $pdo->prepare(
        "SELECT homestay_id, COUNT(*) as cnt FROM bookings 
         WHERE homestay_id IN ($in_def) AND booking_status != 'Cancelled' AND check_out >= ?
         GROUP BY homestay_id ORDER BY cnt DESC LIMIT 1"
    );
    $def_stmt->execute(array_merge($owner_stay_ids, [$today_cal]));
    $def_row = $def_stmt->fetch(PDO::FETCH_ASSOC);
    if ($def_row) {
        $calendar_stay_id = intval($def_row['homestay_id']);
    } else {
        // Fallback: stay with the most bookings overall
        $def_stmt2 = $pdo->prepare(
            "SELECT homestay_id, COUNT(*) as cnt FROM bookings 
             WHERE homestay_id IN ($in_def)
             GROUP BY homestay_id ORDER BY cnt DESC LIMIT 1"
        );
        $def_stmt2->execute($owner_stay_ids);
        $def_row2 = $def_stmt2->fetch(PDO::FETCH_ASSOC);
        if ($def_row2) $calendar_stay_id = intval($def_row2['homestay_id']);
    }
}
if (isset($_GET['cal_stay']) && in_array(intval($_GET['cal_stay']), $owner_stay_ids)) {
    $calendar_stay_id = intval($_GET['cal_stay']);
}

if (!empty($owner_stay_ids)) {
    $in_clause = implode(',', array_fill(0, count($owner_stay_ids), '?'));
    
    // Fetch bookings list
    $list_stmt = $pdo->prepare("SELECT b.*, u.fullname as guest_name, h.name as stay_name, r.room_name 
                                FROM bookings b 
                                JOIN users u ON b.user_id = u.id 
                                JOIN homestays h ON b.homestay_id = h.id 
                                LEFT JOIN homestay_rooms r ON b.room_id = r.id
                                WHERE b.homestay_id IN ($in_clause) 
                                ORDER BY b.id DESC");
    $list_stmt->execute($owner_stay_ids);
    $host_bookings = $list_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch booked ranges for calendar (for selected stay)
    $cal_stmt = $pdo->prepare("SELECT check_in, check_out FROM bookings 
                               WHERE homestay_id = ? AND booking_status != 'Cancelled'");
    $cal_stmt->execute([$calendar_stay_id]);
    $booked_ranges = $cal_stmt->fetchAll();
    
    foreach ($booked_ranges as $range) {
        $start = new DateTime($range['check_in']);
        $end   = new DateTime($range['check_out']);
        $interval  = new DateInterval('P1D');
        $daterange = new DatePeriod($start, $interval, $end);
        foreach ($daterange as $date) {
            $booked_dates[] = $date->format("Y-m-d");
        }
    }
    
    // Fetch owner-blocked dates for selected stay
    $blk_stmt = $pdo->prepare("SELECT blocked_date FROM homestay_blocked_dates WHERE homestay_id = ?");
    $blk_stmt->execute([$calendar_stay_id]);
    $blocked_dates = array_column($blk_stmt->fetchAll(PDO::FETCH_ASSOC), 'blocked_date');
    
    // Calculate overall average rating and list reviews
    $avg_stmt = $pdo->prepare("SELECT AVG(rating) FROM homestay_reviews WHERE homestay_id IN ($in_clause) AND is_hidden = 0");
    $avg_stmt->execute($owner_stay_ids);
    $avg_rating_all = $avg_stmt->fetchColumn() ?: 4.5;
    
    $rev_stmt = $pdo->prepare("SELECT r.*, u.fullname as guest_name, h.name as stay_name 
                               FROM homestay_reviews r 
                               JOIN users u ON r.user_id = u.id 
                               JOIN homestays h ON r.homestay_id = h.id 
                               WHERE r.homestay_id IN ($in_clause) AND r.is_hidden = 0 
                               ORDER BY r.id DESC LIMIT 6");
    $rev_stmt->execute($owner_stay_ids);
    $latest_reviews = $rev_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch generated reports for this owner
$rep_stmt = $pdo->prepare("SELECT * FROM owner_reports WHERE owner_id = ? ORDER BY id DESC");
$rep_stmt->execute([$user_id]);
$owner_reports_list = $rep_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- AUTO-GENERATE / REFRESH CURRENT MONTH REPORT FROM LIVE DATA ---
$current_month_label = date('F Y'); // e.g. "July 2026"
$month_start = date('Y-m-01');
$month_end   = date('Y-m-t');

if (!empty($owner_stay_ids)) {
    $in_ph = implode(',', array_fill(0, count($owner_stay_ids), '?'));

    // Count bookings this month for this owner's stays
    $bk = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN booking_status = 'Completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN booking_status = 'Cancelled'  THEN 1 ELSE 0 END) as cancelled,
        COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN total_price ELSE 0 END), 0) as revenue
    FROM bookings 
    WHERE homestay_id IN ($in_ph)
      AND check_in BETWEEN ? AND ?");
    $bk->execute(array_merge($owner_stay_ids, [$month_start, $month_end]));
    $bk_data = $bk->fetch(PDO::FETCH_ASSOC);

    // Avg rating across all stays
    $ar = $pdo->prepare("SELECT COALESCE(AVG(rating), 0), COUNT(*) FROM homestay_reviews WHERE homestay_id IN ($in_ph) AND is_hidden = 0");
    $ar->execute($owner_stay_ids);
    [$avg_r, $rev_cnt] = $ar->fetch(PDO::FETCH_NUM);

    // Most popular stay by booking count
    $pop = $pdo->prepare("SELECT h.name, COUNT(b.id) as cnt FROM bookings b JOIN homestays h ON b.homestay_id = h.id WHERE b.homestay_id IN ($in_ph) GROUP BY b.homestay_id ORDER BY cnt DESC LIMIT 1");
    $pop->execute($owner_stay_ids);
    $pop_row = $pop->fetch(PDO::FETCH_ASSOC);
    $popular_name = $pop_row ? $pop_row['name'] : ($owner_stays[0]['name'] ?? 'N/A');

    $gross     = floatval($bk_data['revenue']);
    $commission = round($gross * 0.10, 2);
    $earnings   = round($gross * 0.90, 2);

    // Days in month for occupancy estimate
    $days_in_month = intval(date('t'));
    $total_stays   = count($owner_stay_ids);
    $booked_nights = intval($bk_data['total']) * 2; // estimate 2 nights avg per booking
    $occupancy     = ($total_stays > 0 && $days_in_month > 0)
                   ? min(100, round(($booked_nights / ($days_in_month * $total_stays)) * 100, 2))
                   : 0;

    // Upsert: update if this month's row exists, else insert
    $exists = $pdo->prepare("SELECT id FROM owner_reports WHERE owner_id = ? AND report_month = ?");
    $exists->execute([$user_id, $current_month_label]);
    $existing_row = $exists->fetch();

    if ($existing_row) {
        $upd = $pdo->prepare("UPDATE owner_reports SET 
            bookings_count = ?, completed_count = ?, cancelled_count = ?,
            total_revenue = ?, commission = ?, earnings = ?,
            avg_rating = ?, reviews_count = ?, popular_homestay = ?, occupancy_rate = ?
            WHERE id = ?");
        $upd->execute([
            $bk_data['total'], $bk_data['completed'], $bk_data['cancelled'],
            $gross, $commission, $earnings,
            round(floatval($avg_r), 2), $rev_cnt, $popular_name, $occupancy,
            $existing_row['id']
        ]);
    } else {
        $ins = $pdo->prepare("INSERT INTO owner_reports 
            (owner_id, report_month, bookings_count, completed_count, cancelled_count,
             total_revenue, commission, earnings, avg_rating, reviews_count, popular_homestay, occupancy_rate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $user_id, $current_month_label,
            $bk_data['total'], $bk_data['completed'], $bk_data['cancelled'],
            $gross, $commission, $earnings,
            round(floatval($avg_r), 2), $rev_cnt, $popular_name, $occupancy
        ]);
    }

    // Refresh the list after upsert
    $rep_stmt->execute([$user_id]);
    $owner_reports_list = $rep_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch owner bank statement transactions for completed paid bookings
try {
    $stmt_transactions = $pdo->prepare(
        "SELECT b.id, b.booking_no, b.created_at, b.total_price, b.settlement_id,
                h.name AS homestay_name, r.room_name, u.fullname AS guest_name,
                IFNULL(s.created_at, '') AS transfer_date
         FROM bookings b
         JOIN homestays h ON b.homestay_id = h.id
         LEFT JOIN homestay_rooms r ON b.room_id = r.id
         JOIN users u ON b.user_id = u.id
         LEFT JOIN host_settlements s ON b.settlement_id = s.id
         WHERE h.user_id = ?
           AND b.payment_status = 'Paid'
           AND b.booking_status != 'Cancelled'
         ORDER BY b.created_at DESC"
    );
    $stmt_transactions->execute([$user_id]);
    $statement_items = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $statement_items = [];
}

$statement_summary = [
    'pending_net' => 0.0,
    'transferred_net' => 0.0,
    'total_net' => 0.0,
    'pending_count' => 0,
    'transferred_count' => 0,
];
foreach ($statement_items as $item) {
    $gross = floatval($item['total_price']);
    $commission = round($gross * 0.10, 2);
    $net = round($gross - $commission, 2);
    if (empty($item['settlement_id'])) {
        $statement_summary['pending_net'] += $net;
        $statement_summary['pending_count']++;
    } else {
        $statement_summary['transferred_net'] += $net;
        $statement_summary['transferred_count']++;
    }
    $statement_summary['total_net'] += $net;
}


// Handle Homestay Listing Registration Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_create_homestay'])) {
    $name = trim($_POST['h_name']);
    $category = isset($_POST['homestay_category']) ? $_POST['homestay_category'] : 'Apartment';
    $description = trim($_POST['h_description']);
    $max_guests = intval($_POST['h_max_guests']);
    $price = floatval($_POST['h_price']);
    
    // Multi-pricing structures
    $pricing_type = isset($_POST['h_pricing_type']) ? trim($_POST['h_pricing_type']) : 'Whole House';
    $total_rooms = ($pricing_type === 'Per Room') ? intval($_POST['h_total_rooms']) : 1;
    
    $state = trim($_POST['h_state']);
    $district = trim($_POST['h_district']);
    $address = trim($_POST['h_address']);
    $maps_link = trim($_POST['h_maps']);
    
    // Enforce selection of minimum of 3 facilities
    $facilities_arr = isset($_POST['facilities']) ? $_POST['facilities'] : [];
    if (count($facilities_arr) < 3) {
        die("Error: Please select at least 3 amenities or facilities.");
    }
    $facilities_json = json_encode($facilities_arr);

    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Single file upload utility
    $uploadFile = function($inputName) use ($upload_dir) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            $filename = time() . '_' . rand(1000, 9999) . '_' . basename($_FILES[$inputName]['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $target_file)) {
                return $target_file;
            }
        }
        return NULL;
    };

    // Upload Main Exterior Image (Required)
    $cover_image_path = $uploadFile('cover_image') ?: '';
    if (empty($cover_image_path)) {
        die("Error: The main cover image is required.");
    }

    // Upload Facility Images (Enforce at least 3 images)
    $facility_images_paths = [];
    if (isset($_FILES['facility_images'])) {
        $filesCount = count($_FILES['facility_images']['name']);
        for ($i = 0; $i < $filesCount; $i++) {
            if ($_FILES['facility_images']['error'][$i] === UPLOAD_ERR_OK) {
                $filename = time() . '_' . rand(1000, 9999) . '_' . basename($_FILES['facility_images']['name'][$i]);
                $target_file = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['facility_images']['tmp_name'][$i], $target_file)) {
                    $facility_images_paths[] = $target_file;
                }
            }
        }
    }
    
    if (count($facility_images_paths) < 3) {
        die("Error: Please upload at least 3 facility images.");
    }
    $facility_images_json = json_encode($facility_images_paths);

    // Documents
    $ic_copy = $uploadFile('ic_copy') ?: '';
    $utility_bill = $uploadFile('utility_bill') ?: '';
    $ssm_doc = $uploadFile('ssm_doc') ?: '';
    $business_license = $uploadFile('business_license') ?: '';
    $ownership_proof = $uploadFile('ownership_proof') ?: '';

    // Save with Registered and Unpaid status
    $ins = $pdo->prepare("INSERT INTO homestays 
        (user_id, name, category, description, max_guests, price_per_night, pricing_type, total_rooms, state, district, address, maps_link, facilities, cover_image, main_image, facility_images, payment_status, listing_fee_status, approval_status, completion_score) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Unpaid', 'Unpaid', 'Registered', 85)");
    
    $ins->execute([
        $user_id, $name, $category, $description, $max_guests, $price, 
        $pricing_type, $total_rooms, $state, $district, $address, $maps_link, 
        $facilities_json, $cover_image_path, $cover_image_path, $facility_images_json
    ]);
    
    $homestay_id = $pdo->lastInsertId();

    // Save verification documents separately (columns may be newly added)
    if ($homestay_id) {
        $pdo->prepare("UPDATE homestays SET ic_copy=?, utility_bill=?, ssm_doc=?, business_license=?, ownership_proof=? WHERE id=?")
            ->execute([$ic_copy, $utility_bill, $ssm_doc, $business_license, $ownership_proof, $homestay_id]);
    }

    // Insert rooms inventory if "Per Room"
    if ($pricing_type === 'Per Room' && isset($_POST['room_names']) && isset($_POST['room_prices'])) {
        $room_names = $_POST['room_names'];
        $room_prices = $_POST['room_prices'];
        $room_types = isset($_POST['room_types']) ? $_POST['room_types'] : [];
        $room_guests = isset($_POST['room_guests']) ? $_POST['room_guests'] : [];
        
        $ins_room = $pdo->prepare("INSERT INTO homestay_rooms (homestay_id, room_name, price_modifier, room_type, price_per_night, max_guests, status) VALUES (?, ?, 0.00, ?, ?, ?, 'Available')");
        for ($i = 0; $i < count($room_names); $i++) {
            if (!empty(trim($room_names[$i]))) {
                $r_name = trim($room_names[$i]);
                $r_type = isset($room_types[$i]) ? $room_types[$i] : 'Double Room';
                $r_price = isset($room_prices[$i]) ? floatval($room_prices[$i]) : 150.00;
                $r_guests = isset($room_guests[$i]) ? intval($room_guests[$i]) : 2;
                
                $ins_room->execute([$homestay_id, $r_name, $r_type, $r_price, $r_guests]);
            }
        }
    }

    // Redirect to ToyyibPay billing process for RM29 fee
    header("Location: toyyibpay_process.php?homestay_id=" . $homestay_id);
    exit;
}

// Fetch all registered homestays for the current owner
$stmt = $pdo->prepare("SELECT * FROM homestays WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$my_listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate metrics
$count_total = count($my_listings);
$count_live = 0; $count_pending = 0; $count_registered = 0; $count_rejected = 0;
foreach ($my_listings as $l) {
    if ($l['approval_status'] === 'Published' || $l['approval_status'] === 'Live' || $l['approval_status'] === 'Approved') $count_live++;
    elseif ($l['approval_status'] === 'Pending Approval') $count_pending++;
    elseif ($l['approval_status'] === 'Registered') $count_registered++;
    elseif ($l['approval_status'] === 'Rejected') $count_rejected++;
}

// Fetch notifications for the owner
$notif_stmt = $pdo->prepare("SELECT * FROM owner_notifications WHERE owner_id = ? ORDER BY id DESC LIMIT 5");
$notif_stmt->execute([$user_id]);
$my_notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> Partner Dashboard - Host Centre</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/localhomestay.css?v=<?= time() ?>">
</head>
<body>

    <div class="moving-bg-overlay"></div>

    <div class="host-wrapper">
        
        <header class="main-portal-header">
            <div class="header-left">
                <a href="index.php?action=logout" class="btn-navigation-back"><i class="ri-arrow-left-s-line"></i> Exit Host Centre</a>
                <h2><span style="color: var(--gold-accent);">Good Day</span>, <?php echo htmlspecialchars($owner_name); ?> 👋</h2>
                <p>Welcome back to <span><?= htmlspecialchars($system_name) ?> Host Centre</span>.</p>
            </div>
            <div class="header-right">
                <button class="btn-trigger-wizard" id="btnLaunchWizard"><i class="ri-add-line"></i> Add New Homestay</button>
            </div>
        </header>

        <?php if (!empty($my_notifications)): ?>
            <div class="notifications-panel" style="background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.15); padding: 20px; border-radius: 16px; margin-bottom: 24px; border-left: 4px solid var(--gold-accent);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="color: var(--gold-accent); margin: 0; font-size: 1.05rem;"><i class="ri-notification-3-line"></i> Alerts & Moderation Log</h4>
                    <form method="POST" style="margin: 0;">
                        <button type="submit" name="clear_notifications" style="background: transparent; border: 1px solid rgba(255,255,255,0.3); color: white; padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; cursor: pointer; transition: 0.2s;">Mark all as read</button>
                    </form>
                </div>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($my_notifications as $notif): ?>
                        <li style="font-size: 0.88rem; color: rgba(255,255,255,0.9); padding: 8px 12px; background: rgba(0,0,0,0.15); border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                            <span><?php echo htmlspecialchars($notif['message']); ?></span>
                            <span style="font-size: 0.72rem; color: rgba(255,255,255,0.4);"><?php echo $notif['created_at']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="portal-timeline-card">
            <div class="timeline-step done"><i class="ri-checkbox-circle-fill"></i> <span>Registered</span></div>
            <div class="timeline-line active"></div>
            <div class="timeline-step done"><i class="ri-checkbox-circle-fill"></i> <span>Profile Completed</span></div>
            <div class="timeline-line active"></div>
            <div class="timeline-step <?php echo ($count_total > 0) ? 'done' : ''; ?>"><i class="ri-checkbox-circle-fill"></i> <span>Listing Submitted</span></div>
            <div class="timeline-line <?php echo ($count_pending > 0 || $count_live > 0) ? 'active' : ''; ?>"></div>
            <div class="timeline-step <?php echo ($count_pending > 0 || $count_live > 0) ? 'done' : ''; ?>"><i class="ri-checkbox-circle-fill"></i> <span>Payment Completed</span></div>
            <div class="timeline-line <?php echo ($count_live > 0) ? 'active' : ''; ?>"></div>
            <div class="timeline-step current"><i class="ri-time-line"></i> <span>Waiting Approval</span></div>
            <div class="timeline-line"></div>
            <div class="timeline-step"><i class="ri-global-line"></i> <span>Published</span></div>
        </div>

        <section class="statistics-grid">
            <div class="stat-box">
                <div class="stat-icon green"><i class="ri-home-4-line"></i></div>
                <div class="stat-info"><h4>Total Properties</h4><h3><?php echo $count_total; ?></h3></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon emerald"><i class="ri-checkbox-circle-line"></i></div>
                <div class="stat-info"><h4>Active Published</h4><h3><?php echo $count_live; ?></h3></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon gold"><i class="ri-time-line"></i></div>
                <div class="stat-info"><h4>Pending Review</h4><h3><?php echo $count_pending; ?></h3></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon red"><i class="ri-close-circle-line"></i></div>
                <div class="stat-info"><h4>Rejected listings</h4><h3><?php echo $count_rejected; ?></h3></div>
            </div>
        </section>

        <main class="content-showcase-section">
            <div class="section-title-bar">
                <h3><i class="ri-hotel-line"></i> My Registered Homestays</h3>
                <span class="count-pill"><?php echo $count_total; ?> Properties</span>
            </div>

            <?php if (empty($my_listings)): ?>
                <div class="blank-state-box">
                    <i class="ri-folder-open-line"></i>
                    <h4>No Homestay Listings Found</h4>
                    <p>Click "Add New Homestay" above to initialize your hosting business wizard.</p>
                </div>
            <?php else: ?>
                <div class="listings-flex-grid">
                    <?php foreach ($my_listings as $list): ?>
                        <div class="listing-premium-card">
                            <div class="card-image-placeholder" style="background-image: url('<?php echo !empty($list['cover_image']) ? $list['cover_image'] : 'images/default_place.jpg'; ?>'); background-size: cover; background-position: center; height: 160px; border-radius: 12px 12px 0 0; position: relative;">
                                <span class="score-tag">Score: <?php echo $list['completion_score']; ?>%</span>
                                <div class="status-badge-container">
                                    <?php if ($list['approval_status'] === 'Published' || $list['approval_status'] === 'Live'): ?>
                                        <span class="badge badge-live">🟢 Published</span>
                                    <?php elseif ($list['approval_status'] === 'Pending Approval'): ?>
                                        <span class="badge badge-pending">🟡 Pending Review</span>
                                    <?php elseif ($list['approval_status'] === 'Rejected'): ?>
                                        <span class="badge badge-rejected">🔴 Rejected</span>
                                    <?php else: ?>
                                        <span class="badge badge-draft">🔵 Registered</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body-details">
                                <span class="property-category"><?php echo htmlspecialchars($list['category']); ?> (<?php echo htmlspecialchars($list['pricing_type']); ?>)</span>
                                <h4><?php echo htmlspecialchars($list['name']); ?></h4>
                                <p class="location-text"><i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($list['district'] . ', ' . $list['state']); ?></p>
                                <div class="price-pax-row">
                                    <span><i class="ri-user-heart-line"></i> Max <?php echo $list['max_guests']; ?> Pax</span>
                                    <strong class="price-tag">RM <?php echo number_format($list['price_per_night'], 2); ?>/night</strong>
                                </div>
                                <div class="card-footer-buttons">
                                    <?php if ($list['payment_status'] === 'Unpaid'): ?>
                                        <a href="toyyibpay_process.php?homestay_id=<?php echo $list['id']; ?>" class="btn-pay-trigger" style="text-decoration: none; text-align: center; display: inline-block; width: 100%;">
                                            <i class="ri-wallet-3-line"></i> Pay RM29 Fee
                                        </a>
                                    <?php else: ?>
                                        <button class="btn-manage-disabled" disabled style="width: 100%;"><i class="ri-shield-user-line"></i> Activ. Fee Paid</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <section class="payment-history-card">
            <div class="table-header-title">
                <h3><i class="ri-currency-line"></i> ToyyibPay Payment History & Audit Records</h3>
                <p>Transaction history showing the RM29.00 Listing activation fees paid by your host profile.</p>
            </div>
            <div class="table-responsive">
                <table class="custom-premium-table">
                    <thead>
                        <tr>
                            <th>Invoice Number</th>
                            <th>Homestay Name</th>
                            <th>Listing Plan</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($my_listings)): ?>
                            <tr><td colspan="6" style="text-align:center; color: #a0aec0;">No billing records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($my_listings as $list): ?>
                                <tr>
                                    <td><strong>#VYG-2026-00<?php echo $list['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($list['name']); ?></td>
                                    <td>Listing Registration Fee</td>
                                    <td>RM 29.00</td>
                                    <td>
                                        <?php if ($list['payment_status'] === 'Paid'): ?>
                                            <span class="badge-table paid"><i class="ri-checkbox-circle-fill"></i> Paid</span>
                                        <?php else: ?>
                                            <span class="badge-table unpaid"><i class="ri-error-warning-fill"></i> Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($list['payment_status'] === 'Paid'): ?>
                                            <span class="badge-table" style="background:#0e3a20; color:white; padding: 4px 8px; border-radius:4px;"><i class="ri-printer-line"></i> Paid Receipt</span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="payment-history-card" style="margin-top: 24px;">
            <div class="table-header-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h3><i class="ri-bank-card-line"></i> Owner Bank Statement</h3>
                    <p>Every completed booking is recorded as a statement transaction, showing gross, commission, net payout and transfer status.</p>
                </div>
                <a href="export_settlement.php" style="background-color: #1f804f; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-family: 'DM Sans', sans-serif; font-size: 0.85rem;">
                    <i class="ri-file-excel-2-line"></i> Export Statement
                </a>
            </div>
            <div style="display:flex; gap:18px; flex-wrap:wrap; margin-bottom:18px;">
                <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; padding: 16px; min-width: 220px;">
                    <div style="font-size:0.8rem; color:rgba(255,255,255,0.6); margin-bottom:6px;">Pending Transfer</div>
                    <div style="font-size:1.4rem; font-weight:700; color:#f4cb66;">RM <?= number_format($statement_summary['pending_net'], 2) ?></div>
                    <div style="font-size:0.85rem; color:rgba(255,255,255,0.7);"><?= $statement_summary['pending_count'] ?> booking(s)</div>
                </div>
                <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; padding: 16px; min-width: 220px;">
                    <div style="font-size:0.8rem; color:rgba(255,255,255,0.6); margin-bottom:6px;">Transferred</div>
                    <div style="font-size:1.4rem; font-weight:700; color:#10b981;">RM <?= number_format($statement_summary['transferred_net'], 2) ?></div>
                    <div style="font-size:0.85rem; color:rgba(255,255,255,0.7);"><?= $statement_summary['transferred_count'] ?> booking(s)</div>
                </div>
                <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; padding: 16px; min-width: 220px;">
                    <div style="font-size:0.8rem; color:rgba(255,255,255,0.6); margin-bottom:6px;">Total Net Statement</div>
                    <div style="font-size:1.4rem; font-weight:700; color:#ffffff;">RM <?= number_format($statement_summary['total_net'], 2) ?></div>
                    <div style="font-size:0.85rem; color:rgba(255,255,255,0.7);">All completed bookings</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-premium-table" id="hostSettlementTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Booking No</th>
                            <th>Guest</th>
                            <th>Property / Room</th>
                            <th>Gross Amount</th>
                            <th>Commission 10%</th>
                            <th>Net Amount</th>
                            <th>Transfer Status</th>
                            <th>Transfer Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($statement_items)): ?>
                            <tr><td colspan="9" style="text-align:center; color: #a0aec0;">No completed statement transactions available yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($statement_items as $item): 
                                $gross = floatval($item['total_price']);
                                $commission = round($gross * 0.10, 2);
                                $net = round($gross - $commission, 2);
                                $status_label = empty($item['settlement_id']) ? 'Pending' : 'Transferred';
                                $transfer_date = $item['transfer_date'] ? date('Y-m-d H:i', strtotime($item['transfer_date'])) : '-';
                                $booking_no = $item['booking_no'] ?: 'BKG-' . str_pad($item['id'], 4, '0', STR_PAD_LEFT);
                                $property_label = htmlspecialchars($item['homestay_name']) . ($item['room_name'] ? ' / ' . htmlspecialchars($item['room_name']) : '');
                            ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($item['created_at'])) ?></td>
                                    <td><strong><?= htmlspecialchars($booking_no) ?></strong></td>
                                    <td><?= htmlspecialchars($item['guest_name']) ?></td>
                                    <td><?= $property_label ?></td>
                                    <td>RM <?= number_format($gross, 2) ?></td>
                                    <td>RM <?= number_format($commission, 2) ?></td>
                                    <td style="color:#f4cb66; font-weight:700;">RM <?= number_format($net, 2) ?></td>
                                    <td>
                                        <span class="badge-table <?= $status_label === 'Transferred' ? 'paid' : 'unpaid' ?>" style="background: <?= $status_label === 'Transferred' ? 'rgba(16,185,129,0.15)' : 'rgba(249,115,22,0.15)' ?>; color: <?= $status_label === 'Transferred' ? '#10b981' : '#f97316' ?>; border:1px solid <?= $status_label === 'Transferred' ? 'rgba(16,185,129,0.3)' : 'rgba(249,115,22,0.3)' ?>; padding:6px 10px; border-radius:20px; font-size:0.8rem; font-weight:700;">
                                            <?= $status_label ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($transfer_date) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- New Guest Feedback / User Reports Section -->
        <section class="payment-history-card" style="margin-top: 24px; margin-bottom: 24px;">
            <div class="table-header-title">
                <h3><i class="ri-message-3-line"></i> User Feedback Reports</h3>
                <p>Read the latest reviews and reports submitted by your guests.</p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; margin-top: 15px;">
                <?php if (empty($latest_reviews)): ?>
                    <div style="padding: 20px; color: rgba(255,255,255,0.5); text-align: center; border: 1px dashed rgba(255,255,255,0.2); border-radius: 12px; grid-column: 1 / -1;">
                        No user reviews or reports recorded yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($latest_reviews as $rev): ?>
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 15px; display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: #f4cb66;"><?php echo htmlspecialchars($rev['guest_name']); ?></strong>
                                <span style="font-size: 0.8rem; color: rgba(255,255,255,0.5);"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                            </div>
                            <div style="font-size: 0.85rem; color: #10b981;">
                                <?php echo htmlspecialchars($rev['stay_name']); ?>
                            </div>
                            <div style="color: #f4cb66;">
                                <?php echo str_repeat('⭐', $rev['rating']); ?>
                            </div>
                            <p style="font-size: 0.9rem; color: rgba(255,255,255,0.9); line-height: 1.4; margin: 0;">
                                "<?php echo htmlspecialchars($rev['feedback_text'] ?: 'No text provided.'); ?>"
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; flex-wrap: wrap; margin-top: 24px;">
            
            <div class="calendar-card" id="calendar-section" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 20px; color: white;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                    <h4 style="color: #f4cb66; font-size: 1rem; display: flex; align-items: center; gap: 6px; margin:0;"><i class="ri-calendar-2-line"></i> Availability Calendar (<?php echo date('F Y'); ?>)</h4>
                    <?php if (count($owner_stays) > 1): ?>
                    <select onchange="location='localhomestay.php?cal_stay='+this.value+'#calendar-section'" style="background:rgba(0,0,0,0.3); color:white; border:1px solid rgba(255,255,255,0.2); border-radius:8px; padding:5px 10px; font-size:0.8rem; cursor:pointer;">
                        <?php foreach ($owner_stays as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($s['id'] == $calendar_stay_id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>
                <p style="font-size:0.78rem; color:rgba(255,255,255,0.55); margin:0 0 12px 0;"><i class="ri-information-line"></i> Click any <strong style="color:#10b981;">available</strong> date to block it. Click an <strong style="color:#f97316;">orange</strong> date to unblock it. Red dates are guest bookings and cannot be changed.</p>
                
                <?php
                $month_days = date('t');
                $start_day_of_week = date('N', strtotime(date('Y-m-01')));
                ?>
                <form method="POST" id="blockDateForm">
                    <input type="hidden" name="action_toggle_block_date" value="1">
                    <input type="hidden" name="block_date" id="blockDateInput" value="">
                    <input type="hidden" name="block_homestay_id" value="<?php echo $calendar_stay_id; ?>">
                </form>

                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; font-size: 0.8rem; font-weight: 700; margin-bottom: 10px;">
                    <div style="color: #f4cb66;">M</div>
                    <div style="color: #f4cb66;">T</div>
                    <div style="color: #f4cb66;">W</div>
                    <div style="color: #f4cb66;">T</div>
                    <div style="color: #f4cb66;">F</div>
                    <div style="color: #f4cb66;">S</div>
                    <div style="color: #f4cb66;">S</div>
                    
                    <?php 
                    for ($i = 1; $i < $start_day_of_week; $i++) {
                        echo "<div></div>";
                    }
                    $today = date('Y-m-d');
                    for ($day = 1; $day <= $month_days; $day++) {
                        $current_date = date('Y-m-') . sprintf('%02d', $day);
                        $is_booked  = in_array($current_date, $booked_dates);
                        $is_blocked = in_array($current_date, $blocked_dates);
                        $is_past    = $current_date < $today;

                        if ($is_booked) {
                            // Guest booking — red, no click
                            echo "<div class='calendar-day cal-booked' title='Guest booking: $current_date' style='padding:8px; border-radius:6px; font-weight:bold; background:rgba(239,68,68,0.25); border:1px solid #ef4444; color:white; cursor:default;'>$day</div>";
                        } elseif ($is_past) {
                            // Past date — greyed out, no action
                            echo "<div class='calendar-day cal-past' title='Past date' style='padding:8px; border-radius:6px; font-weight:bold; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); color:rgba(255,255,255,0.25); cursor:default;'>$day</div>";
                        } elseif ($is_blocked) {
                            // Owner-blocked — orange, clickable to unblock
                            echo "<div class='calendar-day cal-blocked' title='Click to unblock $current_date' onclick=\"toggleDate('$current_date')\" style='padding:8px; border-radius:6px; font-weight:bold; background:rgba(249,115,22,0.3); border:1px solid #f97316; color:white; cursor:pointer; transition:transform 0.15s;' onmouseover=\"this.style.transform='scale(1.1)'\" onmouseout=\"this.style.transform=''\">$day</div>";
                        } else {
                            // Available — green, clickable to block
                            echo "<div class='calendar-day cal-available' title='Click to block $current_date' onclick=\"toggleDate('$current_date')\" style='padding:8px; border-radius:6px; font-weight:bold; background:rgba(16,185,129,0.2); border:1px solid #10b981; color:white; cursor:pointer; transition:transform 0.15s;' onmouseover=\"this.style.transform='scale(1.1)'\" onmouseout=\"this.style.transform=''\">$day</div>";
                        }
                    }
                    ?>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 14px; font-size: 0.78rem; flex-wrap:wrap;">
                    <span style="display:flex; align-items:center; gap:4px;"><span style="width:11px;height:11px;border-radius:3px;background:rgba(239,68,68,0.5);border:1px solid #ef4444;display:inline-block;"></span> Guest Booked</span>
                    <span style="display:flex; align-items:center; gap:4px;"><span style="width:11px;height:11px;border-radius:3px;background:rgba(249,115,22,0.5);border:1px solid #f97316;display:inline-block;"></span> Blocked by You</span>
                    <span style="display:flex; align-items:center; gap:4px;"><span style="width:11px;height:11px;border-radius:3px;background:rgba(16,185,129,0.5);border:1px solid #10b981;display:inline-block;"></span> Available</span>
                    <span style="display:flex; align-items:center; gap:4px;"><span style="width:11px;height:11px;border-radius:3px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);display:inline-block;"></span> Past</span>
                </div>
            </div>

            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 20px; color: white;">
                <h4 style="color: #f4cb66; margin-bottom: 12px; font-size: 1rem; display: flex; align-items: center; gap: 6px;"><i class="ri-file-chart-line"></i> Monthly Performance Audits</h4>
                
                <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                    <table class="custom-premium-table" style="width: 100%; font-size: 0.82rem;">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Sales Volume</th>
                                <th>Net Earnings (90%)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($owner_reports_list)): ?>
                                <tr><td colspan="4" style="text-align:center; color:rgba(255,255,255,0.4); font-style:italic;">No performance reports published yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($owner_reports_list as $rep): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($rep['report_month']); ?></strong></td>
                                        <td>RM <?php echo number_format($rep['total_revenue'], 2); ?></td>
                                        <td style="color:#10b981; font-weight:700;">RM <?php echo number_format($rep['earnings'], 2); ?></td>
                                        <td style="white-space:nowrap;">
                                            <a href="view_report.php?id=<?php echo $rep['id']; ?>" target="_blank" class="btn-pay-trigger" style="padding: 4px 8px; font-size: 0.72rem; text-decoration:none; margin-right:4px;"><i class="ri-eye-line"></i> View</a>
                                            <a href="view_report.php?id=<?php echo $rep['id']; ?>&print=1" target="_blank" class="btn-pay-trigger" style="padding: 4px 8px; font-size: 0.72rem; text-decoration:none; background:rgba(16,185,129,0.2); border-color:rgba(16,185,129,0.5);"><i class="ri-download-line"></i> Download</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <section class="payment-history-card" style="margin-bottom: 24px;">
            <div class="table-header-title">
                <h3><i class="ri-book-open-line"></i> Guest Bookings & Reservation Ledgers</h3>
                <p>Monitor your active guest check-in lists, room selection variants, and automated check-in statuses.</p>
            </div>
            
            <div class="table-responsive">
                <table class="custom-premium-table">
                    <thead>
                        <tr>
                            <th>Booking No</th>
                            <th>Guest Name</th>
                            <th>Property / Room</th>
                            <th>Check-in / Check-out</th>
                            <th>Guests</th>
                            <th>Booking Status</th>
                            <th>Payment Status</th>
                            <th>Review Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($host_bookings)): ?>
                            <tr><td colspan="8" style="text-align:center; color:rgba(255,255,255,0.4);">No stay reservations recorded for your properties yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($host_bookings as $bkg): 
                                $today = date('Y-m-d');
                                $b_status = $bkg['booking_status'];
                                $p_status = $bkg['payment_status'];
                                
                                if ($p_status !== 'Paid') {
                                    $p_status = 'Awaiting Payment';
                                    $b_status = 'Pending';
                                } else {
                                    if ($today < $bkg['check_in']) {
                                        $b_status = 'Upcoming';
                                    } elseif ($today >= $bkg['check_in'] && $today <= $bkg['check_out']) {
                                        $b_status = 'Current Stay'; // Checked In
                                    } else {
                                        $b_status = 'Completed';
                                    }
                                }
                                
                                // Fetch review if exists
                                $rev_check = $pdo->prepare("SELECT rating FROM homestay_reviews WHERE booking_id = ? AND is_hidden = 0");
                                $rev_check->execute([$bkg['id']]);
                                $rev_rating = $rev_check->fetchColumn();
                            ?>
                                <tr>
                                    <td><strong>#<?php echo $bkg['booking_no'] ?: 'VYG-BKG-'.$bkg['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($bkg['guest_name']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($bkg['stay_name']); ?></strong>
                                        <?php if ($bkg['room_name']): ?>
                                            <br><small style="color:rgba(255,255,255,0.6);">Room: <?php echo htmlspecialchars($bkg['room_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $bkg['check_in']; ?> to <?php echo $bkg['check_out']; ?></td>
                                    <td><?php echo $bkg['guests']; ?> Pax</td>
                                    <td>
                                        <span class="badge-table <?php echo $b_status === 'Completed' ? 'paid' : ($b_status === 'Current Stay' ? 'pending' : ''); ?>" style="background: <?php echo $b_status === 'Completed' ? 'rgba(16,185,129,0.2)' : ($b_status === 'Current Stay' ? 'rgba(244,203,102,0.2)' : 'rgba(255,255,255,0.1)'); ?>; color: <?php echo $b_status === 'Completed' ? '#10b981' : ($b_status === 'Current Stay' ? '#f4cb66' : 'white'); ?>;">
                                            <?php echo htmlspecialchars($b_status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-table <?php echo $p_status === 'Paid' ? 'paid' : 'unpaid'; ?>">
                                            <?php echo htmlspecialchars($p_status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($rev_rating): ?>
                                            <span style="color:#f4cb66; font-weight:700;"><?php echo str_repeat('⭐', $rev_rating); ?></span>
                                        <?php else: ?>
                                            <span style="color:rgba(255,255,255,0.4); font-size:0.8rem; font-style:italic;">No Review</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="payment-history-card" style="margin-bottom: 24px;">
            <div class="table-header-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3><i class="ri-star-line"></i> Customer Feedback & Reviews Summary</h3>
                    <p>Recent traveler ratings and comment reviews left for your active properties.</p>
                </div>
                <div style="text-align: right; background: rgba(14,58,32,0.6); padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                    <span style="font-size: 0.8rem; text-transform: uppercase; color: #f4cb66; font-weight:700; display:block;">Overall rating</span>
                    <strong style="font-size: 1.4rem; color: #ffffff;">⭐ <?php echo number_format($avg_rating_all, 1); ?> <span style="font-size:0.9rem; color:rgba(255,255,255,0.5);">/ 5</span></strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                <?php if (empty($latest_reviews)): ?>
                    <p style="color:rgba(255,255,255,0.4); font-style:italic; grid-column: 1/-1; text-align:center; padding: 20px;">No traveler feedback reviews posted yet.</p>
                <?php else: ?>
                    <?php foreach ($latest_reviews as $rev): ?>
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-weight:700; font-size: 0.88rem; color:#f4cb66;"><?php echo htmlspecialchars($rev['guest_name']); ?></span>
                                <span style="font-size:0.75rem; color:rgba(255,255,255,0.5);"><?php echo str_repeat('⭐', $rev['rating']); ?></span>
                            </div>
                            <small style="color:rgba(255,255,255,0.6); display:block; margin-bottom: 8px;">Stay at: <?php echo htmlspecialchars($rev['stay_name']); ?></small>
                            <p style="margin: 0; font-style: italic; font-size: 0.85rem; line-height: 1.5; color: rgba(255,255,255,0.95);">"<?php echo htmlspecialchars($rev['feedback_text']); ?>"</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <div class="wizard-modal-overlay" id="wizardOverlay">
        <div class="wizard-box-card">
            <div class="wizard-box-header">
                <h3><i class="ri-magic-line"></i> Create New Homestay Listing</h3>
                <button class="btn-close-modal" id="btnCloseWizard">×</button>
            </div>
            <div class="wizard-progress-track">
                <div class="wizard-progress-bar" id="wizardBar"></div>
            </div>

            <form action="localhomestay.php" method="POST" id="wizardMainForm" enctype="multipart/form-data">
                <input type="hidden" name="action_create_homestay" value="1">

                <div class="wizard-step-pane active" data-step="1">
                    <h4>Step 1: Basic Information</h4>
                    <div class="field-group">
                        <label>Homestay Name *</label>
                        <input type="text" name="h_name" placeholder="E.g. Sea View Villa Terengganu" required>
                    </div>
                    <div class="field-group">
                        <label>Category *</label>
                        <select name="homestay_category" id="homestay_category">
                            <option value="Chalet">Chalet</option>
                            <option value="Apartment">Apartment</option>
                            <option value="Villa">Villa</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label>Pricing Type *</label>
                        <div style="display: flex; gap: 20px; margin-top: 5px;">
                            <label style="display:flex; align-items:center; gap:6px; color: white;"><input type="radio" name="h_pricing_type" value="Whole House" checked id="pricing_type_whole"> Whole House</label>
                            <label style="display:flex; align-items:center; gap:6px; color: white;"><input type="radio" name="h_pricing_type" value="Per Room" id="pricing_type_room"> Per Room</label>
                        </div>
                    </div>

                    <div class="field-group" id="total_rooms_wrapper" style="display: none; background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); margin-top: 10px;">
                        <label style="color: var(--gold-accent);">Total Rooms in Homestay *</label>
                        <input type="number" name="h_total_rooms" id="h_total_rooms" value="1" min="1" class="sidebar-input" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 8px; border-radius: 6px; width: 100%;">
                        
                        <div id="room_variants_container" style="margin-top: 12px;">
                            <label style="font-size: 0.78rem; color: #ffffff; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Configure Room Names & Modifiers</label>
                            <div id="room_inputs_list" style="display:flex; flex-direction:column; gap:8px; margin-top: 8px;">
                                </div>
                        </div>
                    </div>

                    <div class="field-group" style="margin-top:15px;">
                        <label>Description *</label>
                        <textarea name="h_description" rows="3" placeholder="Describe the layout, rooms, and surroundings..." required></textarea>
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label>Max Guests *</label>
                            <input type="number" name="h_max_guests" value="4" min="1" required>
                        </div>
                        <div class="field-group">
                            <label>Price Per Night (RM) *</label>
                            <input type="number" name="h_price" value="150" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="wizard-step-pane" data-step="2">
                    <h4>Step 2: Location Details</h4>
                    <div class="field-row">
                        <div class="field-group">
                            <label>State *</label>
                            <select name="h_state" required style="width:100%; padding:10px; border-radius:8px; background: rgba(0,0,0,0.5); color:white; border:1px solid rgba(255,255,255,0.2);">
                                <option value="Terengganu">Terengganu</option>
                                <option value="Perak">Perak</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Pulau Pinang">Pulau Pinang</option>
                                <option value="Pahang">Pahang</option>
                                <option value="Johor">Johor</option>
                                <option value="Sabah">Sabah</option>
                                <option value="Sarawak">Sarawak</option>
                                <option value="Kedah">Kedah</option>
                                <option value="Kelantan">Kelantan</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label>District *</label>
                            <input type="text" name="h_district" placeholder="E.g. Marang" required>
                        </div>
                    </div>
                    <div class="field-group">
                        <label>Full Address *</label>
                        <textarea name="h_address" rows="2" placeholder="Street, village name, and postcode details..." required></textarea>
                    </div>
                    <div class="field-group">
                        <label>Google Maps Link</label>
                        <input type="url" name="h_maps" placeholder="http://maps.google.com/...">
                    </div>
                </div>

                <div class="wizard-step-pane" data-step="3">
                    <h4>Step 3: Select Facilities *</h4>
                    <p style="font-size: 0.8rem; color: #cbd5e1; margin-bottom: 12px;">Choose at least 3 facilities to display on the booking directory listing page:</p>
                    <div class="checkbox-grid-selection">
                        <label style="color:white; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="facilities[]" value="WiFi"> WiFi Internet</label>
                        <label style="color:white; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="facilities[]" value="Swimming Pool"> Swimming Pool</label>
                        <label style="color:white; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="facilities[]" value="Kitchen"> Kitchen / Cooking</label>
                        <label style="color:white; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="facilities[]" value="Air Conditioner"> Air Conditioner</label>
                        <label style="color:white; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="facilities[]" value="TV"> Smart TV</label>
                        <label style="color:white; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="facilities[]" value="BBQ"> BBQ Pit / Area</label>
                        <label style="color:white; display:flex; align-items:center; gap:8px;"><input type="checkbox" name="facilities[]" value="Parking"> Free Parking</label>
                    </div>
                </div>

                <div class="wizard-step-pane" data-step="4">
                    <h4>Step 4: Upload Cover & Facility Images</h4>
                    <div class="field-group">
                        <label>Main Cover Image * (1 photo)</label>
                        <input type="file" name="cover_image" accept="image/*" required style="width: 100%; padding: 12px; border: 2px dashed rgba(255,255,255,0.2); border-radius: 8px; background: rgba(0,0,0,0.2); cursor: pointer; color: white;">
                    </div>
                    <div class="field-group" style="margin-top: 15px;">
                        <label>Facility Images * (Select at least 3 photos)</label>
                        <input type="file" name="facility_images[]" accept="image/*" multiple required style="width: 100%; padding: 12px; border: 2px dashed rgba(255,255,255,0.2); border-radius: 8px; background: rgba(0,0,0,0.2); cursor: pointer; color: white;">
                    </div>
                </div>

                <div class="wizard-step-pane" data-step="5">
                    <h4>Step 5: Host Contact Details</h4>
                    <div class="field-group">
                        <label>Registered Host Owner</label>
                        <input type="text" value="<?php echo htmlspecialchars($owner_name); ?>" readonly style="background: rgba(0,0,0,0.3); color: #a0aec0;">
                    </div>
                    <div class="field-group">
                        <label>Emergency Host Phone Number *</label>
                        <input type="text" name="h_phone" placeholder="E.g. 0134567890" required>
                    </div>
                </div>

                <div class="wizard-step-pane" data-step="6">
                    <h4>Step 6: Moderation Verification Documents</h4>
                    <div class="field-group">
                        <label>Owner Identity Card (IC / Passport copy) *</label>
                        <input type="file" name="ic_copy" accept=".pdf,image/*" required>
                    </div>
                    <div class="field-group">
                        <label>Utility Bill (Electricity/Water for address proof) *</label>
                        <input type="file" name="utility_bill" accept=".pdf,image/*" required>
                    </div>
                    <div class="field-group">
                        <label>SSM Company Registration Doc *</label>
                        <input type="file" name="ssm_doc" accept=".pdf,image/*" required>
                    </div>
                    <div class="field-group">
                        <label>Local Authority Business License *</label>
                        <input type="file" name="business_license" accept=".pdf,image/*" required>
                    </div>
                    <div class="field-group">
                        <label>Proof of Property Ownership *</label>
                        <input type="file" name="ownership_proof" accept=".pdf,image/*" required>
                    </div>
                </div>

                <div class="wizard-step-pane" data-step="7">
                    <h4>Step 7: Final Confirmation</h4>
                    <div class="review-summary-notice">
                        <h5><i class="ri-information-line"></i> Annual Listing Activation Fee Alert</h5>
                        <p>Completing this listing requires a processing fee payment of **RM 29.00**. You will be redirected directly to ToyyibPay FPX gateway. Once payment is confirmed, the listing will transition status: Registered → Paid → Pending Admin Moderation.</p>
                    </div>
                </div>

                <div class="wizard-card-footer">
                    <button type="button" class="btn-step-nav btn-secondary hidden" id="btnWizardPrev">Back</button>
                    <button type="button" class="btn-step-nav btn-primary" id="btnWizardNext">Next</button>
                    <button type="submit" class="btn-step-nav btn-success hidden" id="btnWizardSubmit">Proceed to Payment</button>
                </div>
            </form>
        </div>
    </div>

    <div class="toyyibpay-mock-overlay" id="toyyibpayOverlay">
        <div class="toyyibpay-gateway-box">
            <div class="tp-header-branding">
                <span class="tp-logo">toyyib<span>Pay</span></span>
                <span class="tp-secure-badge"><i class="ri-lock-fill"></i> FPX Secure Payment</span>
            </div>
            
            <div class="tp-order-summary">
                <div class="tp-row"><span>Merchant:</span><strong><?= htmlspecialchars($system_name) ?> Enterprise</strong></div>
                <div class="tp-row"><span>Bill Reference:</span><strong id="tpRefId">#VYG-000</strong></div>
                <div class="tp-row"><span>Listing For:</span><span id="tpPropName" style="font-size:0.85rem; color:#4a5568; font-weight:bold;">Property Name</span></div>
                <div class="tp-amount-block">RM 29.00</div>
            </div>

            <div class="tp-bank-selection-container">
                <p>Select Online Banking Portal:</p>
                <div class="tp-bank-grid">
                    <div class="bank-card active" onclick="selectMockBank(this)"><span class="bank-dot"></span> Maybank2u (M2U)</div>
                    <div class="bank-card" onclick="selectMockBank(this)"><span class="bank-dot"></span> CIMB Clicks</div>
                    <div class="bank-card" onclick="selectMockBank(this)"><span class="bank-dot"></span> Bank Islam</div>
                    <div class="bank-card" onclick="selectMockBank(this)"><span class="bank-dot"></span> RHB Now</div>
                </div>
            </div>

            <div class="tp-footer-actions">
                <button type="button" class="btn-tp-cancel" onclick="closeMockToyyibPay()">Cancel</button>
                <button type="button" class="btn-tp-confirm" id="btnConfirmMockPayment">Authorize Payment</button>
            </div>
        </div>
    </div>

    <script src="js/localhomestay.js"></script>
    <script>
        <?php if(isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
            Swal.fire('Form Saved!', 'Your homestay data has been logged into our servers.', 'success');
        <?php endif; ?>
        
        <?php if(isset($_GET['payment']) && $_GET['payment'] === 'success'): ?>
            Swal.fire('Listing Activation Success!', 'RM29 processing payment completed. Your listing is now under verification review.', 'success');
        <?php endif; ?>
        
        <?php if(isset($_GET['payment']) && $_GET['payment'] === 'failed'): ?>
            Swal.fire('Payment Failed', 'Processing listing transaction was aborted. Please check online banking status and retry.', 'error');
        <?php endif; ?>
    </script>

    <script>
    function exportHostSettlementsToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
        
        filename = filename ? filename + '.xls' : 'excel_data.xls';
        downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        
        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff' + tableHTML], { type: dataType });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            downloadLink.href = 'data:' + dataType + ', ' + '\ufeff' + tableHTML;
            downloadLink.download = filename;
            downloadLink.click();
        }
    }
    </script>    <script>
    function toggleDate(dateStr) {
        if (!dateStr) return;
        document.getElementById('blockDateInput').value = dateStr;
        document.getElementById('blockDateForm').submit();
    }
    </script>

</body>
</html>
