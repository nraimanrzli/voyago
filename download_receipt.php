<?php
// download_receipt.php
session_start();
require_once('toyyibpay_config.php');

$is_booking_receipt = isset($_GET['booking_id']);

if ($is_booking_receipt) {
    // --- TRAVELER STAY BOOKING RECEIPT ---
    $booking_id = intval($_GET['booking_id']);
    
    $stmt = $pdo->prepare(
        "SELECT b.*, h.name as homestay_name, h.state, h.district, h.address,
                u.fullname as traveller_name, u.email as traveller_email,
                ph.billcode, ph.amount as paid_amount, ph.payment_date
         FROM bookings b
         JOIN homestays h ON b.homestay_id = h.id
         JOIN users u ON b.user_id = u.id
         LEFT JOIN payment_history ph ON ph.billcode = b.billcode
         WHERE b.id = ?"
    );
    $stmt->execute([$booking_id]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receipt) {
        die("Booking receipt not found.");
    }

    $receipt_type   = 'booking';
    $invoice_no     = 'BKG-2026-' . str_pad($booking_id, 5, '0', STR_PAD_LEFT);
    $item_label     = $receipt['homestay_name'] . ' — ' . $receipt['district'] . ', ' . $receipt['state'];
    $item_sub       = 'Check-in: ' . date('d M Y', strtotime($receipt['check_in'])) . ' | Check-out: ' . date('d M Y', strtotime($receipt['check_out'])) . ' | Guests: ' . ($receipt['guests'] ?? 1);
    $total_amount   = $receipt['paid_amount'] ?? $receipt['total_price'];
    $billcode_disp  = $receipt['billcode'] ?? 'N/A';
    $payment_date   = $receipt['payment_date'] ?? $receipt['created_at'] ?? date('Y-m-d H:i:s');
    $buyer_name     = $receipt['traveller_name'];
    $buyer_email    = $receipt['traveller_email'];

} else {
    // --- HOST LISTING ACTIVATION RECEIPT ---
    if (!isset($_GET['id'])) {
        die("Invalid request");
    }
    $homestay_id = intval($_GET['id']);

    $stmt = $pdo->prepare(
        "SELECT h.*, p.billcode, p.amount, p.payment_date, u.fullname as owner_name, u.email as owner_email
         FROM homestays h
         LEFT JOIN payment_history p ON h.id = p.homestay_id
         LEFT JOIN users u ON h.user_id = u.id
         WHERE h.id = ?"
    );
    $stmt->execute([$homestay_id]);
    $receipt = $stmt->fetch();

    if (!$receipt) {
        die("Receipt not found");
    }

    if ($receipt['payment_status'] !== 'Paid') {
        die("Receipt not available. Payment has not been completed.");
    }

    $receipt_type  = 'listing';
    $invoice_no    = 'VYG-2026-' . str_pad($homestay_id, 5, '0', STR_PAD_LEFT);
    $item_label    = $receipt['name'] . ' — Listing Activation';
    $item_sub      = 'Premium Annual Fee (1 Year)';
    $total_amount  = $receipt['amount'] ?? 29.00;
    $billcode_disp = $receipt['billcode'] ?? 'N/A';
    $payment_date  = $receipt['payment_date'] ?? date('Y-m-d H:i:s');
    $buyer_name    = $receipt['owner_name'] ?? 'Host Manager';
    $buyer_email   = $receipt['owner_email'] ?? '';
}

$dashboard_link = "index.php";
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'Admin') {
        $dashboard_link = "admindashboard.php";
    } elseif ($_SESSION['user_role'] === 'Traveller/User') {
        $dashboard_link = $is_booking_receipt ? "booking.php" : "dashboard.php";
    } elseif ($_SESSION['user_role'] === 'Local Homestay Owner') {
        $dashboard_link = "localhomestay.php";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt #VYG-2026-00<?php echo $receipt['id']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f4f7f6; padding: 40px; margin: 0; }
        .receipt-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px dashed #ddd; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2c3e50; font-family: 'Playfair Display', serif; }
        .header p { color: #7f8c8d; margin: 5px 0; }
        .details { margin-bottom: 30px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px solid #f1f1f1; padding-bottom: 8px; }
        .details-row strong { color: #34495e; text-align: right; }
        .total-row { display: flex; justify-content: space-between; margin-top: 20px; font-size: 1.2rem; font-weight: bold; color: #2c3e50; border-top: 2px solid #2c3e50; padding-top: 15px; }
        .footer { text-align: center; margin-top: 40px; color: #95a5a6; font-size: 0.9rem; }
        @media print {
            body { background: white; padding: 0; }
            .receipt-container { box-shadow: none; border: none; padding: 20px; }
            .btn-print, .btn-back { display: none !important; }
        }
        .btn-wrapper { display: flex; gap: 10px; margin-top: 30px; }
        .btn-print { flex: 1; text-align: center; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; text-decoration: none; font-weight: bold; }
        .btn-print:hover { background: #2ecc71; }
        .btn-back { flex: 1; text-align: center; padding: 12px; background: #ecf0f1; color: #2c3e50; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; text-decoration: none; font-weight: bold; }
        .btn-back:hover { background: #bdc3c7; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1><?= htmlspecialchars($system_name) ?></h1>
            <p><?php echo $receipt_type === 'booking' ? 'Official Booking Confirmation Receipt' : 'Official Listing Activation Receipt'; ?></p>
            <p style="font-size: 0.85rem;"><i class="ri-calendar-line"></i> Date: <?php echo date('d M Y, h:i A', strtotime($payment_date)); ?></p>
        </div>
        
        <div class="details">
            <div class="details-row">
                <span>Invoice Number:</span>
                <strong>#<?php echo htmlspecialchars($invoice_no); ?></strong>
            </div>
            <div class="details-row">
                <span><?php echo $receipt_type === 'booking' ? 'Property / Stay:' : 'Homestay Name:'; ?></span>
                <strong><?php echo htmlspecialchars($item_label); ?></strong>
            </div>
            <div class="details-row">
                <span><?php echo $receipt_type === 'booking' ? 'Booking Details:' : 'Plan:'; ?></span>
                <strong><?php echo htmlspecialchars($item_sub); ?></strong>
            </div>
            <div class="details-row">
                <span>Guest / Customer:</span>
                <strong><?php echo htmlspecialchars($buyer_name); ?> <?php echo $buyer_email ? '&lt;' . htmlspecialchars($buyer_email) . '&gt;' : ''; ?></strong>
            </div>
            <div class="details-row">
                <span>Payment Status:</span>
                <strong style="color: #27ae60;"><i class="ri-checkbox-circle-fill"></i> PAID</strong>
            </div>
            <?php if (!empty($billcode_disp) && $billcode_disp !== 'N/A'): ?>
            <div class="details-row">
                <span>ToyyibPay Billcode:</span>
                <strong><?php echo htmlspecialchars($billcode_disp); ?></strong>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="total-row">
            <span>Total Paid</span>
            <span>RM <?php echo number_format($total_amount, 2); ?></span>
        </div>
        
        <div class="btn-wrapper">
            <a href="<?php echo htmlspecialchars($dashboard_link); ?>" class="btn-back"><i class="ri-arrow-left-line"></i> Back to Dashboard</a>
            <button class="btn-print" onclick="window.print()"><i class="ri-printer-line"></i> Print Receipt</button>
        </div>
        
        <div class="footer">
            Thank you for partnering with <?= htmlspecialchars($system_name) ?>!<br>
            If you have any questions, please contact support@<?= htmlspecialchars($system_name) ?>.com
        </div>
    </div>
    
    <script>
        // Automatically print when page loads
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
