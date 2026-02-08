<?php
session_start();
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get order ID from URL
$orderId = $_GET['id'] ?? 0;

if (!$orderId) {
    header('Location: manage_orders.php');
    exit();
}

// Get order details
$order = getOrder($orderId);
if (!$order) {
    header('Location: manage_orders.php');
    exit();
}

// Get order items
$orderItems = getOrderItems($orderId);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    if (updateOrderStatus($orderId, $newStatus)) {
        $message = "Order status updated successfully!";
        // Refresh order data
        $order = getOrder($orderId);
    } else {
        $error = "Failed to update order status.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['order_number']; ?> - Foodey Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .order-details-container {
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
        
        .order-info-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        
        .order-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2f3542;
        }
        
        .order-status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
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
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }
        
        .info-card h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #555;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-row {
            margin-bottom: 0.8rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #2f3542;
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .order-items-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            color: #666;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }
        
        .order-items-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
        }
        
        .total-row {
            font-weight: bold;
            font-size: 1.1rem;
            color: #2f3542;
        }
        
        .grand-total {
            color: #ff6b6b;
            font-size: 1.2rem;
        }
        
        .status-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
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
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .order-items-table {
                font-size: 0.9rem;
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
                <li><a href="manage_orders.php"><i class="fas fa-list-alt"></i> Manage Orders</a></li>
                <li><a href="manage_menu.php"><i class="fas fa-utensils"></i> Manage Menu</a></li>
                <li><a href="manage_riders.php"><i class="fas fa-motorcycle"></i> Manage Riders</a></li>
                <li><a href="reply.php"><i class="fas fa-envelope"></i> Reply Messages</a></li>
            </ul>
        </aside>
        
        <main class="order-details-container">
            <?php if (isset($message)): ?>
            <div class="alert alert-success fade-in">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-error fade-in">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h1>Order Details</h1>
                <a href="manage_orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>
            
            <div class="order-info-card fade-in">
                <div class="order-header">
                    <div class="order-number">Order #<?php echo $order['order_number']; ?></div>
                    <?php
                        $normalizedStatus = normalizeOrderStatus($order['status']);
                        $badgeClass = orderStatusBadgeClass($order['status']);
                    ?>
                    <span class="order-status-badge <?php echo htmlspecialchars($badgeClass); ?>">
                        <?php echo htmlspecialchars(orderStatusLabel($normalizedStatus)); ?>
                    </span>
                </div>
                
                <div class="info-grid">
                    <div class="info-card">
                        <h3>Customer Information</h3>
                        <div class="info-row">
                            <div class="info-label">Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <h3>Delivery Information</h3>
                        <div class="info-row">
                            <div class="info-label">Delivery Address</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Order Date</div>
                            <div class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['updated_at'])); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Order Items</h3>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $itemsTotal = 0;
                            foreach ($orderItems as $item): 
                                $itemsTotal += $item['total_price'];
                            ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <?php if ($item['image'] && $item['image'] != 'default.jpg'): ?>
                                        <?php
                                            $imageSrc = $item['image'];
                                            if (!preg_match('/^https?:\/\//', $imageSrc)) {
                                                $imageSrc = '../' . $imageSrc;
                                            }
                                        ?>
                                        <img src="<?php echo $imageSrc; ?>" 
                                             alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                             class="item-image">
                                        <?php else: ?>
                                        <div class="item-image" style="background: #ddd; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-utensils" style="color: #666;"></i>
                                        </div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($item['item_name']); ?></span>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Order Totals -->
                            <tr class="total-row">
                                <td colspan="3" style="text-align: right;">Subtotal:</td>
                                <td>$<?php echo number_format($order['subtotal'], 2); ?></td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="3" style="text-align: right;">Tax (10%):</td>
                                <td>$<?php echo number_format($order['tax'], 2); ?></td>
                            </tr>
                            <tr class="total-row grand-total">
                                <td colspan="3" style="text-align: right;">Total Amount:</td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="notes-section" style="margin-top: 2rem;">
                    <h3>Customer Notes</h3>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="status-form">
                    <h3>Update Order Status</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="status">Select New Status</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="assigned" <?php echo $order['status'] == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="out_for_delivery" <?php echo $order['status'] == 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i> Update Status
                        </button>
                    </form>
                </div>
                
                <div class="action-buttons">
                    <a href="manage_orders.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Back to Orders List
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Print Order
                    </button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
