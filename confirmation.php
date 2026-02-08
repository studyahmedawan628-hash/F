<?php
session_start();
require_once 'includes/functions.php';

// Get order ID from URL or session
$orderId = $_GET['order_id'] ?? ($_SESSION['last_order']['id'] ?? null);

if (!$orderId) {
    header('Location: index.php');
    exit();
}

// Get order details
$order = getOrder($orderId);
if (!$order) {
    header('Location: index.php');
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
    <title>Order Confirmation - Foodey</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="fade-in-down">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-utensils"></i> Foodey
                </a>
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="menu.php"><i class="fas fa-burger"></i> Menu</a></li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="contact_us.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li><a href="admin/login.php" class="login-btn login-admin"><i class="fas fa-user-shield"></i> Admin Login</a></li>
                    <li><a href="DeliveryBoy/login.php" class="login-btn login-rider"><i class="fas fa-motorcycle"></i> Rider Login</a></li>
                    <li><a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="container">
        <div class="confirmation-container fade-in">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Order Confirmed!</h1>
            <p class="lead">Thank you for your order. We're preparing your food now.</p>
            
            <div class="order-details slide-in-up">
                <h2><i class="fas fa-receipt"></i> Order Details</h2>
                
                <div class="detail-row">
                    <span>Order Number:</span>
                    <strong><?php echo $order['order_number']; ?></strong>
                </div>
                
                <div class="detail-row">
                    <span>Order Status:</span>
                    <?php
                        $normalizedStatus = normalizeOrderStatus($order['status']);
                        $badgeClass = orderStatusBadgeClass($order['status']);
                    ?>
                    <span class="order-status <?php echo htmlspecialchars($badgeClass); ?>">
                        <?php echo htmlspecialchars(orderStatusLabel($normalizedStatus)); ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span>Customer Name:</span>
                    <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span>Delivery Address:</span>
                    <span><?php echo htmlspecialchars($order['customer_address']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span>Order Date:</span>
                    <span><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                </div>
                
                <div class="detail-row">
                    <span>Estimated Delivery:</span>
                    <span>30-45 minutes</span>
                </div>
                
                <div class="order-items">
                    <h3>Order Items:</h3>
                    <?php foreach($orderItems as $item): ?>
                    <div class="order-item">
                        <span><?php echo $item['item_name']; ?> x <?php echo $item['quantity']; ?></span>
                        <span>$<?php echo number_format($item['total_price'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Tax (10%):</span>
                            <span>$<?php echo number_format($order['tax'], 2); ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="order-notes">
                    <h4>Special Instructions:</h4>
                    <p><?php echo htmlspecialchars($order['notes']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="confirmation-actions fade-in-up delay-2">
                <a href="index.php" class="btn">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="menu.php" class="btn btn-secondary">
                    <i class="fas fa-utensils"></i> Order Again
                </a>
            </div>
            
            <div class="whats-next slide-in-up delay-3">
                <h3><i class="fas fa-question-circle"></i> What happens next?</h3>
                <div class="steps">
                    <div class="step">
                        <div class="step-icon">1</div>
                        <p>We receive your order</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">2</div>
                        <p>We prepare your food</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">3</div>
                        <p>Delivery partner picks up your order</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">4</div>
                        <p>Food delivered to your door!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="fade-in">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Foodey</h3>
                    <p>Delivering delicious meals since 2025. Your satisfaction is our priority.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="admin/index.php">Admin</a></li>
                        <li><a href="DeliveryBoy/login.php">Rider</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                    <p><i class="fas fa-envelope"></i> Ahmedawan@gmail.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Address .Wahcantt</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Foodey. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/cart.js"></script>
    <script>
        // Clear cart on confirmation page
        clearCart();
        
        // Update cart count
        updateCartCount();
    </script>
    
    <style>
        .confirmation-container {
            text-align: center;
            padding: 3rem 0;
        }
        
        .confirmation-icon {
            font-size: 4rem;
            color: #2ed573;
            margin-bottom: 1rem;
            animation: bounce 1s ease infinite;
        }
        
        .order-details {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 2rem auto;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .order-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-preparing { background: #cce5ff; color: #004085; }
        .status-out_for_delivery { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .order-items {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 2px solid #eee;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #eee;
        }
        
        .order-totals {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #eee;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #ff6b6b;
            border-top: 2px solid #ddd;
            margin-top: 0.5rem;
            padding-top: 0.8rem;
        }
        
        .order-notes {
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .confirmation-actions {
            margin: 2rem 0;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .whats-next {
            margin-top: 3rem;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 2rem;
            gap: 1rem;
        }
        
        .step {
            flex: 1;
            min-width: 150px;
            text-align: center;
        }
        
        .step-icon {
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin: 0 auto 1rem;
        }
        
        @media (max-width: 768px) {
            .steps {
                flex-direction: column;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 0.3rem;
            }
        }
    </style>
</body>
</html>

