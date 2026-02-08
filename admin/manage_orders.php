<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$deliveryBoys = getDeliveryBoys();
$deliveryBoyMap = [];
foreach ($deliveryBoys as $db) {
    $deliveryBoyMap[(int)$db['id']] = $db['full_name'];
}
$assignmentColumn = getOrderAssignmentColumn();
$canAssignDelivery = $assignmentColumn !== null && !empty($deliveryBoys);
$errorMessage = '';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle admin assignment to delivery boy
    if (isset($_POST['assign_delivery_boy'])) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $deliveryBoyId = (int)($_POST['delivery_boy_id'] ?? 0);
        if ($orderId <= 0 || $deliveryBoyId <= 0) {
            $errorMessage = 'Please select a delivery boy.';
        } else {
            $adminName = $_SESSION['admin_name'] ?? ($_SESSION['admin_username'] ?? 'admin');
            $result = assignOrderToDeliveryBoy($orderId, $deliveryBoyId, (string)$adminName);
            if ($result['success'] ?? false) {
                $message = $result['message'] ?? 'Order assigned.';
            } else {
                $errorMessage = $result['message'] ?? 'Assignment failed.';
            }
        }
    }

    if (isset($_POST['bulk_action']) && isset($_POST['selected_orders'])) {
        $action = $_POST['bulk_action'];
        $selectedOrders = $_POST['selected_orders'];
        
        // The UI sends selected orders as a JSON string in a hidden input.
        // Normalize it to an array before processing bulk actions.
        if (!is_array($selectedOrders)) {
            $decoded = json_decode($selectedOrders, true);
            $selectedOrders = is_array($decoded) ? $decoded : [];
        }
        
        if ($action === 'delete') {
            // Delete multiple orders
            $successCount = 0;
            foreach ($selectedOrders as $orderId) {
                // First delete order items, then order
                require_once '../includes/db.php';
                $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                if ($stmt->execute([$orderId])) {
                    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                    if ($stmt->execute([$orderId])) {
                        $successCount++;
                    }
                }
            }
            $message = "Successfully deleted $successCount order(s).";
        } elseif ($action === 'update_status' && isset($_POST['bulk_status'])) {
            // Update status for multiple orders
            $newStatus = $_POST['bulk_status'];
            $successCount = 0;
            foreach ($selectedOrders as $orderId) {
                if (updateOrderStatus($orderId, $newStatus)) {
                    $successCount++;
                }
            }
            $message = "Successfully updated status for $successCount order(s).";
        }
    }
    
    // Handle single status update via AJAX
    if (isset($_POST['update_single_status'])) {
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['status'];
        
        if (updateOrderStatus($orderId, $newStatus)) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        exit();
    }
}

// Get filter parameters
$status = $_GET['status'] ?? null;
$search = $_GET['search'] ?? null;
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;

