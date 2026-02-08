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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Order #<?php echo $order['order_number']; ?></title>
    <style>
        @media print {
            .no-print { display: none; }
            body { font-size: 12pt; }
            .print-header { margin-bottom: 20px; }
            .print-section { margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            th { background-color: #f2f2f2; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .print-section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .total-row {
            font-weight: bold;
        }
        
        .grand-total {
            font-size: 1.2em;
            color: #ff6b6b;
        }
        
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .btn {
            padding: 10px 20px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background: #ff4757;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn" onclick="window.print()">Print Order</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>
    
    <div class="print-header">
        <h1>Foodey - Order Invoice</h1>
        <p>Order #<?php echo $order['order_number']; ?></p>
        <p>Date: <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
    </div>
    
    <div class="info-grid">
        <div class="info-box">
            <div class="section-title">Customer Information</div>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
        </div>
        
        <div class="info-box">
            <div class="section-title">Delivery Information</div>
            <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
        </div>
    </div>
    
    <div class="print-section">
        <div class="section-title">Order Items</div>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orderItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                
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
    <div class="print-section">
        <div class="section-title">Customer Notes</div>
        <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="print-section" style="margin-top: 40px;">
        <p>Thank you for your order!</p>
        <p><strong>Foodey</strong><br>
        Address .Wahcantt<br>
        Phone: (123) 456-7890<br>
        Email: Ahmedawan@gmail.com</p>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>