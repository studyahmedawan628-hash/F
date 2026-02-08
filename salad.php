<?php
session_start();
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salads - Foodey</title>
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

    <section class="hero" style="background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1551248429-40975aa4de74?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');">
        <div class="container">
            <h1>Salads</h1>
            <p>Fresh, crisp, and made to order</p>
        </div>
    </section>

    <section class="container">
        <div class="section-header">
            <h2>Salad Menu</h2>
            <p>Light, healthy options packed with flavor</p>
        </div>

        <div class="menu-grid">
            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('images/Caesar%20Salad.jpg');"></div>
                <div class="menu-content">
                    <h3>Caesar Salad</h3>
                    <p class="menu-price">$8.99</p>
                    <p class="menu-description">Romaine, parmesan, croutons, and creamy Caesar dressing.</p>
                    <button class="add-to-cart" data-id="salad-1" data-name="Caesar Salad" data-price="8.99">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('images/Greek%20Salad.jpg');"></div>
                <div class="menu-content">
                    <h3>Greek Salad</h3>
                    <p class="menu-price">$9.49</p>
                    <p class="menu-description">Cucumbers, tomatoes, olives, feta, and oregano vinaigrette.</p>
                    <button class="add-to-cart" data-id="salad-2" data-name="Greek Salad" data-price="9.49">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
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
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const price = this.dataset.price;

                addToCart(id, name, price, 1);
                this.classList.add('cart-add-animation');
                setTimeout(() => {
                    this.classList.remove('cart-add-animation');
                }, 500);
            });
        });

        updateCartCount();
    </script>
</body>
</html>


