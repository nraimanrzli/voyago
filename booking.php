<?php
// booking.php - Traveller Marketplace Interface
require_once('toyyibpay_config.php');

// Verify session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Handle Submit Review if posted to booking.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_submit_review'])) {
    $booking_id = intval($_POST['booking_id']);
    $homestay_id = intval($_POST['homestay_id']);
    $rating = intval($_POST['rating']);
    $feedback = trim($_POST['feedback_text']);
    
    if ($rating >= 1 && $rating <= 5) {
        $chk_stmt = $pdo->prepare("SELECT check_out FROM bookings WHERE id = ?");
        $chk_stmt->execute([$booking_id]);
        $chk_date = $chk_stmt->fetchColumn();
        
        if ($chk_date && $chk_date <= date('Y-m-d')) {
            // Check if reviewed already
            $r_chk = $pdo->prepare("SELECT id FROM homestay_reviews WHERE booking_id = ?");
            $r_chk->execute([$booking_id]);
            if (!$r_chk->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO homestay_reviews (booking_id, homestay_id, user_id, rating, feedback_text) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$booking_id, $homestay_id, $user_id, $rating, $feedback]);
                
                // Update average rating
                $avg_stmt = $pdo->prepare("SELECT AVG(rating) FROM homestay_reviews WHERE homestay_id = ?");
                $avg_stmt->execute([$homestay_id]);
                $new_avg = $avg_stmt->fetchColumn() ?: 4.5;
                
                $upd_h = $pdo->prepare("UPDATE homestays SET rating = ? WHERE id = ?");
                $upd_h->execute([$new_avg, $homestay_id]);
                
                // Notify host
                $h_stmt = $pdo->prepare("SELECT user_id, name FROM homestays WHERE id = ?");
                $h_stmt->execute([$homestay_id]);
                $h_data = $h_stmt->fetch(PDO::FETCH_ASSOC);
                if ($h_data) {
                    $owner_id = intval($h_data['user_id']);
                    $msg = "A new traveler review has been posted for '" . $h_data['name'] . "' (Rating: " . $rating . "/5 Stars).";
                    $n_stmt = $pdo->prepare("INSERT INTO owner_notifications (owner_id, message) VALUES (?, ?)");
                    $n_stmt->execute([$owner_id, $msg]);
                }
            }
            
            header("Location: booking.php?review=success");
            exit;
        }
    }
}

// Fetch traveler bookings for history
$stmt_travel = $pdo->prepare("SELECT b.*, h.name as stay_name, h.price_per_night, r.room_name, u.fullname as owner_name 
                              FROM bookings b 
                              JOIN homestays h ON b.homestay_id = h.id 
                              JOIN users u ON h.user_id = u.id
                              LEFT JOIN homestay_rooms r ON b.room_id = r.id
                              WHERE b.user_id = ? 
                              ORDER BY b.id DESC");
$stmt_travel->execute([$user_id]);
$traveler_bookings = $stmt_travel->fetchAll(PDO::FETCH_ASSOC);

// --- AJAX AVAILABILITY CHECK & OVERLAP MATRIX ENDPOINT ---
if (isset($_GET['action_check_availability'])) {
    header('Content-Type: application/json');
    $homestay_id = intval($_GET['homestay_id']);
    $in = isset($_GET['check_in']) ? trim($_GET['check_in']) : '';
    $out = isset($_GET['check_out']) ? trim($_GET['check_out']) : '';
    
    if (empty($in) || empty($out)) {
        echo json_encode(['status' => 'error', 'message' => 'Please select check-in and check-out dates.']);
        exit;
    }
    
    // Fetch homestay information
    $h_stmt = $pdo->prepare("SELECT pricing_type, price_per_night FROM homestays WHERE id = ?");
    $h_stmt->execute([$homestay_id]);
    $h_data = $h_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$h_data) {
        echo json_encode(['status' => 'error', 'message' => 'Homestay not found.']);
        exit;
    }
    
    if ($h_data['pricing_type'] === 'Whole House') {
        // Check guest-booking overlap
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                               WHERE homestay_id = ? 
                               AND booking_status != 'Cancelled' 
                               AND check_in < ? 
                               AND check_out > ?");
        $stmt->execute([$homestay_id, $out, $in]);
        $booked = $stmt->fetchColumn() > 0;

        // Check owner-blocked date overlap
        $blk_stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM homestay_blocked_dates 
             WHERE homestay_id = ? AND blocked_date >= ? AND blocked_date < ?"
        );
        $blk_stmt->execute([$homestay_id, $in, $out]);
        $has_blocked = $blk_stmt->fetchColumn() > 0;
        
        echo json_encode([
            'pricing_type' => 'Whole House',
            'available'    => !$booked && !$has_blocked,
            'blocked'      => $has_blocked,
            'price'        => floatval($h_data['price_per_night'])
        ]);
        exit;
    } else {
        // Per Room setup: Check room availability inventory and whole-house booking conflicts
        $r_stmt = $pdo->prepare("SELECT * FROM homestay_rooms WHERE homestay_id = ?");
        $r_stmt->execute([$homestay_id]);
        $rooms = $r_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Find which rooms are booked during this date range, including any whole-house booking
        $b_stmt = $pdo->prepare("SELECT room_id FROM bookings 
                                 WHERE homestay_id = ? 
                                 AND booking_status != 'Cancelled' 
                                 AND check_in < ? 
                                 AND check_out > ?");
        $b_stmt->execute([$homestay_id, $out, $in]);
        $booked_room_ids = $b_stmt->fetchAll(PDO::FETCH_COLUMN);
        $has_whole_house_booking = in_array(null, $booked_room_ids, true);
        
        $vacant_rooms = [];
        if (!$has_whole_house_booking) {
            foreach ($rooms as $room) {
                if (!in_array($room['id'], $booked_room_ids, true)) {
                    $r_price = isset($room['price_per_night']) ? floatval($room['price_per_night']) : 0.00;
                    if ($r_price <= 0.00) {
                        $r_price = floatval($h_data['price_per_night']) + (isset($room['price_modifier']) ? floatval($room['price_modifier']) : 0.00);
                    }
                    $vacant_rooms[] = [
                        'id' => $room['id'],
                        'name' => $room['room_name'],
                        'price' => $r_price
                    ];
                }
            }
        }
        
        // Check owner-blocked date overlap for Per Room
        $blk_stmt2 = $pdo->prepare(
            "SELECT COUNT(*) FROM homestay_blocked_dates 
             WHERE homestay_id = ? AND blocked_date >= ? AND blocked_date < ?"
        );
        $blk_stmt2->execute([$homestay_id, $in, $out]);
        $has_blocked2 = $blk_stmt2->fetchColumn() > 0;

        echo json_encode([
            'pricing_type' => 'Per Room',
            'available'    => count($vacant_rooms) > 0 && !$has_blocked2,
            'blocked'      => $has_blocked2,
            'rooms'        => $vacant_rooms,
            'reason'       => $has_whole_house_booking ? 'whole_house_blocked' : ''
        ]);
        exit;
    }
}

