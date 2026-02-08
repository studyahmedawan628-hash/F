<?php
session_start();
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Dishes - Foodey</title>
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

    <section class="hero" style="background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1752673508949-f4aeeaef75f0?auto=format&fit=crop&w=1800&q=80');">
        <div class="container">
            <h1>Upcoming Desi Dishes</h1>
            <p>Traditional Pakistani favorites coming soon</p>
        </div>
    </section>

    <section class="container">
        <div class="section-header">
            <h2>Upcoming Menu</h2>
            <p>These are our new upcoming very yummy dishes.</p>
        </div>

        <div class="menu-grid">
            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://images.unsplash.com/photo-1697155406055-2db32d47ca07?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8Y2hpY2tlbiUyMGJpcnlhbml8ZW58MHx8MHx8fDA%3D');"></div>
                <div class="menu-content">
                    <h3>Chicken Biryani</h3>
                    <p class="menu-description">Fragrant basmati rice with spiced chicken.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://media.istockphoto.com/id/1410335829/photo/mutton-sukka-karahi-served-in-a-dish-isolated-on-dark-background-top-view.webp?a=1&b=1&s=612x612&w=0&k=20&c=2pET8CzKH7dDhy0spneHqiBr-dyoZKcbv2SPUhAE020=');"></div>
                <div class="menu-content">
                    <h3>Mutton Korma</h3>
                    <p class="menu-description">Slow-cooked mutton in a rich, creamy gravy.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://images.unsplash.com/photo-1694579740719-0e601c5d2437?w=400&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8Y2hpY2tlbiUyMGthcmFoaXxlbnwwfHwwfHx8MA%3D%3D');"></div>
                <div class="menu-content">
                    <h3>Chicken Karahi</h3>
                    <p class="menu-description">Spicy wok-fried chicken with tomatoes and ginger.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?w=400&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8c2Vla2glMjBrYWJhYnxlbnwwfHwwfHx8MA%3D%3D');"></div>
                <div class="menu-content">
                    <h3>Seekh Kabab</h3>
                    <p class="menu-description">Grilled minced meat kababs with herbs and spices.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://images.unsplash.com/photo-1644364935906-792b2245a2c0?w=400&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8Y2FwbGklMjBrYWJhYnxlbnwwfHwwfHx8MA%3D%3D');"></div>
                <div class="menu-content">
                    <h3>Chapli Kabab</h3>
                    <p class="menu-description">Flat, spicy kababs with onions and chilies.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://media.istockphoto.com/id/504338599/photo/tender-beef-nihari.webp?a=1&b=1&s=612x612&w=0&k=20&c=3cGYJoHYdLWri5vpqJGzPzuEMlmn2Yk8cyM65cumJbw=');"></div>
                <div class="menu-content">
                    <h3>Nihari</h3>
                    <p class="menu-description">Slow-cooked beef stew with traditional spices.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://media.istockphoto.com/id/2233190495/photo/middle-eastern-harees-dish.webp?a=1&b=1&s=612x612&w=0&k=20&c=iTG6VK3Y4-LTyf6zE39jtMCauR0bt_Quh8r9sSNRU8E=');"></div>
                <div class="menu-content">
                    <h3>Haleem</h3>
                    <p class="menu-description">Hearty mix of lentils, wheat, and tender meat.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://media.istockphoto.com/id/1052349782/photo/delicious-homemade-biryani-with-chicken-onion-lemon-spices-and-cilantro-close-up-horizontal.webp?a=1&b=1&s=612x612&w=0&k=20&c=bHsywpZ7oJwYGdQAa4zXwPf7SvV1cGZiwRZ5oCwTyNI=');"></div>
                <div class="menu-content">
                    <h3>Chicken Pulao</h3>
                    <p class="menu-description">Aromatic rice cooked with tender chicken.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://media.istockphoto.com/id/1909936020/photo/palak-saag.webp?a=1&b=1&s=612x612&w=0&k=20&c=kNwmTsAU5vIHbDYykbm-qqlgexfqebHpzg2bqJU8EME=');"></div>
                <div class="menu-content">
                    <h3>Palak Paneer</h3>
                    <p class="menu-description">Creamy spinach curry with soft paneer cubes.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://media.istockphoto.com/id/1170374719/photo/dal-makhani-at-dark-background.webp?a=1&b=1&s=612x612&w=0&k=20&c=FWHhW6SnrLvmwaR-APN3pIxEjLJe073-PQ0cfvOGoTI=');"></div>
                <div class="menu-content">
                    <h3>Daal Makhani</h3>
                    <p class="menu-description">Black lentils simmered with butter and spices.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://images.unsplash.com/photo-1652545296821-09a023a9fd08?w=400&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8Y2hpY2tlbiUyMHRpa2thfGVufDB8fDB8fHww');"></div>
                <div class="menu-content">
                    <h3>Chicken Tikka</h3>
                    <p class="menu-description">Char-grilled chicken pieces with smoky flavor.</p>
                </div>
            </div>

            <div class="menu-card fade-in">
                <div class="menu-image" style="background-image: url('https://media.istockphoto.com/id/2225934955/photo/minced-beef-keema-with-fresh-chopped-onions-green-chilies-and-flavorful-spices-perfectly.webp?a=1&b=1&s=612x612&w=0&k=20&c=l5RsfeA8K9Fu_Ey85wVFWDxQSADHrKlgljy5O4p2XSI=');"></div>
                <div class="menu-content">
                    <h3>Beef Keema</h3>
                    <p class="menu-description">Minced beef cooked with peas and spices.</p>
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
        updateCartCount();
    </script>
</body>
</html>


