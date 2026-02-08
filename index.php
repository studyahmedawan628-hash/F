<?php
session_start();
require_once 'includes/functions.php';

// Get all menu items
$allMenuItems = getMenuItems();
// Get featured/popular items (fixed list in a specific order)
$popularNames = [
    'Bacon Burger',
    'Cheeseburger',
    'Chocolate Brownie',
    'Coca Cola',
    'Orange Juice',
    'Chinease Rice',
];
$itemsByName = [];
foreach ($allMenuItems as $mi) {
    $itemsByName[$mi['name']] = $mi;
}
$featuredItems = [];
foreach ($popularNames as $pn) {
    if (isset($itemsByName[$pn])) {
        $featuredItems[] = $itemsByName[$pn];
    }
}
// Safe fallback if some popular items are missing in the database
if (count($featuredItems) < 6) {
    $featuredItems = array_slice($allMenuItems, 0, 6);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foodey - Online Food Ordering</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="fade-in-down">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-utensils"></i> Foodey
                </a>
                <ul class="nav-links">
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider">
            <div class="hero-slide active" style="background-image: url('images/Margherita%20Pizza.jpg');"></div>
            <div class="hero-slide" style="background-image: url('images/Bacon%20Burger.jpg');"></div>
            <div class="hero-slide" style="background-image: url('images/Caesar%20Salad.jpg');"></div>
            <div class="hero-slide" style="background-image: url('images/Chocolate%20Brownie.jpg');"></div>
            <div class="hero-slide" style="background-image: url('images/chinease%20rice.webp');"></div>
            <div class="hero-slide" style="background-image: url('images/French%20Fries.jpg');"></div>
        </div>
        <div class="container">
            <h1>Delicious Food Delivered To Your Door</h1>
            <p>Order from our wide selection of meals and get it delivered hot and fresh</p>
            <a href="menu.php" class="btn">Order Now <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="hero-controls" aria-label="Hero slider controls">
            <button class="hero-btn hero-prev" type="button" aria-label="Previous slide">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hero-btn hero-next" type="button" aria-label="Next slide">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="hero-dots" aria-label="Hero slider pagination"></div>
    </section>

    <!-- Featured Categories -->
    <section class="container">
        <div class="section-title fade-in">
            <h2>Our Categories</h2>
        </div>
        
        <div class="category-grid">
            <div class="category-card slide-in-left delay-1">
                <img src="images/Margherita%20Pizza.jpg" alt="Pizzas" class="category-image">
                <div class="category-icon">
                    <i class="fas fa-pizza-slice"></i>
                </div>
                <h3>Pizzas</h3>
                <p>Freshly baked with premium toppings</p>
                <a href="pizza.php" class="btn btn-secondary" onmouseover="this.style.backgroundColor='skyblue'; this.style.color='#000';" onmouseout="this.style.backgroundColor=''; this.style.color='';">View Pizzas</a>
            </div>
            
            <div class="category-card fade-in-up delay-2">
                <img src="images/Bacon%20Burger.jpg" alt="Burgers" class="category-image">
                <div class="category-icon">
                    <i class="fas fa-hamburger"></i>
                </div>
                <h3>Burgers</h3>
                <p>Juicy burgers with secret sauces</p>
                <a href="burger.php" class="btn btn-secondary" onmouseover="this.style.backgroundColor='skyblue'; this.style.color='#000';" onmouseout="this.style.backgroundColor=''; this.style.color='';">View Burgers</a>
            </div>
            
            <div class="category-card slide-in-right delay-3">
                <img src="images/Caesar%20Salad.jpg" alt="Salads" class="category-image">
                <div class="category-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3>Salads</h3>
                <p>Fresh and healthy options</p>
                <a href="salad.php" class="btn btn-secondary" onmouseover="this.style.backgroundColor='skyblue'; this.style.color='#000';" onmouseout="this.style.backgroundColor=''; this.style.color='';">View Salads</a>
            </div>

            <div class="category-card fade-in-up delay-4">
                <img src="images/chinease%20rice.webp" alt="Pakistani Cuisine" class="category-image">
                <div class="category-icon">
                    <i class="fas fa-pepper-hot"></i>
                </div>
                <h3>Pakistani Cuisine</h3>
                <p>Upcoming desi favorites and classics</p>
                <a href="upcoming_dishes.php" class="btn btn-secondary" onmouseover="this.style.backgroundColor='skyblue'; this.style.color='#000';" onmouseout="this.style.backgroundColor=''; this.style.color='';">View Upcoming</a>
            </div>
        </div>
    </section>

    <!-- Featured Items -->
    <section class="container">
        <div class="section-title fade-in">
            <h2>Popular Dishes</h2>
        </div>
        
        <div class="menu-grid">
            <?php
            $count = 0;
            
            foreach($featuredItems as $item):
                $animationClass = "fade-in-up delay-" . ($count % 5 + 1);
                $count++;
                
                // Use only uploaded/local images from the database
                $imageUrl = $item['image'];
                $hasLocalPath = !empty($imageUrl) && !preg_match('/^https?:\/\//', $imageUrl);
                if (!$hasLocalPath) {
                    $imageUrl = '';
                }
            ?>
            <div class="menu-card <?php echo $animationClass; ?>">
                <div class="menu-image"<?php echo $imageUrl ? ' style="background-image: url(' . htmlspecialchars($imageUrl) . ');"' : ''; ?>></div>
                <div class="menu-content">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p class="menu-price">$<?php echo number_format($item['price'], 2); ?></p>
                    <p class="menu-description"><?php echo htmlspecialchars($item['description']); ?></p>
                    <button class="add-to-cart" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price']; ?>">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center fade-in delay-5">
            <a href="menu.php" class="btn btn-secondary" onmouseover="this.style.backgroundColor='skyblue'; this.style.color='#000';" onmouseout="this.style.backgroundColor=''; this.style.color='';">View Full Menu <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

    <!-- Footer -->
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

    <!-- JavaScript -->
    <script src="js/cart.js"></script>
    <script src="js/animations.js"></script>
    <script>
        // Add to cart functionality
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
        
        // Update cart count on load
        updateCartCount();

        // Hero slider
        (function() {
            const slides = Array.from(document.querySelectorAll('.hero-slide'));
            const dotsContainer = document.querySelector('.hero-dots');
            const prevBtn = document.querySelector('.hero-prev');
            const nextBtn = document.querySelector('.hero-next');
            const hero = document.querySelector('.hero');
            let currentIndex = 0;
            let timerId = null;

            if (!slides.length) return;

            const setActive = (index) => {
                slides[currentIndex].classList.remove('active');
                if (dotsContainer) {
                    dotsContainer.children[currentIndex].classList.remove('active');
                }
                currentIndex = (index + slides.length) % slides.length;
                slides[currentIndex].classList.add('active');
                if (dotsContainer) {
                    dotsContainer.children[currentIndex].classList.add('active');
                }
            };

            const start = () => {
                timerId = setInterval(() => setActive(currentIndex + 1), 5000);
            };

            const stop = () => {
                if (timerId) clearInterval(timerId);
                timerId = null;
            };

            if (dotsContainer) {
                slides.forEach((_, i) => {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'hero-dot' + (i === 0 ? ' active' : '');
                    dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                    dot.addEventListener('click', () => {
                        stop();
                        setActive(i);
                        start();
                    });
                    dotsContainer.appendChild(dot);
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    stop();
                    setActive(currentIndex - 1);
                    start();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    stop();
                    setActive(currentIndex + 1);
                    start();
                });
            }

            if (hero) {
                hero.addEventListener('mouseenter', stop);
                hero.addEventListener('mouseleave', start);
            }

            start();
        })();
    </script>
</body>
</html>



