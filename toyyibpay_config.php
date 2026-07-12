<?php
// toyyibpay_config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ToyyibPay Sandbox Credentials
define('TOYYIBPAY_SECRET_KEY', '69b5c80d-xt2a-2yvt-df4d-bu1oiw4oco3s'); 
define('TOYYIBPAY_CATEGORY_CODE', 'forvu007');
define('TOYYIBPAY_URL', 'https://dev.toyyibpay.com/'); 

// Alamat Server Root Tempatan Anda
define('BASE_URL', 'http://localhost/voyago/'); 

// Sambungan Database PDO Global
$host = 'localhost'; $db = 'voyago'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // --- AUTOMATED DATABASE SCHEMA MIGRATIONS ---
    // 1. Alter homestays table if needed
    $columns = $pdo->query("DESCRIBE homestays")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('pricing_type', $columns)) {
        $pdo->exec("ALTER TABLE homestays ADD COLUMN `pricing_type` ENUM('Whole House', 'Per Room') DEFAULT 'Whole House'");
    }
    if (!in_array('total_rooms', $columns)) {
        $pdo->exec("ALTER TABLE homestays ADD COLUMN `total_rooms` INT DEFAULT 1");
    }
    if (!in_array('main_image', $columns)) {
        $pdo->exec("ALTER TABLE homestays ADD COLUMN `main_image` VARCHAR(255) NULL");
    }
    if (!in_array('facility_images', $columns)) {
        try { $pdo->exec("ALTER TABLE homestays ADD COLUMN `facility_images` TEXT NULL"); } catch (PDOException $e) { /* already exists */ }
    }
    if (!in_array('listing_fee_status', $columns)) {
        try { $pdo->exec("ALTER TABLE homestays ADD COLUMN `listing_fee_status` ENUM('Unpaid', 'Paid') DEFAULT 'Unpaid'"); } catch (PDOException $e) { /* already exists */ }
    }
    // Modify approval_status ENUM values
    $pdo->exec("ALTER TABLE homestays MODIFY COLUMN `approval_status` ENUM('Registered', 'Pending Approval', 'Published', 'Rejected', 'Draft', 'Live', 'Approved') DEFAULT 'Registered'");

    // 2. Create homestay_rooms table
    $pdo->exec("CREATE TABLE IF NOT EXISTS homestay_rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        homestay_id INT NOT NULL,
        room_name VARCHAR(100) NOT NULL,
        price_modifier DECIMAL(10,2) DEFAULT 0.00,
        status ENUM('Available', 'Booked') DEFAULT 'Available'
    ) ENGINE=InnoDB;");

    $room_cols = $pdo->query("DESCRIBE homestay_rooms")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('room_type', $room_cols)) {
        try { $pdo->exec("ALTER TABLE homestay_rooms ADD COLUMN `room_type` VARCHAR(100) DEFAULT 'Double Room'"); } catch (PDOException $e) {}
    }
    if (!in_array('price_per_night', $room_cols)) {
        try { $pdo->exec("ALTER TABLE homestay_rooms ADD COLUMN `price_per_night` DECIMAL(10,2) DEFAULT 150.00"); } catch (PDOException $e) {}
    }
    if (!in_array('max_guests', $room_cols)) {
        try { $pdo->exec("ALTER TABLE homestay_rooms ADD COLUMN `max_guests` INT DEFAULT 2"); } catch (PDOException $e) {}
    }


    // 3. Alter bookings table if needed
    $b_columns = $pdo->query("DESCRIBE bookings")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('booking_no', $b_columns)) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN `booking_no` VARCHAR(50) NULL");
    }
    if (!in_array('room_id', $b_columns)) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN `room_id` INT NULL");
    }
    if (!in_array('booking_status', $b_columns)) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN `booking_status` ENUM('Confirmed', 'Cancelled', 'Completed') DEFAULT 'Confirmed'");
    }
    if (!in_array('total_budget', $b_columns)) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN `total_budget` DECIMAL(10,2) DEFAULT 2000.00");
    }
    if (!in_array('finished_at', $b_columns)) {
        try { $pdo->exec("ALTER TABLE bookings ADD COLUMN `finished_at` DATETIME NULL"); } catch (PDOException $e) { /* already exists */ }
    }
    if (!in_array('payment_plan', $b_columns)) {
        try { $pdo->exec("ALTER TABLE bookings ADD COLUMN `payment_plan` ENUM('full','installment') DEFAULT 'full'"); } catch (PDOException $e) { /* already exists */ }
    }

    // 3b. Create booking_installments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS booking_installments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        user_id INT NOT NULL,
        installment_no TINYINT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        due_date DATE NOT NULL,
        paid_date DATETIME NULL,
        status ENUM('Pending','Paid','Overdue') DEFAULT 'Pending',
        billcode VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_booking_inst (booking_id, installment_no)
    ) ENGINE=InnoDB;");

    // 4. Create homestay_reviews table
    $pdo->exec("CREATE TABLE IF NOT EXISTS homestay_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        homestay_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT CHECK (rating BETWEEN 1 AND 5),
        feedback_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    $r_columns = $pdo->query("DESCRIBE homestay_reviews")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_hidden', $r_columns)) {
        $pdo->exec("ALTER TABLE homestay_reviews ADD COLUMN `is_hidden` TINYINT(1) DEFAULT 0");
    }
    if (!in_array('admin_reply', $r_columns)) {
        $pdo->exec("ALTER TABLE homestay_reviews ADD COLUMN `admin_reply` TEXT NULL");
    }

    // 5. Create owner_notifications table
    $pdo->exec("CREATE TABLE IF NOT EXISTS owner_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 6. Create owner_reports table
    $pdo->exec("CREATE TABLE IF NOT EXISTS owner_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        report_month VARCHAR(20) NOT NULL,
        bookings_count INT DEFAULT 0,
        completed_count INT DEFAULT 0,
        cancelled_count INT DEFAULT 0,
        total_revenue DECIMAL(10,2) DEFAULT 0.00,
        commission DECIMAL(10,2) DEFAULT 0.00,
        earnings DECIMAL(10,2) DEFAULT 0.00,
        avg_rating DECIMAL(3,2) DEFAULT 0.00,
        reviews_count INT DEFAULT 0,
        popular_homestay VARCHAR(255) DEFAULT '',
        occupancy_rate DECIMAL(5,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 7. Create system_settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB;");

    // 8. Ensure homestays table has document upload columns
    $h_cols = $pdo->query("DESCRIBE homestays")->fetchAll(PDO::FETCH_COLUMN);
    $doc_cols = ['ic_copy', 'utility_bill', 'ssm_doc', 'business_license', 'ownership_proof'];
    foreach ($doc_cols as $dc) {
        if (!in_array($dc, $h_cols)) {
            try { $pdo->exec("ALTER TABLE homestays ADD COLUMN `{$dc}` VARCHAR(500) DEFAULT ''"); } catch (PDOException $e) { /* already exists */ }
        }
    }

    // Initialize system name if it doesn't exist
    $sys_stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'system_name'");
    $sys_stmt->execute();
    $sys_res = $sys_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sys_res) {
        $pdo->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES ('system_name', 'Voyago')");
        $system_name = 'Voyago';
    } else {
        $system_name = $sys_res['setting_value'];
    }

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>