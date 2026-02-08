<?php
session_start();
require_once 'includes/functions.php';

$category = isset($_GET['category']) ? $_GET['category'] : null;
$menuItems = getMenuItems($category);
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Menu - Foodey</title>
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
                    <li><a href="menu.php" class="active"><i class="fas fa-burger"></i> Menu</a></li>
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

    <section class="hero" style="background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');">
        <div class="container">
            <h1>Our Delicious Menu</h1>
            <p>Explore our wide variety of dishes prepared with love</p>
        </div>
    </section>

    <section class="container">
        <div class="category-filters fade-in">
            <button class="filter-btn <?php echo !$category ? 'active' : ''; ?>" data-category="">All Items</button>
            <?php foreach($categories as $cat): ?>
            <button class="filter-btn <?php echo $category == $cat ? 'active' : ''; ?>" data-category="<?php echo $cat; ?>">
                <?php echo htmlspecialchars($cat); ?>
            </button>
            <?php endforeach; ?>
        </div>
        
        <div class="menu-grid">
            <?php
            $count = 0;
            foreach($menuItems as $item):
                $animationClass = "fade-in delay-" . ($count % 5 + 1);
                $count++;
                
                // Hardcoded local image references (project assets)
                $imageReferences = [
                    'Margherita Pizza' => 'images/Margherita%20Pizza.jpg',
                    'Pepperoni Pizza' => 'images/Pepperoni%20Pizza.jpg',
                    'BBQ Chicken Pizza' => 'images/BBQ%20Chicken%20Pizza.jpg',
                    'Cheeseburger' => 'images/Cheeseburger.jpg',
                    'Bacon Burger' => 'images/Bacon%20Burger.jpg',
                    'Caesar Salad' => 'images/Caesar%20Salad.jpg',
                    'Greek Salad' => 'images/Greek%20Salad.jpg',
                    'French Fries' => 'images/French%20Fries.jpg',
                    'Onion Rings' => 'images/Onion%20Rings.jpg',
                    'Coca Cola' => 'images/Coca%20Cola.jpg',
                    'Orange Juice' => 'images/Orange%20Juice.jpg',
                    'Chocolate Brownie' => 'images/Chocolate%20Brownie.jpg',
                    'Chinease Rice' => 'images/chinease%20rice.webp',
                ];
                
                // Get image URL - prefer uploaded/local path, otherwise use hardcoded local references
                $imageUrl = $item['image'];
                $hasLocalPath = !empty($imageUrl) && !preg_match('/^https?:\/\//', $imageUrl);
                if (!$hasLocalPath) {
                    $imageUrl = $imageReferences[$item['name']] ?? 'images/menu/menu_20260127_062404_d038c01b.webp';
                }
            ?>
            <div class="menu-card <?php echo $animationClass; ?>">
                <div class="menu-image" style="background-image: url('<?php echo htmlspecialchars($imageUrl); ?>');"></div>
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
        
        <?php if (empty($menuItems)): ?>
        <div class="no-items-message" style="text-align: center; padding: 3rem;">
            <h3>No items found in this category.</h3>
            <p>Please try another category or check back soon!</p>
            <a href="menu.php" class="btn">View All Items</a>
        </div>
        <?php endif; ?>
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
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const category = this.dataset.category;
                window.location.href = category ? `menu.php?category=${category}` : 'menu.php';
            });
        });
        
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
    </script>
</body>
</html>
[file content end]

