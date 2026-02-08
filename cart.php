<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Foodey</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .notification {
            display: none;
        }
    </style>
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
                    <li><a href="cart.php" class="cart-icon active">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="container">
        <div class="section-title fade-in">
            <h2>Your Shopping Cart</h2>
        </div>
        
        <div id="empty-cart" class="text-center" style="display: none;">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart fa-4x" style="color: #ddd;"></i>
            </div>
            <h3>Your cart is empty</h3>
            <p>Add some delicious items from our menu</p>
            <a href="menu.php" class="btn">Browse Menu</a>
        </div>
        
        <div class="cart-container" id="cart-summary" style="display: none;">
            <div class="cart-items">
                <h3>Order Items</h3>
                <div id="cart-items">
                    <!-- Cart items will be loaded here by JavaScript -->
                </div>
            </div>
            
            <div class="cart-summary slide-in-right">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Tax (10%):</span>
                    <span id="tax">$0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="cart-total">$0.00</span>
                </div>
                
                <div class="cart-actions">
                    <button onclick="clearCart()" class="btn" style="background: #ff4757; margin-bottom: 10px; width: 100%;">
                        <i class="fas fa-trash"></i> Clear Cart
                    </button>
                    <a href="checkout.php" class="btn" style="width: 100%; text-align: center;">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </a>
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
        // Load cart items when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadCartItems();
        });
    </script>
</body>
</html>

