<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['delivery_logged_in']) && $_SESSION['delivery_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$info = '';

if (!tableExists('riders') && !tableExists('delivery_boys')) {
    $info = 'Rider tables are not installed yet. Please create the riders table and add an assignment column on orders.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $result = verifyDeliveryBoyCredentials($username, $password);
        if (!($result['success'] ?? false)) {
            $error = $result['message'] ?? 'Login failed.';
        } else {
            $user = $result['user'];
            $_SESSION['delivery_logged_in'] = true;
            $_SESSION['delivery_boy_id'] = (int)$user['id'];
            $_SESSION['delivery_boy_username'] = (string)$user['username'];
            $_SESSION['delivery_boy_name'] = (string)$user['full_name'];

            header('Location: dashboard.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Login - Foodey</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 2rem;
        }
        .login-box {
            background: white;
            border-radius: 16px;
            padding: 2.75rem;
            box-shadow: 0 25px 60px rgba(0,0,0,0.18);
            width: 100%;
            max-width: 420px;
            animation: fadeInUp 0.5s ease;
        }
        .login-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }
        .login-header h1 {
            color: #1f2937;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            font-size: 1.8rem;
        }
        .login-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }
        .role-chip {
            display: inline-block;
            background: rgba(37, 99, 235, 0.12);
            color: #2563eb;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.8rem;
            margin-top: 0.6rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.45rem;
            color: #374151;
            font-weight: 600;
            font-size: 0.92rem;
        }
        .input-with-icon {
            position: relative;
        }
        .input-with-icon i {
            position: absolute;
            left: 0.95rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }
        .input-with-icon input {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.85rem;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.25s ease;
            background: #f9fafb;
        }
        .input-with-icon input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            background: white;
        }
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            border: 1px solid transparent;
        }
        .alert-error {
            background: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .alert-info {
            background: #e0f2fe;
            border-color: #bae6fd;
            color: #0c4a6e;
        }
        .btn-login {
            width: 100%;
            padding: 0.95rem 1.1rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.35);
        }
        .login-footer {
            margin-top: 1.25rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
        }
        .login-footer a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .demo-credentials {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }
        .demo-credentials p {
            margin: 0.25rem 0;
        }
        .demo-credentials code {
            background: #eef2ff;
            color: #1d4ed8;
            padding: 0.1rem 0.35rem;
            border-radius: 6px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><i class="fas fa-motorcycle"></i> Foodey</h1>
                <p>Delivery partner portal</p>
                <div class="role-chip">Delivery Boy Panel</div>
            </div>

            <?php if ($info): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($info); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="login-footer">
                Admin? <a href="../admin/login.php">Go to Admin Login</a>
            </div>

            <div class="demo-credentials">
                <p><strong>Demo Credentials:</strong></p>
                <p>Username: <code>rider1</code></p>
                <p>Password: <code>rider123</code></p>
            </div>
        </div>
    </div>
</body>
</html>