// Inherit state from URL or session (from smart-planner.php)
$search_state = isset($_GET['state']) ? trim($_GET['state']) : (isset($_SESSION['selected_state']) ? $_SESSION['selected_state'] : '');
if (isset($_GET['state']) && !empty($_GET['state'])) {
    $_SESSION['selected_state'] = $_GET['state'];
}

$check_in = isset($_GET['check_in']) ? trim($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? trim($_GET['check_out']) : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$pricing_type = isset($_GET['pricing_type']) ? trim($_GET['pricing_type']) : '';
$selected_facilities = isset($_GET['facilities']) && is_array($_GET['facilities']) ? $_GET['facilities'] : [];

// Query published properties only
$query = "SELECT h.*, u.fullname as owner_name 
          FROM homestays h 
          JOIN users u ON h.user_id = u.id 
          WHERE (h.approval_status = 'Published' OR h.approval_status = 'Live' OR h.approval_status = 'Approved')";

$params = [];

if (!empty($search_state)) {
    $query .= " AND (h.state LIKE ? OR h.district LIKE ? OR h.name LIKE ?)";
    $like_search = "%$search_state%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
}

if (!empty($check_in) && !empty($check_out)) {
    // Basic pre-filter check: homestays with conflicting whole-house or full-property booked dates
    $query .= " AND NOT EXISTS (
                    SELECT 1 FROM bookings b 
                    WHERE b.homestay_id = h.id
                      AND b.booking_status != 'Cancelled'
                      AND b.check_in < ? 
                      AND b.check_out > ?
                      AND (h.pricing_type = 'Whole House' OR b.room_id IS NULL)
                )";
    $params[] = $check_out;
    $params[] = $check_in;
}

if (!empty($guests) && $guests > 1) {
    $query .= " AND h.max_guests >= ?";
    $params[] = $guests;
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $query .= " AND h.category = ?";
    $params[] = $_GET['category'];
}

if ($rating > 0) {
    $query .= " AND h.rating >= ?";
    $params[] = $rating;
}

if (!empty($pricing_type) && in_array($pricing_type, ['Whole House', 'Per Room'])) {
    $query .= " AND h.pricing_type = ?";
    $params[] = $pricing_type;
}

