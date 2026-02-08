<?php
session_start();
require_once 'includes/db.php';

$errors = [];
$success = false;
$lookupEmail = '';
$replyMessages = [];
$replyError = '';

$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['subject'] = trim($_POST['subject'] ?? '');
    $formData['message'] = trim($_POST['message'] ?? '');

    if ($formData['name'] === '') {
        $errors['name'] = 'Name is required.';
    }

    if ($formData['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($formData['subject'] === '') {
        $errors['subject'] = 'Subject is required.';
    }

    if ($formData['message'] === '') {
        $errors['message'] = 'Message is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $formData['name'],
                $formData['email'],
                $formData['phone'] !== '' ? $formData['phone'] : null,
                $formData['subject'],
                $formData['message']
            ]);
            $success = true;
            $lookupEmail = $formData['email'];
            $formData = [
                'name' => '',
                'email' => '',
                'phone' => '',
                'subject' => '',
                'message' => ''
            ];
        } catch (PDOException $e) {
            $errors['database'] = 'Failed to send your message. Please try again later.';
        }
    }
}

if (isset($_GET['email'])) {
    $lookupEmail = trim($_GET['email']);
}

if ($lookupEmail !== '') {
    if (!filter_var($lookupEmail, FILTER_VALIDATE_EMAIL)) {
        $replyError = 'Please enter a valid email address to view replies.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC');
        $stmt->execute([$lookupEmail]);
        $replyMessages = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Foodey</title>
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
            <h2>Contact Us</h2>
        </div>

        <?php if ($success): ?>
        <div class="checkout-form fade-in" style="border-left: 4px solid var(--success);">
            <p><i class="fas fa-check-circle" style="color: var(--success);"></i> Thank you! Your message has been sent successfully.</p>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="checkout-form fade-in" style="border-left: 4px solid var(--primary);">
            <h4><i class="fas fa-exclamation-triangle" style="color: var(--primary);"></i> Please fix the following errors:</h4>
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form class="checkout-form fade-in-up" method="POST" action="contact_us.php">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?php echo htmlspecialchars($formData['name']); ?>">
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?php echo htmlspecialchars($formData['email']); ?>">
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?php echo htmlspecialchars($formData['phone']); ?>">
            </div>

            <div class="form-group">
                <label for="subject"><i class="fas fa-tag"></i> Subject *</label>
                <input type="text" id="subject" name="subject" class="form-control" required
                       value="<?php echo htmlspecialchars($formData['subject']); ?>">
            </div>

            <div class="form-group">
                <label for="message"><i class="fas fa-comment-dots"></i> Message *</label>
                <textarea id="message" name="message" class="form-control" rows="5" required><?php echo htmlspecialchars($formData['message']); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn" style="width: 100%; padding: 1rem;">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </div>
        </form>

        <div class="section-title fade-in" style="margin-top: 3rem;">
            <h2>View Admin Replies</h2>
        </div>

        <form class="checkout-form fade-in-up" method="GET" action="contact_us.php">
            <div class="form-group">
                <label for="reply-email"><i class="fas fa-envelope"></i> Your Email Address</label>
                <input type="email" id="reply-email" name="email" class="form-control" required
                       value="<?php echo htmlspecialchars($lookupEmail); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 1rem;">
                    <i class="fas fa-search"></i> Check Replies
                </button>
            </div>
        </form>

        <?php if ($replyError): ?>
        <div class="checkout-form fade-in" style="border-left: 4px solid var(--primary);">
            <p><i class="fas fa-exclamation-triangle" style="color: var(--primary);"></i> <?php echo htmlspecialchars($replyError); ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($replyMessages)): ?>
        <div class="checkout-form fade-in-up">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-inbox"></i> Replies</h3>
            <?php foreach ($replyMessages as $msg): ?>
                <div style="border-bottom: 1px solid #eee; padding: 1rem 0;">
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject']); ?></p>
                    <p><strong>Your Message:</strong> <?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    <?php if (!empty($msg['reply'])): ?>
                        <p><strong>Admin Reply:</strong> <?php echo nl2br(htmlspecialchars($msg['reply'])); ?></p>
                        <?php if (!empty($msg['replied_at'])): ?>
                            <p style="color: #777; font-size: 0.9rem;">Replied on <?php echo date('M j, Y g:i a', strtotime($msg['replied_at'])); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color: #777;">No reply yet.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php elseif ($lookupEmail !== '' && empty($replyError)): ?>
        <div class="checkout-form fade-in">
            <p>No messages found for this email address.</p>
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
    <script src="js/animations.js"></script>
    <script>
        updateCartCount();
    </script>
</body>
</html>
