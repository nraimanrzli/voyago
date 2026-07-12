<?php
// toyyibpay_process.php
require_once('toyyibpay_config.php');

if (!isset($_SESSION['user_id'])) {
    die("Access not allowed. Please log in again.");
}

if (!isset($_GET['homestay_id'])) {
    die("Error: The homestay_id parameter is required.");
}

$homestay_id = intval($_GET['homestay_id']);
$user_id = intval($_SESSION['user_id']);

// 1. Fetch homestay details
$stmt = $pdo->prepare("SELECT * FROM homestays WHERE id = ?");
$stmt->execute([$homestay_id]);
$homestay = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$homestay) {
    die("The homestay record does not exist in the system.");
}

// 2. Fetch payor name and email from DB
$stmt_user = $pdo->prepare("SELECT fullname, email FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

$payor_name = !empty($user_data['fullname']) ? $user_data['fullname'] : (!empty($_SESSION['user_fullname']) ? $_SESSION['user_fullname'] : $system_name . ' Customer');
$payor_email = !empty($user_data['email']) ? $user_data['email'] : 'nurinbatrisya15@gmail.com';

// 3. Process Payment Details
if (isset($_GET['total_amount_cents']) && intval($_GET['total_amount_cents']) > 0) {
    $bill_amount = intval($_GET['total_amount_cents']);
    $bill_name = $system_name . ' Stay Booking Payment';
    $bill_desc = 'Booking payment for Stay at ' . $homestay['name'];
    $return_url = BASE_URL . 'toyyibpay_return.php?type=booking';
    $callback_url = BASE_URL . 'toyyibpay_return.php?type=booking';
    
    // Dates
    $check_in = isset($_GET['check_in']) ? trim($_GET['check_in']) : null;
    $check_out = isset($_GET['check_out']) ? trim($_GET['check_out']) : null;
    $guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;
    $room_id = isset($_GET['room_id']) && !empty($_GET['room_id']) ? intval($_GET['room_id']) : null;
    
    $total_price_rm = $bill_amount / 100;
    
    if ($check_in && $check_out) {
        // Enforce final availability overlap check
        if ($room_id) {
            $check_overlap = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                                           WHERE homestay_id = ? 
                                           AND booking_status != 'Cancelled' 
                                           AND (room_id = ? OR room_id IS NULL) 
                                           AND check_in < ? 
                                           AND check_out > ?");
            $check_overlap->execute([$homestay_id, $room_id, $check_out, $check_in]);
        } else {
            $check_overlap = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                                           WHERE homestay_id = ? 
                                           AND booking_status != 'Cancelled' 
                                           AND check_in < ? 
                                           AND check_out > ?");
            $check_overlap->execute([$homestay_id, $check_out, $check_in]);
        }
        
        if ($check_overlap->fetchColumn() > 0) {
            die("Error: The selected stay dates have already been booked by another guest. Please review your selection.");
        }

        // Check if any date in range is owner-blocked
        $blk_chk = $pdo->prepare(
            "SELECT COUNT(*) FROM homestay_blocked_dates 
             WHERE homestay_id = ? AND blocked_date >= ? AND blocked_date < ?"
        );
        $blk_chk->execute([$homestay_id, $check_in, $check_out]);
        if ($blk_chk->fetchColumn() > 0) {
            die("Error: One or more of your selected dates are not available — the host has blocked those dates. Please choose different dates.");
        }

        // Generate unique booking number
        $booking_no = 'VYG-BKG-' . date('Ymd') . '-' . rand(1000, 9999);

        // Insert booking with status Pending, booking_status Confirmed (awaiting payment settlement)
        $ins_booking = $pdo->prepare("INSERT INTO bookings 
            (booking_no, user_id, homestay_id, room_id, check_in, check_out, guests, total_price, status, payment_status, booking_status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending', 'Confirmed', NOW())");
        
        $ins_booking->execute([$booking_no, $user_id, $homestay_id, $room_id, $check_in, $check_out, $guests, $total_price_rm]);
        $booking_id = $pdo->lastInsertId();

        // Set booking payment plan to full payment only
        $payment_plan = 'full';
        $pdo->prepare("UPDATE bookings SET payment_plan = ? WHERE id = ?")->execute([$payment_plan, $booking_id]);

        $return_url .= '&booking_id=' . $booking_id . '&order_no=' . $booking_id;
        $callback_url .= '&booking_id=' . $booking_id . '&order_no=' . $booking_id;
    } else {
        die("Error: Check-in and check-out dates are required.");
    }
} else {
    // Host RM29 Listing registration fee activation
    $bill_amount = 2900; 
    $bill_name = $system_name . ' Premium Listing Fee';
    $bill_desc = 'Payment for activating Homestay ID #' . $homestay_id;
    $return_url = BASE_URL . 'toyyibpay_return.php?type=host';
    $callback_url = BASE_URL . 'toyyibpay_return.php?type=host';
}

// Prepare data to send to ToyyibPay API
$some_data = array(
    'userSecretKey' => TOYYIBPAY_SECRET_KEY,
    'categoryCode' => TOYYIBPAY_CATEGORY_CODE,
    'billName' => $bill_name,
    'billDescription' => $bill_desc,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => $bill_amount, 
    'billReturnUrl' => $return_url,
    'billCallbackUrl' => $callback_url, 
    'billExternalReferenceNo' => (string)$homestay_id,
    'billTo' => $payor_name, 
    'billEmail' => $payor_email, 
    'billPhone' => '0123446158',
    'billSplitPayment' => 0,
    'billSplitPaymentArgs' => '',
    'billPaymentChannel' => '0', 
    'billContentHtml' => '',
    'billChargeToCustomer' => 1
);  

// Send request using cURL
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, TOYYIBPAY_URL . 'index.php/api/createBill');  
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
$result = curl_exec($curl);
$info = curl_getinfo($curl);  
curl_close($curl);

$bill = json_decode($result, true);

if (isset($bill[0]['BillCode'])) {
    $billcode = $bill[0]['BillCode'];
    header("Location: " . TOYYIBPAY_URL . $billcode);
    exit;
} else {
    // Fallback Mock Payment confirmation link for local testing if API offline
    if (isset($booking_id)) {
        // Traveler stay booking mock
        header("Location: toyyibpay_return.php?status_id=1&billcode=MOCKBKG" . $booking_id . "&order_no=" . $booking_id . "&type=booking&booking_id=" . $booking_id);
    } else {
        // Host listing fee mock
        header("Location: toyyibpay_return.php?status_id=1&billcode=MOCKTP" . $homestay_id . "&order_no=" . $homestay_id . "&type=host");
    }
    exit;
}
?>
