<?php
// wishlist_handler.php
require_once('toyyibpay_config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. GET ALL WISHLIST ITEMS FOR LOGGED USER
if ($action == 'fetch') {
    $stmt = $pdo->prepare("SELECT w.homestay_id, h.name, h.price_per_night, h.state, h.image_url 
                           FROM wishlist w 
                           JOIN homestays h ON w.homestay_id = h.id 
                           WHERE w.user_id = ?");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $items]);
    exit();
}

// 2. TOGGLE WISHLIST (ADD / REMOVE)
if ($action == 'toggle') {
    $data = json_decode(file_get_contents('php://input'), true);
    $homestay_id = isset($data['homestay_id']) ? intval($data['homestay_id']) : 0;

    if ($homestay_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        exit();
    }

    $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND homestay_id = ?");
    $check->execute([$user_id, $homestay_id]);
    
    if ($check->fetch()) {
        // Dah wujud, buang dari wishlist
        $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND homestay_id = ?");
        $del->execute([$user_id, $homestay_id]);
        echo json_encode(['status' => 'removed']);
    } else {
        // Belum wujud, tambah baru
        $ins = $pdo->prepare("INSERT INTO wishlist (user_id, homestay_id) VALUES (?, ?)");
        $ins->execute([$user_id, $homestay_id]);
        echo json_encode(['status' => 'added']);
    }
    exit();
}