// Build query for orders
require_once '../includes/db.php';
$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if ($date_from) {
    $sql .= " AND DATE(order_date) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $sql .= " AND DATE(order_date) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY order_date DESC";

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get total counts for stats
$allOrders = getAllOrders();
$stats = ['all' => count($allOrders), 'pending' => 0, 'preparing' => 0, 'delivered' => 0, 'cancelled' => 0];
foreach ($allOrders as $o) {
    $normalized = normalizeOrderStatus($o['status']);
    if ($normalized === 'placed') {
        $stats['pending']++;
    } elseif (in_array($normalized, ['confirmed', 'assigned', 'picked_up'], true)) {
        $stats['preparing']++;
    } elseif ($normalized === 'delivered') {
        $stats['delivered']++;
    } elseif ($normalized === 'cancelled') {
        $stats['cancelled']++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Foodey Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
            background: #f5f7fa;
        }
        
        .admin-sidebar {
            background: linear-gradient(180deg, #2f3542 0%, #1a1e27 100%);
            color: white;
            padding: 0;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            color: #ff6b6b;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }
        
        .sidebar-header p {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .admin-info {
            background: rgba(255,255,255,0.1);
            padding: 0.8rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        
        .admin-info p {
            margin: 0;
            font-size: 0.9rem;
        }
        
        .logout-btn {
            display: inline-block;
            margin-top: 0.5rem;
            color: #ff6b6b;
            text-decoration: none;
            font-size: 0.8rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 1.5rem;
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255, 107, 107, 0.1);
            border-left-color: #ff6b6b;
            color: white;
        }
        
        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
        }
        
        .orders-container {
            padding: 2rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            color: #2f3542;
            margin: 0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .filters {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .filter-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .btn {
            padding: 0.8rem 2rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #ff6b6b;
            color: white;
        }
        
        .btn-primary:hover {
            background: #ff4757;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .orders-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            color: #666;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .order-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-preparing { background: #cce5ff; color: #004085; }
        .status-out_for_delivery { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-placed { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #cce5ff; color: #004085; }
        .status-assigned { background: #d1ecf1; color: #0c5460; }
        .status-picked_up { background: #d1ecf1; color: #0c5460; }
        
        .customer-info {
            display: flex;
            flex-direction: column;
        }
        
        .customer-info strong {
            margin-bottom: 0.3rem;
        }
        
        .customer-info small {
            color: #888;
            font-size: 0.8rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card.active {
            background: #ff6b6b;
            color: white;
        }
        
        .stat-card.active .stat-number,
        .stat-card.active .stat-label {
            color: white;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2f3542;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .bulk-actions {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .select-all {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .bulk-action-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .status-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        
        .checkbox-cell {
            width: 30px;
        }
        
        .checkbox-cell input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .quick-status {
            padding: 0.3rem 0.6rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.8rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quick-status:hover {
            background: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .filter-row {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .bulk-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-utensils"></i> Foodey</h2>
                <p>Admin Panel</p>
                <div class="admin-info">
                    <p><i class="fas fa-user"></i> <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></p>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="edit_idem.php"><i class="fas fa-utensils"></i> Manage Menu</a></li>
                <li><a href="manage_orders.php" class="active"><i class="fas fa-list-alt"></i> Manage Orders</a></li>
                <li><a href="manage_riders.php"><i class="fas fa-motorcycle"></i> Manage Riders</a></li>
                <li><a href="reply.php"><i class="fas fa-envelope"></i> Reply Messages</a></li>
            </ul>
        </aside>
        
        <main class="orders-container">
            <?php if (isset($message)): ?>
            <div class="alert alert-success fade-in">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error fade-in">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h1>Manage Orders</h1>
            </div>
            
            <!-- Order Stats -->
            <div class="stats-cards">
                <a href="manage_orders.php" class="stat-card <?php echo !$status ? 'active' : ''; ?>">
                    <div class="stat-number"><?php echo $stats['all']; ?></div>
                    <div class="stat-label">All Orders</div>
                </a>
                <a href="manage_orders.php?status=pending" class="stat-card <?php echo $status == 'pending' ? 'active' : ''; ?>">
                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending</div>
                </a>
                <a href="manage_orders.php?status=preparing" class="stat-card <?php echo $status == 'preparing' ? 'active' : ''; ?>">
                    <div class="stat-number"><?php echo $stats['preparing']; ?></div>
                    <div class="stat-label">Preparing</div>
                </a>
                <a href="manage_orders.php?status=assigned" class="stat-card <?php echo $status == 'assigned' ? 'active' : ''; ?>">
                    <div class="stat-number"><?php echo (int)count(array_filter($allOrders, fn($o) => normalizeOrderStatus($o['status']) === 'assigned')); ?></div>
                    <div class="stat-label">Assigned</div>
                </a>
                <a href="manage_orders.php?status=delivered" class="stat-card <?php echo $status == 'delivered' ? 'active' : ''; ?>">
                    <div class="stat-number"><?php echo $stats['delivered']; ?></div>
                    <div class="stat-label">Delivered</div>
                </a>
                <a href="manage_orders.php?status=cancelled" class="stat-card <?php echo $status == 'cancelled' ? 'active' : ''; ?>">
                    <div class="stat-number"><?php echo $stats['cancelled']; ?></div>
                    <div class="stat-label">Cancelled</div>
                </a>
            </div>
            
            <!-- Filters -->
            <div class="filters fade-in">
                <form method="GET" action="">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search">Search Orders</label>
                            <input type="text" id="search" name="search" class="form-control" 
                                   placeholder="Search by order number, name, email..." 
                                   value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="status">Filter by Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="assigned" <?php echo $status == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="preparing" <?php echo $status == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="out_for_delivery" <?php echo $status == 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                <option value="delivered" <?php echo $status == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="date_from">Date From</label>
                            <input type="date" id="date_from" name="date_from" class="form-control" 
                                   value="<?php echo htmlspecialchars($date_from ?? ''); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="date_to">Date To</label>
                            <input type="date" id="date_to" name="date_to" class="form-control" 
                                   value="<?php echo htmlspecialchars($date_to ?? ''); ?>">
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                        <?php if ($search || $status || $date_from || $date_to): ?>
                        <div class="filter-group">
                            <a href="manage_orders.php" class="btn btn-secondary" style="width: 100%;">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Bulk Actions -->
            <div class="bulk-actions fade-in">
                <div class="select-all">
                    <input type="checkbox" id="select-all-orders">
                    <label for="select-all-orders">Select All</label>
                </div>
                
                <form method="POST" action="" class="bulk-action-form" id="bulk-action-form">
                    <input type="hidden" name="selected_orders" id="selected-orders-input">
                    <select name="bulk_action" id="bulk-action" class="form-control" style="width: 150px;">
                        <option value="">Bulk Actions</option>
                        <option value="update_status">Update Status</option>
                        <option value="delete">Delete</option>
                    </select>
                    
                    <select name="bulk_status" id="bulk-status" class="form-control" style="width: 150px; display: none;">
                        <option value="pending">Pending</option>
                        <option value="preparing">Preparing</option>
                        <option value="out_for_delivery">Out for Delivery</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    
                    <button type="button" id="apply-bulk-action" class="btn btn-primary btn-sm">
                        Apply
                    </button>
                </form>
            </div>
            
            <!-- Orders Table -->
            <div class="orders-table fade-in">
                <div class="table-responsive">
                    <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No Orders Found</h3>
                        <p><?php echo $search ? 'No orders match your search.' : 'No orders have been placed yet.'; ?></p>
                        <?php if ($search || $status || $date_from || $date_to): ?>
                        <a href="manage_orders.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> View All Orders
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <form id="orders-form" method="POST">
                        <table>
                            <thead>
                                <tr>
                                    <th class="checkbox-cell">
                                        <input type="checkbox" id="select-all-header">
                                    </th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td class="checkbox-cell">
                                        <input type="checkbox" class="order-checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>">
                                    </td>
                                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                                    <td>
                                        <div class="customer-info">
                                            <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                            <small><?php echo $order['customer_email']; ?></small>
                                            <small><?php echo $order['customer_phone']; ?></small>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <?php
                                            $normalizedStatus = normalizeOrderStatus($order['status']);
                                            $badgeClass = orderStatusBadgeClass($order['status']);
                                        ?>
                                        <span class="order-status <?php echo htmlspecialchars($badgeClass); ?>">
                                            <?php echo htmlspecialchars(orderStatusLabel($normalizedStatus)); ?>
                                        </span>
                                        <br>
                                        <select class="quick-status" data-order-id="<?php echo $order['id']; ?>">
                                            <option value="">Change</option>
                                            <option value="assigned" <?php echo $order['status'] == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                            <option value="out_for_delivery" <?php echo $order['status'] == 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <?php if ($canAssignDelivery): ?>
                                            <div style="margin-top: 0.5rem;">
                                                <?php
                                                    $assignedId = $assignmentColumn ? (int)($order[$assignmentColumn] ?? 0) : 0;
                                                    $assignedName = $assignedId && isset($deliveryBoyMap[$assignedId]) ? $deliveryBoyMap[$assignedId] : 'Not assigned';
                                                ?>
                                                <div style="font-size: 0.85rem; font-weight: 700; margin-bottom: 0.25rem;">
                                                    Delivery: <?php echo htmlspecialchars($assignedName); ?>
                                                </div>
                                                <form method="POST" style="display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center;">
                                                    <input type="hidden" name="assign_delivery_boy" value="1">
                                                    <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                                    <select name="delivery_boy_id" class="form-control" style="min-width: 150px;">
                                                        <option value="">Assign rider</option>
                                                        <?php foreach ($deliveryBoys as $db): ?>
                                                            <option value="<?php echo (int)$db['id']; ?>" <?php echo $assignedId === (int)$db['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($db['full_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-motorcycle"></i> Assign
                                                    </button>
                                                </form>
                                            </div>
                                        <?php elseif ($assignmentColumn !== null && empty($deliveryBoys)): ?>
                                            <div style="margin-top: 0.4rem; font-size: 0.85rem; color: #666; font-weight: 600;">
                                                No delivery boys found.
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, g:i a', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="printOrder(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-print"></i> Print
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Select All functionality
        document.getElementById('select-all-header').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedOrders();
        });
        
        document.getElementById('select-all-orders').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            const selectAllHeader = document.getElementById('select-all-header');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            selectAllHeader.checked = this.checked;
            updateSelectedOrders();
        });
        
        // Update selected orders array
        function updateSelectedOrders() {
            const checkboxes = document.querySelectorAll('.order-checkbox:checked');
            const selectedIds = Array.from(checkboxes).map(cb => cb.value);
            document.getElementById('selected-orders-input').value = JSON.stringify(selectedIds);
        }
        
        // Add event listeners to individual checkboxes
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedOrders);
        });
        
        // Bulk action form handling
        document.getElementById('bulk-action').addEventListener('change', function() {
            const bulkStatus = document.getElementById('bulk-status');
            if (this.value === 'update_status') {
                bulkStatus.style.display = 'inline-block';
            } else {
                bulkStatus.style.display = 'none';
            }
        });
        
        document.getElementById('apply-bulk-action').addEventListener('click', function() {
            const bulkAction = document.getElementById('bulk-action').value;
            const selectedOrders = JSON.parse(document.getElementById('selected-orders-input').value || '[]');
            
            if (selectedOrders.length === 0) {
                alert('Please select at least one order.');
                return;
            }
            
            if (!bulkAction) {
                alert('Please select a bulk action.');
                return;
            }
            
            if (bulkAction === 'update_status') {
                const bulkStatus = document.getElementById('bulk-status').value;
                if (!bulkStatus) {
                    alert('Please select a status.');
                    return;
                }
                if (confirm(`Update status to "${bulkStatus}" for ${selectedOrders.length} order(s)?`)) {
                    document.getElementById('bulk-action-form').submit();
                }
            } else if (bulkAction === 'delete') {
                if (confirm(`Are you sure you want to delete ${selectedOrders.length} order(s)? This action cannot be undone.`)) {
                    document.getElementById('bulk-action-form').submit();
                }
            }
        });
        
        // Quick status update
        document.querySelectorAll('.quick-status').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.dataset.orderId;
                const newStatus = this.value;
                
                if (!newStatus) return;
                
                if (confirm(`Change order status to "${newStatus}"?`)) {
                    // Send AJAX request
                    const formData = new FormData();
                    formData.append('update_single_status', '1');
                    formData.append('order_id', orderId);
                    formData.append('status', newStatus);
                    
                    fetch('manage_orders.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('An error occurred: ' + error);
                    });
                } else {
                    // Reset to original value
                    this.value = '';
                }
            });
        });
        
        // Print order function
        function printOrder(orderId) {
            window.open(`view_order.php?id=${orderId}&print=1`, '_blank');
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedOrders();
            
            // Check if print parameter is in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('print')) {
                window.print();
            }
        });
    </script>
</body>
</html>
