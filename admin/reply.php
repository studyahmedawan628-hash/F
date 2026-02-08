<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'])) {
    $messageId = (int)$_POST['message_id'];
    $replyText = trim($_POST['reply'] ?? '');

    if ($replyText === '') {
        $error = 'Reply cannot be empty.';
    } else {
        $stmt = $pdo->prepare('UPDATE contact_messages SET reply = ?, replied_at = CURRENT_TIMESTAMP WHERE id = ?');
        if ($stmt->execute([$replyText, $messageId])) {
            $message = 'Reply saved successfully.';
        } else {
            $error = 'Failed to save reply.';
        }
    }
}

$stmt = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Messages - Foodey Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
            background: #f5f7fa;
        }

        .admin-sidebar {
            background: linear-gradient(180deg, #2f3542 0%, #1a1e27 100%);
            color: white;
            padding: 0;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            color: #ff6b6b;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .sidebar-header p {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .admin-info {
            background: rgba(255,255,255,0.1);
            padding: 0.8rem;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .admin-info p {
            margin: 0;
            font-size: 0.9rem;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 0.5rem;
            color: #ff6b6b;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem 1.5rem;
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255, 107, 107, 0.1);
            border-left-color: #ff6b6b;
            color: white;
        }

        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
        }

        .content {
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: #2f3542;
            margin: 0;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .message-header h3 {
            margin: 0;
            color: #2f3542;
        }

        .message-meta {
            color: #777;
            font-size: 0.9rem;
        }

        .message-body {
            margin-bottom: 1rem;
            color: #444;
        }

        .reply-box {
            margin-top: 1rem;
        }

        .reply-box label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .reply-box textarea {
            width: 100%;
            min-height: 120px;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .reply-actions {
            margin-top: 0.8rem;
            display: flex;
            gap: 0.5rem;
        }

        .reply-status {
            margin-top: 0.8rem;
            font-size: 0.9rem;
            color: #555;
        }

        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-utensils"></i> Foodey</h2>
                <p>Admin Panel</p>
                <div class="admin-info">
                    <p><i class="fas fa-user"></i> <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></p>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="edit_idem.php"><i class="fas fa-utensils"></i> Manage Menu</a></li>
                <li><a href="manage_orders.php"><i class="fas fa-list-alt"></i> Manage Orders</a></li>
                <li><a href="manage_riders.php"><i class="fas fa-motorcycle"></i> Manage Riders</a></li>
                <li><a href="reply.php" class="active"><i class="fas fa-envelope"></i> Reply Messages</a></li>
            </ul>
        </aside>

        <main class="content">
            <div class="page-header">
                <h1>Contact Messages</h1>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-success fade-in">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error fade-in">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if (empty($messages)): ?>
            <div class="message-card">
                <p>No contact messages yet.</p>
            </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <div class="message-card">
                    <div class="message-header">
                        <h3><?php echo htmlspecialchars($msg['subject']); ?></h3>
                        <div class="message-meta">
                            <?php echo date('M j, Y g:i a', strtotime($msg['created_at'])); ?>
                        </div>
                    </div>
                    <div class="message-meta">
                        <strong><?php echo htmlspecialchars($msg['name']); ?></strong> | <?php echo htmlspecialchars($msg['email']); ?>
                        <?php if (!empty($msg['phone'])): ?> | <?php echo htmlspecialchars($msg['phone']); ?><?php endif; ?>
                    </div>
                    <div class="message-body">
                        <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    </div>

                    <form method="POST" action="reply.php" class="reply-box">
                        <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                        <label for="reply-<?php echo (int)$msg['id']; ?>">Reply</label>
                        <textarea id="reply-<?php echo (int)$msg['id']; ?>" name="reply" class="form-control" required><?php echo htmlspecialchars($msg['reply'] ?? ''); ?></textarea>
                        <div class="reply-actions">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-paper-plane"></i> Save Reply
                            </button>
                        </div>
                        <?php if (!empty($msg['reply'])): ?>
                        <div class="reply-status">
                            <i class="fas fa-check"></i> Replied
                            <?php if (!empty($msg['replied_at'])): ?>
                                on <?php echo date('M j, Y g:i a', strtotime($msg['replied_at'])); ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