foreach ($selected_facilities as $facility) {
    $facility = trim($facility);
    if ($facility !== '') {
        $query .= " AND h.facilities LIKE ?";
        $params[] = "%$facility%";
    }
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $query .= " AND h.price_per_night <= ?";
    $params[] = floatval($_GET['max_price']);
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'popular';
if ($sort == 'price_low') {
    $query .= " ORDER BY h.price_per_night ASC";
} elseif ($sort == 'price_high') {
    $query .= " ORDER BY h.price_per_night DESC";
} elseif ($sort == 'rating_high') {
    $query .= " ORDER BY h.rating DESC";
} elseif ($sort == 'newest') {
    $query .= " ORDER BY h.id DESC";
} else {
    $query .= " ORDER BY h.id DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$homestays = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> - Premium Stays</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/booking.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.65), rgba(15, 23, 42, 0.65)), url('images/Background.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .booking-layout, .hero-section {
            background: transparent;
        }
        .filter-sidebar {
            background: rgba(14, 58, 32, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            color: white;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .filter-sidebar h3 {
            color: #f4cb66;
        }
        .property-card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            color: white;
        }
        .card-details .property-title {
            color: white;
        }
        .card-details .property-location {
            color: #f4cb66;
        }
        .card-details .price-amount {
            color: #f4cb66;
        }
        .view-details-btn {
            background: #f4cb66;
            color: #0e3a20;
            font-weight: 700;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }
        .view-details-btn:hover {
            background: #ffe082;
        }
        .modal-content {
            background: rgba(14, 58, 32, 0.98);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.15);
            color: white;
            border-radius: 24px;
        }
        .modal-info-pane h2 {
            color: #f4cb66;
        }
        .pricing-card-widget {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 20px;
        }
        .widget-field input, .widget-field select {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
        }
        .confirm-payment-btn {
            background: #f4cb66;
            color: #0e3a20;
            font-weight: 800;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: 0.2s;
            margin-top: 15px;
        }
        .confirm-payment-btn:hover {
            background: #ffe082;
        }
        .badge-status.upcoming { background: #dbeafe !important; color: #1e40af !important; }
        .badge-status.checked-in { background: #fef3c7 !important; color: #92400e !important; }
        .badge-status.completed { background: #d1fae5 !important; color: #065f46 !important; }
        .badge-status.reviewed { background: #d1fae5 !important; color: #065f46 !important; }
        .badge-status.pending { background: #fee2e2 !important; color: #991b1b !important; }
        .badge-payment.paid { background: #d1fae5 !important; color: #065f46 !important; }
        .badge-payment.awaiting-payment { background: #fee2e2 !important; color: #991b1b !important; }
    </style>
</head>
<body>

    <?php include('bar.php'); ?>

    <header class="hero-section" style="padding-top: 130px;">
        <div class="hero-overlay" style="text-align: center; color: white;">
            <h1 style="font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 800;">Find Your Perfect Stay</h1>
            <p style="color: rgba(255,255,255,0.7); margin-top: 8px;">Discover verified local homestays across Malaysia.</p>
        </div>
        
        <div class="search-container" style="max-width: 900px; margin: 30px auto 0;">
            <form action="booking.php" method="GET" class="search-bar" style="background: rgba(14, 58, 32, 0.8); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(8px); padding: 15px; border-radius: 16px; display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="search-input-group" style="flex: 1; min-width: 180px;">
                    <label style="font-size: 0.72rem; color: #f4cb66; text-transform: uppercase; font-weight: 700;"><i class="ri-map-pin-line"></i> Destination</label>
                    <input type="text" name="state" placeholder="Where are you going?" value="<?php echo htmlspecialchars($search_state); ?>" style="background:transparent; border:none; color:white; width:100%; outline:none; margin-top: 4px;">
                </div>
                <div class="search-input-group" style="flex: 1; min-width: 130px;">
                    <label style="font-size: 0.72rem; color: #f4cb66; text-transform: uppercase; font-weight: 700;"><i class="ri-calendar-check-line"></i> Check-in</label>
                    <input type="date" name="check_in" id="search-in" value="<?php echo htmlspecialchars($check_in); ?>" style="background:transparent; border:none; color:white; width:100%; outline:none; margin-top: 4px; filter: invert(1);">
                </div>
                <div class="search-input-group" style="flex: 1; min-width: 130px;">
                    <label style="font-size: 0.72rem; color: #f4cb66; text-transform: uppercase; font-weight: 700;"><i class="ri-calendar-close-line"></i> Check-out</label>
                    <input type="date" name="check_out" id="search-out" value="<?php echo htmlspecialchars($check_out); ?>" style="background:transparent; border:none; color:white; width:100%; outline:none; margin-top: 4px; filter: invert(1);">
                </div>
                <div class="search-input-group" style="flex: 0.5; min-width: 80px;">
                    <label style="font-size: 0.72rem; color: #f4cb66; text-transform: uppercase; font-weight: 700;"><i class="ri-user-shared-line"></i> Guests</label>
                    <input type="number" name="guests" min="1" value="<?php echo $guests; ?>" style="background:transparent; border:none; color:white; width:100%; outline:none; margin-top: 4px;">
                </div>
                <button type="submit" class="search-btn" style="background:#f4cb66; color:#0e3a20; font-weight:700; border:none; border-radius:8px; padding: 10px 24px; cursor:pointer;"><i class="ri-search-line"></i> Search</button>
            </form>
        </div>
    </header>

    <main class="booking-layout">
        
        <aside class="filter-sidebar">
            <h3><i class="ri-filter-3-line"></i> Filter Search</h3>
            <form action="booking.php" method="GET">
                <input type="hidden" name="state" value="<?php echo htmlspecialchars($search_state); ?>">
                <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                <input type="hidden" name="guests" value="<?php echo htmlspecialchars($guests); ?>">
                
                

                <div class="filter-group">
                    <label>Rating</label>
                    <select name="rating" style="width:100%; padding:8px; border-radius:6px; background:#0e3a20; color:white; border:1px solid rgba(255,255,255,0.2);">
                        <option value="0">Any Rating</option>
                        <option value="3" <?php if($rating===3) echo 'selected'; ?>>3 Stars & Up</option>
                        <option value="4" <?php if($rating===4) echo 'selected'; ?>>4 Stars & Up</option>
                        <option value="5" <?php if($rating===5) echo 'selected'; ?>>5 Stars Only</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Pricing Model</label>
                    <select name="pricing_type" style="width:100%; padding:8px; border-radius:6px; background:#0e3a20; color:white; border:1px solid rgba(255,255,255,0.2);">
                        <option value="">All Types</option>
                        <option value="Whole House" <?php if($pricing_type==='Whole House') echo 'selected'; ?>>Entire House</option>
                        <option value="Per Room" <?php if($pricing_type==='Per Room') echo 'selected'; ?>>Per Room</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Facilities</label>
                    <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:8px;">
                        <?php $facilitiesList = ['WiFi', 'Swimming Pool', 'Kitchen', 'Air Conditioner', 'Smart TV', 'BBQ Pit', 'Free Parking']; ?>
                        <?php foreach ($facilitiesList as $facility): ?>
                        <label style="font-size:0.85rem; display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="facilities[]" value="<?php echo htmlspecialchars($facility); ?>" <?php echo in_array($facility, $selected_facilities) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($facility); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Max Price (RM/Night)</label>
                    <input type="range" name="max_price" min="50" max="1000" step="50" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : '1000'; ?>" oninput="this.nextElementSibling.value = 'RM' + this.value">
                    <output>RM<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : '1000'; ?></output>
                </div>

                <div class="filter-group">
                    <label>Sort By</label>
                    <select name="sort" style="width:100%; padding:8px; border-radius:6px; background:#0e3a20; color:white; border:1px solid rgba(255,255,255,0.2);">
                        <option value="popular" <?php if($sort==='popular') echo 'selected'; ?>>Most Popular</option>
                        <option value="price_low" <?php if($sort==='price_low') echo 'selected'; ?>>Lowest Price</option>
                        <option value="price_high" <?php if($sort==='price_high') echo 'selected'; ?>>Highest Price</option>
                        <option value="rating_high" <?php if($sort==='rating_high') echo 'selected'; ?>>Top Rated</option>
                        <option value="newest" <?php if($sort==='newest') echo 'selected'; ?>>Newest Listings</option>
                    </select>
                </div>

                <button type="submit" class="search-btn" style="width:100%; background:#f4cb66; color:#0e3a20; font-weight:700; border:none; padding:10px; border-radius:6px; cursor:pointer;">Apply Filters</button>
            </form>
        </aside>

        <section class="properties-section">
            <div class="section-header">
                <h2>Verified Homestays (Published)</h2>
                <div class="wishlist-toggle-panel" style="position:relative;">
                    <button class="wishlist-trigger-btn" onclick="document.getElementById('wishlist-aside').classList.toggle('open')" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); color:white; padding:8px 16px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <i class="ri-heart-line" style="color:#f4cb66;"></i> Wishlist (<span id="wishlist-count">0</span>)
                    </button>
                    <!-- Floating Wishlist Panel -->
                    <div id="wishlist-aside" style="display:none; position:absolute; right:0; top:45px; background:rgba(14,58,32,0.98); border:1px solid rgba(255,255,255,0.15); border-radius:12px; width:300px; padding:15px; z-index:999; box-shadow:0 8px 24px rgba(0,0,0,0.3);">
                        <h4 style="color:#f4cb66; margin-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:6px;">My Favorites</h4>
                        <div id="wishlist-items-container"></div>
                    </div>
                </div>
            </div>

            <div class="properties-grid">
                <?php if (!empty($homestays)): ?>
                    <?php foreach ($homestays as $row): 
                        // Wishlist check
                        $is_wishlist = false;
                        try {
                            $wh_check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND homestay_id = ?");
                            $wh_check->execute([$user_id, $row['id']]);
                            $is_wishlist = $wh_check->fetch() ? true : false;
                        } catch (PDOException $e) {
                            $is_wishlist = false;
                        }
                    ?>
                        <div class="property-card" data-id="<?php echo $row['id']; ?>">
                        <div class="card-image-wrapper" style="position:relative; overflow:hidden;">
                            <?php
                            $facility_imgs = [];
                            if (!empty($row['facility_images'])) {
                                $decoded = json_decode($row['facility_images'], true);
                                if (is_array($decoded)) $facility_imgs = $decoded;
                            }
                            // Add cover image as first slide
                            if (!empty($row['cover_image'])) array_unshift($facility_imgs, $row['cover_image']);
                            if (empty($facility_imgs)) $facility_imgs = ['images/default_place.jpg'];
                            $card_id = 'imgslider_' . $row['id'];
                            ?>
                            <div id="<?php echo $card_id; ?>" style="position:relative; width:100%; height:100%;">
                                <?php foreach ($facility_imgs as $idx => $img_path): ?>
                                <img class="card-slide-img" src="<?php echo htmlspecialchars($img_path); ?>"
                                     alt="Property Image"
                                     onerror="this.src='images/default_place.jpg';"
                                     style="position:<?php echo $idx === 0 ? 'relative' : 'absolute'; ?>; top:0; left:0; width:100%; height:100%; object-fit:cover; opacity:<?php echo $idx === 0 ? '1' : '0'; ?>; transition:opacity 0.5s ease;">
                                <?php endforeach; ?>
                                <?php if (count($facility_imgs) > 1): ?>
                                <div style="position:absolute; bottom:8px; left:50%; transform:translateX(-50%); display:flex; gap:5px; z-index:5;">
                                    <?php foreach ($facility_imgs as $idx => $img): ?>
                                    <span onclick="goSlide('<?php echo $card_id; ?>',<?php echo $idx; ?>)" style="width:7px;height:7px;border-radius:50%;background:<?php echo $idx===0?'#f4cb66':'rgba(255,255,255,0.5)'; ?>;cursor:pointer;transition:background 0.3s;" class="slide-dot"></span>
                                    <?php endforeach; ?>
                                </div>
                                <button onclick="slideCard('<?php echo $card_id; ?>',-1)" style="position:absolute;left:6px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.4);border:none;color:white;border-radius:50%;width:26px;height:26px;cursor:pointer;font-size:0.85rem;z-index:5;">&#8249;</button>
                                <button onclick="slideCard('<?php echo $card_id; ?>',1)" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.4);border:none;color:white;border-radius:50%;width:26px;height:26px;cursor:pointer;font-size:0.85rem;z-index:5;">&#8250;</button>
                                <?php endif; ?>
                            </div>
                            <button class="wishlist-heart-btn <?php echo $is_wishlist ? 'active' : ''; ?>" onclick="toggleWishlist(<?php echo $row['id']; ?>, this)" style="position:absolute;top:10px;right:10px;z-index:6;">
                                <i class="<?php echo $is_wishlist ? 'ri-heart-fill' : 'ri-heart-line'; ?>"></i>
                            </button>
                            <span class="availability-badge status-available" style="position:absolute;top:10px;left:10px;z-index:6;">Verified Publish</span>
                        </div>
                            <div class="card-details">
                                <div class="card-header-row">
                                    <span class="property-location"><i class="ri-map-pin-2-line"></i> <?php echo htmlspecialchars($row['district'] . ', ' . $row['state']); ?></span>
                                    <span class="property-rating" style="color:#f4cb66;"><i class="ri-star-fill"></i> 4.8 (14 reviews)</span>
                                </div>
                                <h4 class="property-title"><?php echo htmlspecialchars($row['name']); ?></h4>
                                <p class="property-facilities">
                                    <span><i class="ri-user-line"></i> Max <?php echo $row['max_guests']; ?> Pax</span>
                                    <span><i class="ri-home-gear-line"></i> <?php echo htmlspecialchars($row['category']); ?></span>
                                </p>
                                <div class="card-footer-row">
                                    <div class="price-box">
                                        <span class="price-amount">RM<?php echo number_format($row['price_per_night'], 2); ?></span> / night
                                    </div>
                                    <button class="view-details-btn" onclick="openBookingModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Book Now</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-properties" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                        <i class="ri-hotel-bed-line" style="font-size: 3rem; color: #f4cb66; display: block; margin-bottom: 15px;"></i>
                        <p>No published homestays found matching the state search filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Secure Booking Modal -->
    <div id="bookingModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-modal-btn" onclick="closeBookingModal()">&times;</span>
            <div class="modal-grid-layout">
                <div class="modal-info-pane">
                    <h2 id="modal-title">Homestay Name</h2>
                    <p id="modal-location" class="location-sub"><i class="ri-map-pin-line"></i> Location</p>
                    
                    <div class="airbnb-gallery">
                        <div class="gallery-main">
                            <img id="gallery-main-img" src="images/default_place.jpg" alt="Main" onerror="this.src='images/default_place.jpg';">
                        </div>
                        <div id="gallery-thumbs" class="gallery-thumbs" style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px;"></div>
                    </div>

                    <h3>Facilities & Details</h3>
                    <div id="modal-facilities" class="facilities-tags"></div>
                    
                    <h3>About this stay</h3>
                    <p id="modal-desc">Description</p>
                </div>

                <div class="modal-widget-pane">
                    <div class="pricing-card-widget">
                        <h3 style="color:#f4cb66; margin-bottom: 15px;"><i class="ri-shield-user-line"></i> Secure Booking</h3>
                        
                        <form id="checkoutForm" action="toyyibpay_process.php" method="GET">
                            <input type="hidden" name="homestay_id" id="form-homestay-id">
                            <input type="hidden" name="pricing_type" id="form-pricing-type" value="Whole House">
                            <input type="hidden" name="price_per_night" id="form-price-per-night">
                            <input type="hidden" name="total_amount_cents" id="form-total-amount-cents" value="0">

                            <div class="widget-field">
                                <label>Check-in Date</label>
                                <input type="date" name="check_in" id="widget-in" required onchange="checkAvailability()">
                            </div>
                            <div class="widget-field" style="margin-top: 10px;">
                                <label>Check-out Date</label>
                                <input type="date" name="check_out" id="widget-out" required onchange="checkAvailability()">
                            </div>
                            <div id="pricingTypeLabel" style="margin-top: 12px; color: #f4cb66; font-size: 0.9rem;">Select dates to see availability.</div>
                            
                            <!-- Dynamic Vacant Room Select list (For Per Room Pricing) -->
                            <div class="widget-field" id="room-selection-wrapper" style="display: none; margin-top: 10px;">
                                <label style="color: #f4cb66;">Available Room Variant *</label>
                                <select name="room_id" id="widget-room-select" required onchange="calculateLivePrice()"></select>
                            </div>

                            <div class="widget-field" style="margin-top: 10px;">
                                <label>Total Guests</label>
                                <input type="number" name="guests" min="1" max="10" value="1" required>
                            </div>

                            <div id="availability-status-alert" style="margin-top: 12px; padding: 10px; border-radius: 8px; font-size: 0.85rem; display: none;"></div>

                            <div class="live-invoice-summary" id="invoiceSummary" style="margin-top: 15px;">
                                <div class="invoice-row" style="display: flex; justify-content: space-between; font-size: 0.88rem;">
                                    <span id="invoice-days-calc">RM0.00 x 0 nights</span>
                                    <span id="invoice-base-price">RM0.00</span>
                                </div>
                                <div class="invoice-row" style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-top: 6px;">
                                    <span><?= htmlspecialchars($system_name) ?> Service Fee (10%)</span>
                                    <span id="invoice-service-fee">RM0.00</span>
                                </div>
                                <hr style="border: none; border-top: 1px dashed rgba(255,255,255,0.15); margin: 10px 0;">
                                <div class="invoice-row total-row" style="display: flex; justify-content: space-between; font-weight: 700; color: #f4cb66;">
                                    <span>Total Amount Due</span>
                                    <span id="invoice-grand-total">RM0.00</span>
                                </div>
                            </div>

                            <button type="submit" class="confirm-payment-btn" id="confirm-booking-btn">Continue to Payment <i class="ri-arrow-right-line"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== BOOKING HISTORY LEDGER ==================== -->
    <section class="booking-history-section" style="max-width: 1400px; margin: 60px auto 60px; width: 90%;">
        <div style="background: rgba(14, 58, 32, 0.75); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 30px; color: white; box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
            <h2 style="color: #f4cb66; font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 800; margin-bottom: 10px;"><i class="ri-history-line"></i> My Booking History</h2>
            <p style="color: rgba(255,255,255,0.7); margin-bottom: 25px;">Track all your upcoming, current, or completed stays at verified homestays across Malaysia.</p>
            
            <div style="overflow-x: auto; background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: rgba(14, 58, 32, 0.9); border-bottom: 1px solid rgba(255,255,255,0.15);">
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Booking ID</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Homestay Name</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Owner</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Check-in</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Check-out</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Guests</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Amount Paid</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Booking Status</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Payment Status</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #f4cb66; font-size: 0.85rem; text-transform: uppercase;">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="color: rgba(255,255,255,0.95);">
                        <?php if (empty($traveler_bookings)): ?>
                            <tr><td colspan="10" style="padding: 30px; text-align: center; color: rgba(255,255,255,0.5); font-style: italic;">No reservation history found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($traveler_bookings as $bkg): 
                                $today = date('Y-m-d');
                                
                                // Statuses computed automatically based on dates
                                $bkg_status = $bkg['booking_status'];
                                $pay_status = $bkg['payment_status'];
                                
                                if ($pay_status !== 'Paid') {
                                    $pay_status = 'Awaiting Payment';
                                    $bkg_status = 'Pending';
                                } else {
                                    if ($today < $bkg['check_in']) {
                                        $bkg_status = 'Upcoming';
                                    } elseif ($today >= $bkg['check_in'] && $today <= $bkg['check_out']) {
                                        $bkg_status = 'Checked In';
                                    } else {
                                        $bkg_status = 'Completed';
                                    }
                                }

                                // Check if reviewed already
                                $rev_stmt = $pdo->prepare("SELECT COUNT(*) FROM homestay_reviews WHERE booking_id = ? AND user_id = ?");
                                $rev_stmt->execute([$bkg['id'], $user_id]);
                                $has_reviewed = $rev_stmt->fetchColumn() > 0;
                                if ($has_reviewed) {
                                    $bkg_status = 'Reviewed';
                                }
                            ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);">
                                    <td style="padding: 14px 16px; font-weight: 700;">#<?php echo $bkg['booking_no'] ? htmlspecialchars($bkg['booking_no']) : 'VYG-BKG-'.$bkg['id']; ?></td>
                                    <td style="padding: 14px 16px;">
                                        <strong><?php echo htmlspecialchars($bkg['stay_name']); ?></strong>
                                        <?php if ($bkg['room_name']): ?>
                                            <br><small style="color: rgba(255,255,255,0.6);">Room: <?php echo htmlspecialchars($bkg['room_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 14px 16px;"><?php echo htmlspecialchars($bkg['owner_name']); ?></td>
                                    <td style="padding: 14px 16px;"><?php echo $bkg['check_in']; ?></td>
                                    <td style="padding: 14px 16px;"><?php echo $bkg['check_out']; ?></td>
                                    <td style="padding: 14px 16px;"><?php echo $bkg['guests']; ?> Guests</td>
                                    <td style="padding: 14px 16px; font-weight: 700; color: #f4cb66;">RM <?php echo number_format($bkg['total_price'], 2); ?></td>
                                    <td style="padding: 14px 16px;">
                                        <span class="badge-status <?php echo strtolower(str_replace(' ', '-', $bkg_status)); ?>" style="padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; display: inline-block;">
                                            <?php echo htmlspecialchars($bkg_status); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 14px 16px;">
                                        <span class="badge-payment <?php echo strtolower(str_replace(' ', '-', $pay_status)); ?>" style="padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; display: inline-block;">
                                            <?php echo htmlspecialchars($pay_status); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 14px 16px;">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button onclick="showReceipt('<?php echo htmlspecialchars($bkg['booking_no'] ?: 'VYG-BKG-'.$bkg['id']); ?>', '<?php echo htmlspecialchars(addslashes($bkg['stay_name'])); ?>', '<?php echo $bkg['check_in']; ?>', '<?php echo $bkg['check_out']; ?>', '<?php echo $bkg['guests']; ?>', '<?php echo $bkg['total_price']; ?>', '<?php echo htmlspecialchars($bkg['billcode'] ?? ''); ?>')" style="background: #f4cb66; color: #0e3a20; border: none; padding: 6px 10px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.8rem; transition: 0.2s;"><i class="ri-printer-line"></i> Receipt</button>
                                            
                                            <?php if ($bkg_status === 'Completed'): ?>
                                                <?php if ($has_reviewed): ?>
                                                    <span style="color:rgba(255,255,255,0.6); font-size:0.8rem; font-style:italic;"><i class="ri-checkbox-circle-fill" style="color: #f4cb66;"></i> Reviewed</span>
                                                <?php else: ?>
                                                    <button onclick="openReviewModal(<?php echo $bkg['id']; ?>, <?php echo $bkg['homestay_id']; ?>, '<?php echo htmlspecialchars(addslashes($bkg['stay_name'])); ?>')" style="background: #f4cb66; color: #0e3a20; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; cursor: pointer; transition: 0.2s;"><i class="ri-message-3-line"></i> Leave Review</button>
                                                <?php endif; ?>
                                            <?php elseif ($bkg_status === 'Reviewed'): ?>
                                                <span style="color:rgba(255,255,255,0.6); font-size:0.8rem; font-style:italic;"><i class="ri-checkbox-circle-fill" style="color: #f4cb66;"></i> Completed</span>
                                            <?php else: ?>
                                                <span style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">Awaiting stay</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Review Form Modal -->
    <div class="review-modal" id="reviewModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
        <div class="review-modal-box" style="background: rgba(14, 58, 32, 0.98); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.15); border-radius: 24px; padding: 24px; width: 90%; max-width: 440px; color: white; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
            <h3 style="margin-top:0; color:#f4cb66; font-family:'Playfair Display', serif;"><i class="ri-star-line"></i> Leave Review & Feedback</h3>
            <p id="review-stay-name" style="font-size:0.88rem; color:rgba(255,255,255,0.7); margin: 6px 0 15px;"></p>
            
            <form method="POST" action="booking.php">
                <input type="hidden" name="action_submit_review" value="1">
                <input type="hidden" name="booking_id" id="review-booking-id">
                <input type="hidden" name="homestay_id" id="review-homestay-id">
                
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-size:0.8rem; text-transform:uppercase; color:#f4cb66; margin-bottom:6px; font-weight: 700;">Rating Score *</label>
                    <select name="rating" required style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); outline: none; background: rgba(0,0,0,0.3); color: white;">
                        <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                        <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                        <option value="3">⭐⭐⭐ (3 Stars)</option>
                        <option value="2">⭐⭐ (2 Stars)</option>
                        <option value="1">⭐ (1 Star)</option>
                    </select>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:0.8rem; text-transform:uppercase; color:#f4cb66; margin-bottom:6px; font-weight: 700;">Feedback Comments *</label>
                    <textarea name="feedback_text" rows="4" placeholder="How was your stay? Let us know details..." required style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); font-family:inherit; font-size:0.88rem; outline:none; resize:none; background: rgba(0,0,0,0.3); color: white;"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeReviewModal()" style="padding:10px 18px; border-radius:8px; border:none; background:rgba(255,255,255,0.1); color:white; cursor:pointer; font-weight: 700;">Cancel</button>
                    <button type="submit" style="padding:10px 20px; border-radius:8px; border:none; background:#f4cb66; color:#0e3a20; font-weight:700; cursor:pointer;">Submit Review</button>
                </div>
            </form>
        </div>
    </div>

    <script>
      function openReviewModal(bookingId, homestayId, stayName) {
          document.getElementById('review-booking-id').value = bookingId;
          document.getElementById('review-homestay-id').value = homestayId;
          document.getElementById('review-stay-name').innerText = "Property: " + stayName;
          document.getElementById('reviewModal').style.display = 'flex';
      }
      
      function closeReviewModal() {
          document.getElementById('reviewModal').style.display = 'none';
      }

      function showReceipt(bookingNo, stayName, checkIn, checkOut, guests, price, billcode) {
          Swal.fire({
              title: '<?= htmlspecialchars($system_name) ?> Payment Receipt',
              html: `
                  <div style="text-align: left; font-size: 0.9rem; line-height: 1.6; color: #334155;">
                      <p><strong>Booking Reference:</strong> ${bookingNo}</p>
                      <p><strong>Property Name:</strong> ${stayName}</p>
                      <p><strong>Stay Period:</strong> ${checkIn} to ${checkOut}</p>
                      <p><strong>Guests Count:</strong> ${guests} Guests</p>
                      <p><strong>Payment Reference:</strong> ${billcode || 'MOCK_FPX'}</p>
                      <hr style="border: none; border-top: 1px dashed #cbd5e1; margin: 10px 0;">
                      <p style="font-size: 1.15rem; color: #0e3a20; margin-top: 8px;"><strong>Total Paid:</strong> RM ${parseFloat(price).toFixed(2)}</p>
                  </div>
              `,
              confirmButtonText: 'Print Receipt / Close',
              confirmButtonColor: '#06402B'
          }).then((result) => {
              if (result.isConfirmed) {
                  // Simulate print receipt trigger
              }
          });
      }

      // Check URL parameters for review notifications
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('review') === 'success') {
          Swal.fire('Review Submitted!', 'Thank you! Your feedback has been logged and the host has been notified.', 'success');
      }
    </script>

    <script src="js/booking.js"></script>
    <script>
    // Image gallery slider for property cards
    function slideCard(sliderId, dir) {
        const wrap = document.getElementById(sliderId);
        if (!wrap) return;
        const imgs = wrap.querySelectorAll('.card-slide-img');
        const dots = wrap.parentElement.querySelectorAll('.slide-dot');
        let current = 0;
        imgs.forEach((img, i) => { if (parseFloat(img.style.opacity) === 1) current = i; });
        imgs[current].style.opacity = '0';
        imgs[current].style.position = 'absolute';
        if (dots[current]) dots[current].style.background = 'rgba(255,255,255,0.5)';
        current = (current + dir + imgs.length) % imgs.length;
        imgs[current].style.opacity = '1';
        imgs[current].style.position = 'relative';
        if (dots[current]) dots[current].style.background = '#f4cb66';
    }
    function goSlide(sliderId, idx) {
        const wrap = document.getElementById(sliderId);
        if (!wrap) return;
        const imgs = wrap.querySelectorAll('.card-slide-img');
        const dots = wrap.parentElement.querySelectorAll('.slide-dot');
        imgs.forEach((img, i) => {
            img.style.opacity = i === idx ? '1' : '0';
            img.style.position = i === idx ? 'relative' : 'absolute';
            if (dots[i]) dots[i].style.background = i === idx ? '#f4cb66' : 'rgba(255,255,255,0.5)';
        });
    }
    </script>
</body>
</html>
