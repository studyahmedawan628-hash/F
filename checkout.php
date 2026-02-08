<?php
session_start();
require_once 'includes/functions.php';

// Process checkout if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get cart from localStorage via JavaScript or session
    $cartItems = isset($_POST['cart_items']) ? json_decode($_POST['cart_items'], true) : [];
    
    if (empty($cartItems)) {
        // Try to get cart from session
        $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }
    
    // Validate form data
    $errors = [];
    $required = ['name', 'email', 'phone', 'address'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = "This field is required";
        }
    }
    
    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address";
    }
    
    // Check if cart is empty
    if (empty($cartItems)) {
        $errors['cart'] = "Your cart is empty";
    }
    
    if (empty($errors)) {
        // Prepare customer data
        $customerData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'notes' => $_POST['notes'] ?? ''
        ];
        
        // Place order in database
        $result = placeOrder($customerData, $cartItems);
        
        if ($result['success']) {
            // Clear cart
            echo "<script>localStorage.removeItem('FoodeyCart');</script>";
            
            // Store order info in session for confirmation page
            $_SESSION['last_order'] = [
                'id' => $result['order_id'],
                'number' => $result['order_number']
            ];
            
            // Redirect to confirmation page
            header("Location: confirmation.php?order_id=" . $result['order_id']);
            exit();
        } else {
            $errors['database'] = "Failed to place order: " . $result['error'];
        }
    }
}

// Get cart items for display
$cartItems = [];
if (isset($_SESSION['cart'])) {
    $cartItems = $_SESSION['cart'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Foodey</title>
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
        <div class="section-title fade-in">
            <h2>Checkout</h2>
        </div>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger fade-in">
            <h4><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h4>
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <form id="checkout-form" class="checkout-form fade-in-up" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Delivery Address *</label>
                        <textarea id="address" name="address" class="form-control" rows="3" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="order-summary slide-in-right">
                    <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                    <div id="checkout-items">
                        <!-- Order items will be loaded by JavaScript -->
                    </div>
                    <div class="summary-total">
                        <span>Total:</span>
                        <span id="checkout-total">$0.00</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes"><i class="fas fa-sticky-note"></i> Special Instructions (Optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>
                
                <input type="hidden" id="cart-items-input" name="cart_items" value="">
                
                <div class="form-group">
                    <button type="submit" class="btn" style="width: 100%; padding: 1rem;">
                        <i class="fas fa-paper-plane"></i> Place Order
                    </button>
                </div>
            </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Load cart items for checkout summary
            const cart = getCart();
            const checkoutItems = document.getElementById('checkout-items');
            const checkoutTotal = document.getElementById('checkout-total');
            const cartItemsInput = document.getElementById('cart-items-input');
            
            // Store cart in hidden input
            cartItemsInput.value = JSON.stringify(cart);
            
            if (cart.length === 0) {
                checkoutItems.innerHTML = '<p class="text-center">Your cart is empty</p>';
                checkoutTotal.textContent = '$0.00';
                return;
            }
            
            let html = '';
            let subtotal = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                html += `
                    <div class="checkout-item">
                        <span>${item.name} x ${item.quantity}</span>
                        <span>$${itemTotal.toFixed(2)}</span>
                    </div>
                `;
            });
            
            const tax = subtotal * 0.1;
            const total = subtotal + tax;
            
            html += `
                <div class="checkout-item">
                    <span>Subtotal</span>
                    <span>$${subtotal.toFixed(2)}</span>
                </div>
                <div class="checkout-item">
                    <span>Tax (10%)</span>
                    <span>$${tax.toFixed(2)}</span>
                </div>
            `;
            
            checkoutItems.innerHTML = html;
            checkoutTotal.textContent = `$${total.toFixed(2)}`;
            
            // Handle form submission
            const form = document.getElementById('checkout-form');
            form.addEventListener('submit', function(e) {
                // Basic validation
                let valid = true;
                const required = ['name', 'email', 'phone', 'address'];
                
                required.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input.value.trim()) {
                        valid = false;
                        input.style.borderColor = '#ff4757';
                    } else {
                        input.style.borderColor = '#ddd';
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    showNotification('Please fill in all required fields', 'error');
                    return;
                }
                
                if (cart.length === 0) {
                    e.preventDefault();
                    showNotification('Your cart is empty', 'error');
                    return;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>

