<?php
// view_report.php - Monthly Performance Report Audit Page
require_once 'toyyibpay_config.php';

// Verify session — redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($report_id <= 0) {
    die("Invalid report ID.");
}

$stmt = $pdo->prepare("
    SELECT r.*, u.fullname as owner_name, u.email as owner_email 
    FROM owner_reports r 
    JOIN users u ON r.owner_id = u.id 
    WHERE r.id = ?
");
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    die("Report not found. It may not have been generated yet.");
}

// Security: use strict integer cast to avoid type-mismatch
$session_user_id   = intval($_SESSION['user_id']);
$session_user_role = $_SESSION['user_role'] ?? '';
$report_owner_id   = intval($report['owner_id']);

if ($session_user_role !== 'Admin' && $session_user_id !== $report_owner_id) {
    die("Access not allowed. This report belongs to a different host account.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> Monthly Audit Report — <?php echo htmlspecialchars($report['report_month']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            color: #1e293b;
            background: #ffffff;
            margin: 40px;
            line-height: 1.5;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .report-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: #0f172a;
            margin: 0;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .metric-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .metric-table th, .metric-table td {
            border: 1px solid #cbd5e1;
            padding: 12px 16px;
            text-align: left;
        }
        .metric-table th {
            background: #f8fafc;
            color: #0f172a;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
        }
        .summary-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .payout-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.1rem;
        }
        .payout-amount {
            font-size: 1.8rem;
            font-weight: 800;
            color: #16a34a;
        }
        .footer-note {
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 50px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="report-header">
        <div>
            <h1><?= htmlspecialchars($system_name) ?></h1>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 4px; margin-bottom: 0;">Verified Homestay Operations Auditor</p>
        </div>
        <div style="text-align: right;">
            <h2 style="margin:0; font-size:1.3rem; color: #0f172a;">Monthly Performance Review</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0; font-weight:700;"><?php echo htmlspecialchars($report['report_month']); ?></p>
        </div>
    </div>

    <div class="meta-grid">
        <div>
            <h3 style="margin-top:0; color:#0f172a; margin-bottom: 8px;">Host Information</h3>
            <p style="margin: 4px 0;"><strong>Name:</strong> <?php echo htmlspecialchars($report['owner_name']); ?></p>
            <p style="margin: 4px 0;"><strong>Email:</strong> <?php echo htmlspecialchars($report['owner_email']); ?></p>
            <p style="margin: 4px 0;"><strong>Audited Month:</strong> <?php echo htmlspecialchars($report['report_month']); ?></p>
        </div>
        <div style="text-align: right;">
            <h3 style="margin-top:0; color:#0f172a; margin-bottom: 8px;">Platform Commission</h3>
            <p style="margin: 4px 0;"><strong>Standard rate:</strong> 10% Flat Rate</p>
            <p style="margin: 4px 0;"><strong>Commission Collected:</strong> RM <?php echo number_format($report['commission'], 2); ?></p>
            <p style="margin: 4px 0;"><strong>Settlement:</strong> Direct Bank Transfer (FPX/ToyyibPay)</p>
        </div>
    </div>

    <table class="metric-table">
        <thead>
            <tr>
                <th>Performance Indicator</th>
                <th>Audited Value</th>
                <th>Context / Reference</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Bookings Scheduled</strong></td>
                <td><?php echo $report['bookings_count']; ?> Stay Reservations</td>
                <td>Total volume during timeframe</td>
            </tr>
            <tr>
                <td><strong>Completed Bookings</strong></td>
                <td><?php echo $report['completed_count']; ?> Bookings</td>
                <td>Stays successfully ended</td>
            </tr>
            <tr>
                <td><strong>Cancelled Bookings</strong></td>
                <td><?php echo $report['cancelled_count']; ?> Bookings</td>
                <td>Refunded/Cancelled by traveler</td>
            </tr>
            <tr>
                <td><strong>Average Rating</strong></td>
                <td>⭐⭐⭐⭐⭐ <?php echo number_format($report['avg_rating'], 2); ?> / 5 Stars</td>
                <td>Aggregate review rating score</td>
            </tr>
            <tr>
                <td><strong>Customer Reviews Posted</strong></td>
                <td><?php echo $report['reviews_count']; ?> Reviews</td>
                <td>User feedback responses logged</td>
            </tr>
            <tr>
                <td><strong>Most Popular Homestay</strong></td>
                <td><?php echo htmlspecialchars($report['popular_homestay']); ?></td>
                <td>Highest booking volume property</td>
            </tr>
            <tr>
                <td><strong>Average Occupancy Rate</strong></td>
                <td><?php echo number_format($report['occupancy_rate'], 2); ?>%</td>
                <td>Monthly room nights utilization</td>
            </tr>
            <tr style="background: #f8fafc;">
                <td><strong>Gross Sales volume</strong></td>
                <td style="font-weight:700; color:#0f172a;">RM <?php echo number_format($report['total_revenue'], 2); ?></td>
                <td>Platform bookings transaction sum</td>
            </tr>
        </tbody>
    </table>

    <div class="summary-box">
        <div>
            <span class="payout-title">Total Host Settlement Payout (90%)</span>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.85rem;">Calculated net of standard 10% platform processing service fee.</p>
        </div>
        <div class="payout-amount">
            RM <?php echo number_format($report['earnings'], 2); ?>
        </div>
    </div>

    <div class="no-print" style="margin-top:30px; display:flex; gap:12px; justify-content:flex-end;">
        <a href="localhomestay.php" style="padding: 10px 20px; font-weight:700; background:#e2e8f0; color:#0f172a; border:none; border-radius:6px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">&#8592; Back to Dashboard</a>
        <button onclick="window.print()" style="padding: 10px 20px; font-weight:700; background:#0f172a; color:white; border:none; border-radius:6px; cursor:pointer;">&#128438; Print / Save as PDF</button>
    </div>

    <div class="footer-note">
        This document represents an official automated operations report generated by <?= htmlspecialchars($system_name) ?> travel platform.<br>
        Copyright &copy; 2026 <?= htmlspecialchars($system_name) ?>. All rights reserved.
    </div>

    <script>
        // Only auto-trigger print if ?print=1 is in URL
        window.onload = function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('print') === '1') {
                window.print();
            }
        };
    </script>
</body>
</html>
