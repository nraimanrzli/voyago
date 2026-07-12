<?php
// toyyibpay_return.php
require_once('toyyibpay_config.php');

// Catch response status
$status_id = isset($_REQUEST['status_id']) ? intval($_REQUEST['status_id']) : 0;
$billcode  = isset($_REQUEST['billcode']) ? trim($_REQUEST['billcode']) : '';

// Retrieve references
if (isset($_REQUEST['order_no'])) {
    $homestay_id = intval($_REQUEST['order_no']);
} elseif (isset($_REQUEST['order_id'])) {
    $homestay_id = intval($_REQUEST['order_id']);
} else {
    $homestay_id = 0;
}

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : 'host';

// Process successful payment confirmations (status_id == 1)
if ($status_id == 1 && $homestay_id > 0) {
    
    if ($type === 'booking') {
        // Traveler Stay Booking payment
        $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : $homestay_id;
        
        if ($booking_id > 0) {
            // Update booking to Confirmed & Paid
            $upd = $pdo->prepare("UPDATE bookings SET status = 'Confirmed', booking_status = 'Confirmed', payment_status = 'Paid', billcode = ? WHERE id = ?");
            $upd->execute([$billcode, $booking_id]);

            // Mark installment 1 as paid if this is an installment plan booking
            $inst_chk = $pdo->prepare("SELECT id FROM booking_installments WHERE booking_id = ? AND installment_no = 1 AND status = 'Pending'");
            $inst_chk->execute([$booking_id]);
            if ($inst_chk->fetch()) {
                $pdo->prepare("UPDATE booking_installments SET status='Paid', paid_date=NOW() WHERE booking_id=? AND installment_no=1")->execute([$booking_id]);
            }

            
            // Get booking price to log as expense
            $b_stmt = $pdo->prepare("SELECT homestay_id, total_price FROM bookings WHERE id = ?");
            $b_stmt->execute([$booking_id]);
            $b_data = $b_stmt->fetch(PDO::FETCH_ASSOC);
            $booking_price = $b_data ? $b_data['total_price'] : 0;
            $actual_homestay_id = $b_data ? $b_data['homestay_id'] : $homestay_id;
            
            // Log as Accommodation Expense in traveler Budget (keyed by booking_id)
            $exp = $pdo->prepare("INSERT INTO trip_expenses (homestay_id, title, category, amount, payer) 
                                 VALUES (?, 'Stay Booking Payment', 'Accommodation', ?, 'Online Banking (ToyyibPay)')");
            $exp->execute([$booking_id, $booking_price]);
            
            // Insert booking receipt link into trip_documents wallet (keyed by booking_id)
            $receipt_link = "download_receipt.php?booking_id=" . $booking_id;
            $receipt_label = "Booking_Receipt_" . ($billcode ?: $booking_id);
            $doc = $pdo->prepare("INSERT INTO trip_documents (homestay_id, doc_type, file_path) 
                                 VALUES (?, 'Booking Receipt', ?)");
            $doc->execute([$booking_id, $receipt_link]);
            
            // Add notification to Host Owner that their stay has been booked!
            $h_owner_stmt = $pdo->prepare("SELECT user_id, name FROM homestays WHERE id = ?");
            $h_owner_stmt->execute([$actual_homestay_id]);
            $h_owner_data = $h_owner_stmt->fetch(PDO::FETCH_ASSOC);
            if ($h_owner_data) {
                $owner_msg = "You have received a new booking reservation for '" . $h_owner_data['name'] . "' (Booking #" . $booking_id . ").";
                $n_ins = $pdo->prepare("INSERT INTO owner_notifications (owner_id, message) VALUES (?, ?)");
                $n_ins->execute([$h_owner_data['user_id'], $owner_msg]);
            }
        }
        
        // Add to payment_history table
        $check = $pdo->prepare("SELECT id FROM payment_history WHERE billcode = ?");
        $check->execute([$billcode]);
        if (!$check->fetch()) {
            $ins_pay = $pdo->prepare("INSERT INTO payment_history (user_id, homestay_id, billcode, amount, status) VALUES (?, ?, ?, ?, 'Paid')");
            $final_amount = isset($booking_price) ? $booking_price : 0.00;
            $ins_pay->execute([$user_id, $actual_homestay_id, $billcode, $final_amount]); 
        }
        
        // Redirect traveler to their Booking History Ledger page
        header("Location: booking.php?payment=success");
        exit;
    } else {
        // Host activation Listing Registration Fee (RM29)
        // 1. Update listing payment statuses & approval stage
        $up = $pdo->prepare("UPDATE homestays SET payment_status = 'Paid', listing_fee_status = 'Paid', approval_status = 'Pending Approval' WHERE id = ?");
        $up->execute([$homestay_id]);

        // 2. Add history record
        $stmt_h = $pdo->prepare("SELECT user_id, name FROM homestays WHERE id = ?");
        $stmt_h->execute([$homestay_id]);
        $h_data = $stmt_h->fetch(PDO::FETCH_ASSOC);
        
        $owner_id = $h_data ? intval($h_data['user_id']) : (isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0);
        $homestay_name = $h_data ? $h_data['name'] : 'Premium Homestay';

        $check = $pdo->prepare("SELECT id FROM payment_history WHERE billcode = ?");
        $check->execute([$billcode]);
        if (!$check->fetch()) {
            $ins_pay = $pdo->prepare("INSERT INTO payment_history (user_id, homestay_id, billcode, amount, status) VALUES (?, ?, ?, ?, 'Paid')");
            $ins_pay->execute([$owner_id, $homestay_id, $billcode, 29.00]);
        }

        // 3. Generate simulation receipt document
        $to_email = "nurinbatrisya15@gmail.com"; 
        $subject = "$system_name Payment Received - Homestay Listing Activation";
        
        $message = "==================================================\n";
        $message .= "             " . strtoupper($system_name) . " ENTERPRISE RECEIPT            \n";
        $message .= "==================================================\n";
        $message .= "Hi Host Manager,\n\n";
        $message .= "Payment of RM29.00 for annual listing fee was SUCCESSFUL.\n\n";
        $message .= "Transaction reference metadata:\n";
        $message .= "--------------------------------------------------\n";
        $message .= "Bill Code       : " . $billcode . "\n";
        $message .= "Homestay ID     : #" . $homestay_id . "\n";
        $message .= "Property Name   : " . $homestay_name . "\n";
        $message .= "Amount Paid     : RM 29.00\n";
        $message .= "Payment Channel : FPX Online Banking\n";
        $message .= "Moderation Stage: Pending Approval (Awaiting Admin validation review)\n";
        $message .= "--------------------------------------------------\n";
        $message .= "System confirmation log timestamp: " . date('Y-m-d H:i:s') . "\n";
        $message .= "==================================================\n";
        
        $file_name = "email_receipt_" . $billcode . ".txt";
        file_put_contents($file_name, $message);

        // Redirect Host to their Listing Dashboard
        header("Location: localhomestay.php?payment=success");
        exit;
    }
} else {
    // Failures
    if ($type === 'booking') {
        header("Location: booking.php?payment=failed");
    } else {
        header("Location: localhomestay.php?payment=failed");
    }
    exit;
}
?>