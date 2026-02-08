<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Foodey</title>
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
                    <li><a href="about.php" class="active"><i class="fas fa-info-circle"></i> About</a></li>
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

    <section class="hero" style="background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1559925393-8be0ec4767c8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');">
        <div class="container">
            <h1>About Foodey</h1>
            <p>Delivering happiness, one meal at a time</p>
        </div>
    </section>

    <section class="container">
        <div class="about-content">
            <div class="about-section fade-in">
                <h2>Our Story</h2>
                <p>Founded in 2025, Foodey began with a simple mission: to make delicious, high-quality food accessible to everyone in our community. What started as a small local restaurant has grown into a thriving online food delivery service.</p>
                <p>We believe that great food should be convenient, affordable, and most importantly, delicious. That's why we work with the best local chefs and use only the freshest ingredients in every dish we prepare.</p>
            </div>
            
            <div class="about-section fade-in-up delay-1">
                <h2>Our Mission</h2>
                <p>To revolutionize the food delivery experience by providing exceptional service, amazing food, and a seamless ordering process that brings restaurant-quality meals right to your doorstep.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card slide-in-left delay-1">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Average delivery time of 30 minutes</p>
                </div>
                
                <div class="feature-card fade-in-up delay-2">
                    <div class="feature-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <h3>Fresh Ingredients</h3>
                    <p>Locally sourced, fresh ingredients daily</p>
                </div>
                
                <div class="feature-card slide-in-right delay-3">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Made with Love</h3>
                    <p>Every dish prepared with care and passion</p>
                </div>
            </div>

            <div class="about-section fade-in-up delay-2">
                <h2>Meet the Team</h2>
                <p>The heart of Foodey is our people. A small, dedicated crew who bring warmth, skill, and flavor to every order.</p>
            </div>

            <div class="team-grid">
                <div class="team-card slide-in-left delay-1">
                    <div class="team-image">
                        <img src="images/owner.jpeg" alt="Restaurant Owner">
                    </div>
                    <div class="team-content">
                        <h3>Ahmedawan — Owner</h3>
                        <p>Ahmedawan is a 5th‑semester software engineering student with a kind heart and a passion for helping others. Foodey was built not only for profit, but to deliver quality food and honest service to the community.</p>
                        <span class="team-tag">Founder</span>
                    </div>
                </div>

                <div class="team-card fade-in-up delay-2">
                    <div class="team-image">
                        <img src="images/manager.jpeg" alt="Restaurant Manager">
                    </div>
                    <div class="team-content">
                        <h3>Mahaz — Manager</h3>
                        <p>Mahaz is one of the owner’s close friends and brings high‑level expertise to daily operations, keeping quality and service in perfect sync.</p>
                        <span class="team-tag">Operations</span>
                    </div>
                </div>

                <div class="team-card fade-in-up delay-3">
                    <div class="team-image">
                        <img src="images/cheif.jpeg" alt="Restaurant Chef">
                    </div>
                    <div class="team-content">
                        <h3>Huzaifa — Chef</h3>
                        <p>Huzaifa is also the owner’s close friend, known for high expertise and a talent for creating bold, fresh flavors.</p>
                        <span class="team-tag">Kitchen</span>
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
        // Update cart count on load
        updateCartCount();
    </script>
    
    <style>
        .about-content {
            padding: 3rem 0;
        }
        
        .about-section {
            margin-bottom: 3rem;
        }
        
        .about-section h2 {
            color: #2f3542;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #ff6b6b;
            display: inline-block;
        }
        
        .about-section p {
            margin-bottom: 1rem;
            line-height: 1.8;
            color: #555;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ff6b6b, #ff8e53);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
            color: white;
        }
        
        .feature-card h3 {
            margin-bottom: 1rem;
            color: #2f3542;
        }
        
        .feature-card p {
            color: #666;
            font-size: 0.95rem;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            margin: 3rem 0 1rem;
        }

        .team-card {
            background: linear-gradient(180deg, #ffffff, #fff6f3);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 35px rgba(0,0,0,0.15);
        }

        .team-image {
            position: relative;
            height: 220px;
            background: radial-gradient(circle at top, #ffe0d6, #ffd2c2);
        }

        .team-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .team-content {
            padding: 1.5rem;
            text-align: center;
        }

        .team-content h3 {
            margin-bottom: 0.5rem;
            color: #2f3542;
        }

        .team-content p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .team-tag {
            display: inline-block;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #ff6b6b, #ff8e53);
            color: #fff;
            font-size: 0.8rem;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
    </style>
</body>
</html>

