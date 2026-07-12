<?php
// admin.php - Main Dashboard (Upgraded Premium Themed Aesthetics)
require_once 'toyyibpay_config.php';

$stmt = $pdo->prepare("SELECT p.id, u.fullname as user, h.state, DATE(p.payment_date) as date, p.amount as budget, p.status 
                       FROM payment_history p 
                       JOIN users u ON p.user_id = u.id 
                       JOIN homestays h ON p.homestay_id = h.id 
                       ORDER BY p.id DESC");
$stmt->execute();
$raw_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

$booking_history = [];
$revenue_trends = [];   
$status_counts = ['confirmed' => 0, 'pending' => 0]; 

foreach ($raw_history as $row) {
    $status_lbl = strtolower($row['status']) == 'paid' ? 'confirmed' : 'pending';
    $amount = (float)$row['budget'];
    
    $status_counts[$status_lbl]++;
    if ($status_lbl === 'confirmed' && !empty($row['date'])) {
        $revenue_trends[$row['date']] = ($revenue_trends[$row['date']] ?? 0) + $amount;
    }

    $booking_history[] = [
        'id' => $row['id'],
        'user' => !empty($row['user']) ? $row['user'] : 'Unknown User',
        'state' => $row['state'],
        'date' => $row['date'],
        'budget' => 'RM ' . number_format($amount, 2),
        'status' => $status_lbl
    ];
}

ksort($revenue_trends);

$stat_total_plans = count($booking_history);
$stat_active_users = count(array_unique(array_column($booking_history, 'user')));

$total_revenue_num = 0;
$state_counts = [];
foreach($booking_history as $b) {
    if($b['status'] == 'confirmed') {
        $val = (float) str_replace(['RM ', ','], '', $b['budget']);
        $total_revenue_num += $val;
    }
    if (!isset($state_counts[$b['state']])) {
        $state_counts[$b['state']] = 0;
    }
    $state_counts[$b['state']]++;
}
$stat_total_revenue = "RM " . number_format($total_revenue_num, 2);

if (count($state_counts) > 0) {
    arsort($state_counts);
    $stat_popular_state = array_key_first($state_counts);
} else {
    $stat_popular_state = "N/A";
}

$voyago_commission = $total_revenue_num * 0.10;
$host_payouts = $total_revenue_num * 0.90;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_system_name'])) {
    $new_name = trim($_POST['new_system_name']);
    if (!empty($new_name)) {
        $up_stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'system_name'");
        $up_stmt->execute([$new_name]);
        $system_name = $new_name; // update local variable for immediate display
        $success_msg = "System name updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> Admin Portal - Home Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/admin.css?v=<?= time() ?>">
    <style>
        .dashboard-charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.8rem;
            margin-top: 1rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 900px) {
            .dashboard-charts-grid {
                grid-template-columns: 1fr;
            }
        }
        .chart-card-wrapper {
            background: #ffffff;
            border-radius: 1rem;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(6, 64, 43, 0.04);
            border: 1px solid rgba(6, 64, 43, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .chart-card-wrapper h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: #06402b;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid rgba(6, 64, 43, 0.05);
            padding-bottom: 10px;
        }
        .chart-canvas-container {
            position: relative;
            width: 100%;
            height: 320px; 
        }
        .analytics-category-title {
            margin-top: 2.5rem;
            font-size: 1.4rem;
            color: #06402b;
            font-family: 'Playfair Display', serif;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
            padding-bottom: 6px;
        }
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
            <li><a href="admin_commission.php">Admin Commission</a></li>
            <li><a href="admin.php" style="color: #d4af37; font-weight: 600;">Dashboard</a></li>
            <li><a href="admin_homestay.php">Homestay Approvals</a></li>
            <li><a href="index.php?action=logout" style="background: #e74c3c; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">LOGOUT</a></li>
        </ul>
    </nav>

    <div class="admin-workspace-container">
        <header class="admin-workspace-header">
            <div class="header-titles">
                <h1>Admin Dashboard</h1>
                <p>Welcome back! Here is a quick breakdown of your website's traffic and active trip records.</p>
            </div>
        </header>

        <section class="metrics-grid">
            <div class="metric-card">
                <div class="metric-info">
                    <span class="metric-label">Total Trips Created</span>
                    <h3 class="metric-val"><?php echo $stat_total_plans; ?></h3>
                </div>
                <div class="metric-icon"><i class="ri-route-line"></i></div>
            </div>
            <div class="metric-card">
                <div class="metric-info">
                    <span class="metric-label">Total Active Users</span>
                    <h3 class="metric-val"><?php echo $stat_active_users; ?></h3>
                </div>
                <div class="metric-icon"><i class="ri-user-line"></i></div>
            </div>
            <div class="metric-card">
                <div class="metric-info">
                    <span class="metric-label">Total Bookings Value</span>
                    <h3 class="metric-val"><?php echo $stat_total_revenue; ?></h3>
                </div>
                <div class="metric-icon"><i class="ri-money-dollar-box-line"></i></div>
            </div>
            <div class="metric-card highlight-card">
                <div class="metric-info">
                    <span class="metric-label">Top Destination</span>
                    <h3 class="metric-val"><?php echo $stat_popular_state; ?></h3>
                </div>
                <div class="metric-icon"><i class="ri-treasure-map-line"></i></div>
            </div>
        </section>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.8rem; margin-bottom: 1.5rem;">
            <section class="admin-section-card" style="margin-bottom:0;">
                <div class="section-card-header">
                    <h3><i class="ri-pulse-line"></i> System Status</h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.9rem; color:var(--text-light);">Database Status</span>
                        <span style="font-weight:700; color:#06402B; font-size:0.9rem;"><i class="ri-checkbox-circle-fill"></i> Healthy & Running</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.9rem; color:var(--text-light);">File Storage Sync</span>
                        <span style="font-weight:700; color:#06402B; font-size:0.9rem;"><i class="ri-checkbox-circle-fill"></i> Fully Connected</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.9rem; color:var(--text-light);">Security Encryption</span>
                        <span style="font-weight:700; color:var(--accent-gold); font-size:0.9rem;">Active</span>
                    </div>
                </div>
            </section>

            <section class="admin-section-card" style="margin-bottom:0;">
                <div class="section-card-header">
                    <h3><i class="ri-notification-badge-line"></i> Recent Updates</h3>
                </div>
                <ul style="list-style:none; margin-top:1rem; display:flex; flex-direction:column; gap:12px; font-size:0.88rem;">
                    <li style="display:flex; gap:10px; color:var(--text-dark);"><i class="ri-time-line" style="color:var(--accent-gold);"></i> <span>The travel smart planner is reading destination lists smoothly.</span></li>
                    <li style="display:flex; gap:10px; color:var(--text-dark);"><i class="ri-time-line" style="color:var(--accent-gold);"></i> <span>User budget calculator functions are loaded with zero system errors.</span></li>
                </ul>
            </section>
        </div>

        <!-- System Settings Section -->
        <section class="admin-section-card" style="margin-top: 2rem; margin-bottom: 2rem;">
            <div class="section-card-header">
                <h3><i class="ri-settings-3-line"></i> System Settings</h3>
            </div>
            <div style="margin-top: 1rem;">
                <?php if (!empty($success_msg)): ?>
                    <div style="color: #158a48; background: #e6f6eb; padding: 10px; border-radius: 4px; margin-bottom: 1rem;">
                        <?= htmlspecialchars($success_msg) ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="admin.php" style="display: flex; gap: 1rem; align-items: center;">
                    <label style="font-weight: 500; color: #06402b;">System Name:</label>
                    <input type="text" name="new_system_name" value="<?= htmlspecialchars($system_name) ?>" required style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
                    <button type="submit" style="background: #d4af37; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Update Name</button>
                </form>
            </div>
        </section>

        <h2 class="analytics-category-title"><i class="ri-bar-chart-box-line"></i> Platform System Analytics</h2>
        
        <div class="dashboard-charts-grid">
            <div class="chart-card-wrapper">
                <h4><i class="ri-line-chart-line" style="color: #d4af37;"></i> Booking Performance Trend</h4>
                <div class="chart-canvas-container">
                    <canvas id="chartRevenueTrend"></canvas>
                </div>
            </div>

            <div class="chart-card-wrapper">
                <h4><i class="ri-bar-chart-fill" style="color: #06402b;"></i> Destinations Spread Matrix</h4>
                <div class="chart-canvas-container">
                    <canvas id="chartStatesSpread"></canvas>
                </div>
            </div>

            <div class="chart-card-wrapper">
                <h4><i class="ri-pie-chart-2-line" style="color: #06402b;"></i> Reservation Status Ratios</h4>
                <div class="chart-canvas-container">
                    <canvas id="chartStatusRatio"></canvas>
                </div>
            </div>

            <div class="chart-card-wrapper">
                <h4><i class="ri-donut-chart-fill" style="color: #d4af37;"></i> Platform Revenue Architecture</h4>
                <div class="chart-canvas-container">
                    <canvas id="chartRevenueSplit"></canvas>
                </div>
            </div>
            </div>
        </div>

        <!-- Admin Reports & Tables Center -->
        <h2 class="analytics-category-title"><i class="ri-file-list-3-line"></i> Admin Reports & Data Center</h2>
        
        <div class="admin-section-card" style="margin-top: 1rem; margin-bottom: 2rem;">
            <div class="reports-tabs-header" style="display: flex; gap: 8px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 20px; overflow-x: auto; white-space: nowrap;">
                <button class="tab-btn active" onclick="switchReportTab(event, 'tab-bookings')" style="background: rgba(255,255,255,0.05); color: #f4cb66; border: 1px solid rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif;">Bookings</button>
                <button class="tab-btn" onclick="switchReportTab(event, 'tab-settlements')" style="background: transparent; color: white; border: 1px solid transparent; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif;">Owner Settlements</button>
                <button class="tab-btn" onclick="switchReportTab(event, 'tab-commission')" style="background: transparent; color: white; border: 1px solid transparent; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif;">Commission</button>
                <button class="tab-btn" onclick="switchReportTab(event, 'tab-reviews')" style="background: transparent; color: white; border: 1px solid transparent; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif;">Reviews</button>
                <button class="tab-btn" onclick="switchReportTab(event, 'tab-homestays')" style="background: transparent; color: white; border: 1px solid transparent; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif;">Registered Homestays</button>
                <button class="tab-btn" onclick="switchReportTab(event, 'tab-users')" style="background: transparent; color: white; border: 1px solid transparent; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif;">Registered Users</button>
                <button class="tab-btn" onclick="switchReportTab(event, 'tab-monthly')" style="background: transparent; color: white; border: 1px solid transparent; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif;">Monthly Reports</button>
            </div>
            
            <!-- Bookings Tab -->
            <div id="tab-bookings" class="tab-content-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="color: #f4cb66; margin: 0;"><i class="ri-book-open-line"></i> Global Bookings Logs</h4>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportToExcel('bookings-table', 'Bookings_Report')" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-excel-line"></i> Excel</button>
                        <button type="button" onclick="exportToPDF('bookings-table', 'Bookings_Report')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-pdf-line"></i> PDF</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="bookings-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Guest Name</th>
                                <th>Homestay Name</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Total Revenue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>#1001</td><td>Faiz Customer</td><td>Sunset Valley Chalet</td><td>2026-07-01</td><td>2026-07-03</td><td>RM 360.00</td><td><span style="color: #10b981;">Completed</span></td></tr>
                            <tr><td>#1002</td><td>Ahmad Yani</td><td>Hillview Retreat</td><td>2026-07-02</td><td>2026-07-05</td><td>RM 540.00</td><td><span style="color: #10b981;">Completed</span></td></tr>
                            <tr><td>#1003</td><td>Siti Nur</td><td>Ocean Breeze Villa</td><td>2026-07-04</td><td>2026-07-05</td><td>RM 270.00</td><td><span style="color: #10b981;">Completed</span></td></tr>
                            <tr><td>#1004</td><td>Ali Abu</td><td>Sunset Valley Chalet</td><td>2026-07-08</td><td>2026-07-10</td><td>RM 360.00</td><td><span style="color: #f4cb66;">Upcoming</span></td></tr>
                            <tr><td>#1005</td><td>Kamal Ibrahim</td><td>Kampung Style Homestay</td><td>2026-07-09</td><td>2026-07-12</td><td>RM 450.00</td><td><span style="color: #f4cb66;">Upcoming</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Owner Settlements Tab -->
            <div id="tab-settlements" class="tab-content-panel" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="color: #f4cb66; margin: 0;"><i class="ri-bank-card-line"></i> Owner Settlement Batches</h4>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportToExcel('settlements-table', 'Owner_Settlements_Report')" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-excel-line"></i> Excel</button>
                        <button type="button" onclick="exportToPDF('settlements-table', 'Owner_Settlements_Report')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-pdf-line"></i> PDF</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="settlements-table">
                        <thead>
                            <tr>
                                <th>Settlement ID</th>
                                <th>Host Name</th>
                                <th>Settlement Period</th>
                                <th>Total Bookings</th>
                                <th>Total Gross Revenue</th>
                                <th>Voyago Commission (10%)</th>
                                <th>Net Payout Owed</th>
                                <th>Status</th>
                                <th>Transfer Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>#ST-1001</td><td>Faiz</td><td>2026-07-01 to 2026-07-07</td><td>3</td><td>RM 1,170.00</td><td>RM 117.00</td><td>RM 1,053.00</td><td><span style="color: #10b981;">Paid</span></td><td>2026-07-10 16:00</td></tr>
                            <tr><td>#ST-1002</td><td>Ahmad Host</td><td>2026-07-01 to 2026-07-07</td><td>4</td><td>RM 2,160.00</td><td>RM 216.00</td><td>RM 1,944.00</td><td><span style="color: #f4cb66;">Pending</span></td><td>-</td></tr>
                            <tr><td>#ST-1003</td><td>Faiz</td><td>2026-07-08 to 2026-07-11</td><td>4</td><td>RM 2,850.00</td><td>RM 285.00</td><td>RM 2,565.00</td><td><span style="color: #f4cb66;">Pending</span></td><td>-</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Commission Tab -->
            <div id="tab-commission" class="tab-content-panel" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="color: #f4cb66; margin: 0;"><i class="ri-percent-line"></i> Commission Ledger Records</h4>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportToExcel('commission-table-tab', 'Commission_Report')" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-excel-line"></i> Excel</button>
                        <button type="button" onclick="exportToPDF('commission-table-tab', 'Commission_Report')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-pdf-line"></i> PDF</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="commission-table-tab">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Homestay Info</th>
                                <th>Host Name</th>
                                <th>Gross Revenue</th>
                                <th>Voyago Cut (10%)</th>
                                <th>Host Payout (90%)</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>#1001</td><td>Sunset Valley Chalet</td><td>Faiz</td><td>RM 360.00</td><td>RM 36.00</td><td>RM 324.00</td><td>2026-07-01</td></tr>
                            <tr><td>#1002</td><td>Hillview Retreat</td><td>Ahmad Host</td><td>RM 540.00</td><td>RM 54.00</td><td>RM 486.00</td><td>2026-07-02</td></tr>
                            <tr><td>#1003</td><td>Ocean Breeze Villa</td><td>Faiz</td><td>RM 270.00</td><td>RM 27.00</td><td>RM 243.00</td><td>2026-07-04</td></tr>
                            <tr><td>#1004</td><td>Sunset Valley Chalet</td><td>Faiz</td><td>RM 360.00</td><td>RM 36.00</td><td>RM 324.00</td><td>2026-07-08</td></tr>
                            <tr><td>#1005</td><td>Kampung Style Homestay</td><td>Zol Host</td><td>RM 450.00</td><td>RM 45.00</td><td>RM 405.00</td><td>2026-07-09</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Reviews Tab -->
            <div id="tab-reviews" class="tab-content-panel" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="color: #f4cb66; margin: 0;"><i class="ri-message-3-line"></i> Traveller Reviews Moderation Report</h4>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportToExcel('reviews-table', 'Reviews_Report')" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-excel-line"></i> Excel</button>
                        <button type="button" onclick="exportToPDF('reviews-table', 'Reviews_Report')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-pdf-line"></i> PDF</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="reviews-table">
                        <thead>
                            <tr>
                                <th>Review ID</th>
                                <th>Homestay Name</th>
                                <th>Guest Name</th>
                                <th>Rating</th>
                                <th>Feedback Message</th>
                                <th>Posted Date</th>
                                <th>Visibility</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>#REV-01</td><td>Sunset Valley Chalet</td><td>Faiz Customer</td><td>⭐⭐⭐⭐⭐ (5)</td><td>Super clean and cozy homestay! Strongly recommend.</td><td>2026-07-03</td><td><span style="color: #10b981;">Visible</span></td></tr>
                            <tr><td>#REV-02</td><td>Hillview Retreat</td><td>Ahmad Yani</td><td>⭐⭐⭐⭐ (4)</td><td>A bit far from city but very peaceful environment.</td><td>2026-07-05</td><td><span style="color: #10b981;">Visible</span></td></tr>
                            <tr><td>#REV-03</td><td>Ocean Breeze Villa</td><td>Siti Nur</td><td>⭐⭐⭐⭐⭐ (5)</td><td>Beautiful beachfront view. Will come again next time!</td><td>2026-07-05</td><td><span style="color: #10b981;">Visible</span></td></tr>
                            <tr><td>#REV-04</td><td>Kampung Style Homestay</td><td>Wong Wei</td><td>⭐⭐⭐ (3)</td><td>A bit noisy during daytime. Amenities are standard.</td><td>2026-07-10</td><td><span style="color: #ef4444;">Hidden</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Registered Homestays Tab -->
            <div id="tab-homestays" class="tab-content-panel" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="color: #f4cb66; margin: 0;"><i class="ri-home-line"></i> Registered Homestays Directory</h4>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportToExcel('homestays-table', 'Registered_Homestays_Report')" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-excel-line"></i> Excel</button>
                        <button type="button" onclick="exportToPDF('homestays-table', 'Registered_Homestays_Report')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-pdf-line"></i> PDF</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="homestays-table">
                        <thead>
                            <tr>
                                <th>Homestay ID</th>
                                <th>Name</th>
                                <th>Host Name</th>
                                <th>State</th>
                                <th>Category</th>
                                <th>Price Per Night</th>
                                <th>Approval Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>#HS-01</td><td>Sunset Valley Chalet</td><td>Faiz</td><td>Terengganu</td><td>Chalet</td><td>RM 180.00</td><td><span style="color: #10b981;">Published</span></td></tr>
                            <tr><td>#HS-02</td><td>Hillview Retreat</td><td>Ahmad Host</td><td>Perak</td><td>Apartment</td><td>RM 180.00</td><td><span style="color: #10b981;">Published</span></td></tr>
                            <tr><td>#HS-03</td><td>Ocean Breeze Villa</td><td>Faiz</td><td>Selangor</td><td>Villa</td><td>RM 270.00</td><td><span style="color: #10b981;">Published</span></td></tr>
                            <tr><td>#HS-04</td><td>Kampung Style Homestay</td><td>Zol Host</td><td>Kedah</td><td>Other</td><td>RM 150.00</td><td><span style="color: #f4cb66;">Pending Approval</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Registered Users Tab -->
            <div id="tab-users" class="tab-content-panel" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="color: #f4cb66; margin: 0;"><i class="ri-user-line"></i> Registered System Users Directory</h4>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportToExcel('users-table', 'Registered_Users_Report')" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-excel-line"></i> Excel</button>
                        <button type="button" onclick="exportToPDF('users-table', 'Registered_Users_Report')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-pdf-line"></i> PDF</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="users-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Phone Number</th>
                                <th>Joined Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>#USR-01</td><td>Faiz Owner</td><td>faiz.owner@gmail.com</td><td>Owner</td><td>+60123456789</td><td>2026-01-15</td></tr>
                            <tr><td>#USR-02</td><td>Faiz Customer</td><td>faiz.customer@gmail.com</td><td>Tourist</td><td>+60176543210</td><td>2026-02-20</td></tr>
                            <tr><td>#USR-03</td><td>Ahmad Host</td><td>ahmad.host@gmail.com</td><td>Owner</td><td>+60199887766</td><td>2026-03-05</td></tr>
                            <tr><td>#USR-04</td><td>Admin User</td><td>admin@voyago.com</td><td>Admin</td><td>+60111222333</td><td>2026-01-01</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Monthly Reports Tab -->
            <div id="tab-monthly" class="tab-content-panel" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 style="color: #f4cb66; margin: 0;"><i class="ri-calendar-2-line"></i> Monthly Platform Performance Reports</h4>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="exportToExcel('monthly-table', 'Monthly_Performance_Report')" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-excel-line"></i> Excel</button>
                        <button type="button" onclick="exportToPDF('monthly-table', 'Monthly_Performance_Report')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;"><i class="ri-file-pdf-line"></i> PDF</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="monthly-table">
                        <thead>
                            <tr>
                                <th>Report Month</th>
                                <th>Total Bookings</th>
                                <th>Completed Bookings</th>
                                <th>Cancelled Bookings</th>
                                <th>Total Revenue</th>
                                <th>Total Commission (10%)</th>
                                <th>Total Host Payout (90%)</th>
                                <th>Average Occupancy Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>June 2026</td><td>18</td><td>15</td><td>3</td><td>RM 7,850.00</td><td>RM 785.00</td><td>RM 7,065.00</td><td>65.5%</td></tr>
                            <tr><td>July 2026 (MTD)</td><td>11</td><td>7</td><td>1</td><td>RM 6,180.00</td><td>RM 618.00</td><td>RM 5,562.00</td><td>72.0%</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        function switchReportTab(evt, tabId) {
            const panels = document.querySelectorAll('.tab-content-panel');
            panels.forEach(p => p.style.display = 'none');
            document.getElementById(tabId).style.display = 'block';
            
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'transparent';
                b.style.color = 'white';
                b.style.borderColor = 'transparent';
            });
            evt.currentTarget.classList.add('active');
            evt.currentTarget.style.background = 'rgba(255,255,255,0.05)';
            evt.currentTarget.style.color = '#f4cb66';
            evt.currentTarget.style.borderColor = 'rgba(255,255,255,0.15)';
        }

        function exportToExcel(tableId, filename) {
            const table = document.getElementById(tableId);
            let html = table.outerHTML;
            const dataUri = 'data:application/vnd.ms-excel;charset=utf-8,\uFEFF' + encodeURIComponent(html);
            const link = document.createElement('a');
            link.href = dataUri;
            link.download = filename + '_' + new Date().toISOString().slice(0,10) + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportToPDF(tableId, filename) {
            const printWindow = window.open('', '', 'height=600,width=800');
            const table = document.getElementById(tableId).outerHTML;
            printWindow.document.write('<html><head><title>' + filename + '</title>');
            printWindow.document.write('<style>body{font-family:sans-serif;padding:20px;background:#1a1a1a;color:#fff;} table{width:100%;border-collapse:collapse;margin-top:20px;color:#fff;} th,td{border:1px solid rgba(255,255,255,0.2);padding:10px;text-align:left;} th{background:#06402b;color:#f4cb66;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h2>Voyago System Report: ' + filename.replace(/_/g, ' ') + '</h2>');
            printWindow.document.write(table);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
        </script>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Global Canvas Configuration Override Options
        Chart.defaults.font.family = "'DM Sans', sans-serif";
        Chart.defaults.color = '#718096';

        // --- CHART 1: LINE CHART WITH GOLD GRADIENT ---
        const ctxRevenue = document.getElementById('chartRevenueTrend').getContext('2d');
        const gradientGold = ctxRevenue.createLinearGradient(0, 0, 0, 300);
        gradientGold.addColorStop(0, 'rgba(212, 175, 55, 0.25)');
        gradientGold.addColorStop(1, 'rgba(212, 175, 55, 0.0)');

        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($revenue_trends)); ?>,
                datasets: [{
                    label: 'Gross Vol (RM)',
                    data: <?php echo json_encode(array_values($revenue_trends)); ?>,
                    borderColor: '#d4af37',
                    pointBackgroundColor: '#06402b',
                    pointBorderColor: '#d4af37',
                    pointBorderWidth: 1.5,
                    backgroundColor: gradientGold,
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(6, 64, 43, 0.05)' } }
                }
            }
        });

        // --- CHART 2: BAR CHART WITH GREEN GRADIENT ---
        const ctxStates = document.getElementById('chartStatesSpread').getContext('2d');
        const gradientGreen = ctxStates.createLinearGradient(0, 0, 0, 300);
        gradientGreen.addColorStop(0, '#06402b');
        gradientGreen.addColorStop(1, '#0b5f40');

        new Chart(ctxStates, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($state_counts)); ?>,
                datasets: [{
                    label: 'Trips Created',
                    data: <?php echo json_encode(array_values($state_counts)); ?>,
                    backgroundColor: gradientGreen,
                    hoverBackgroundColor: '#d4af37',
                    borderRadius: 6
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(6, 64, 43, 0.05)' } }
                }
            }
        });

        // --- CHART 3: PREMIUM MATTE DOUGHNUT ---
        const ctxRatio = document.getElementById('chartStatusRatio').getContext('2d');
        new Chart(ctxRatio, {
            type: 'doughnut',
            data: {
                labels: ['Confirmed Booking', 'Pending Validation'],
                datasets: [{
                    data: [<?php echo $status_counts['confirmed']; ?>, <?php echo $status_counts['pending']; ?>],
                    backgroundColor: ['#06402b', '#e2e8f0'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } } },
                cutout: '70%'
            }
        });

        // --- CHART 4: COMPONENT ARCHITECTURE PIE CHART ---
        const ctxSplit = document.getElementById('chartRevenueSplit').getContext('2d');
        new Chart(ctxSplit, {
            type: 'pie',
            data: {
                labels: ['Hosts Share (90%)', '<?= htmlspecialchars($system_name) ?> Admin Fee (10%)'],
                datasets: [{
                    data: [<?php echo $host_payouts; ?>, <?php echo $voyago_commission; ?>],
                    backgroundColor: ['#06402b', '#d4af37'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } } }
            }
        });
    });
    </script>
    <script src="js/admin.js"></script>
</body>
</html>