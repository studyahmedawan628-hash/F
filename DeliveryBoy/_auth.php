<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

function requireDeliveryBoyAuth(): void {
    if (!isset($_SESSION['delivery_logged_in']) || $_SESSION['delivery_logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
}

function deliveryBoySessionUser(): array {
    return [
        'id' => (int)($_SESSION['delivery_boy_id'] ?? 0),
        'username' => (string)($_SESSION['delivery_boy_username'] ?? ''),
        'name' => (string)($_SESSION['delivery_boy_name'] ?? 'Delivery Boy'),
    ];
}

