<?php
// export_settlement.php
require_once 'toyyibpay_config.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = intval($_SESSION['user_id']);

// Fetch owner bank statement transaction history for completed paid bookings
$stmt = $pdo->prepare(
    "SELECT b.booking_no, b.created_at AS date, b.total_price AS gross_amount,
            h.name AS homestay_name, r.room_name, u.fullname AS guest_name,
            IFNULL(s.created_at, '') AS transfer_date,
            b.settlement_id
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
$stmt->execute([$user_id]);
$settlements = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"statement_bill_owner_$user_id.xls\"");
header("Pragma: no-cache");
header("Expires: 0");

$commission_rate = 0.10;

echo "Booking No\tHomestay\tRoom\tGuest Name\tDate\tGross Amount (RM)\tCommission 10% (RM)\tNet Earnings (RM)\tStatus\tTransfer Date\n";

foreach ($settlements as $row) {
    $gross = (float)$row['gross_amount'];
    $commission = round($gross * $commission_rate, 2);
    $net = round($gross - $commission, 2);
    $status = empty($row['settlement_id']) ? 'Pending' : 'Transferred';
    $transfer_date = $row['transfer_date'] ? date('Y-m-d H:i', strtotime($row['transfer_date'])) : '-';
    $room = $row['room_name'] ? $row['room_name'] : '-';
    $booking_no = $row['booking_no'] ?: 'BKG-' . str_pad(0, 4, '0', STR_PAD_LEFT);
    echo "{$booking_no}\t{$row['homestay_name']}\t{$room}\t{$row['guest_name']}\t{$row['date']}\t" . number_format($gross, 2) . "\t" . number_format($commission, 2) . "\t" . number_format($net, 2) . "\t{$status}\t{$transfer_date}\n";
}
?>
