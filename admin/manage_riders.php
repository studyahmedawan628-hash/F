<?php
session_start();
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $result = addRider([
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'status' => 'active'
        ]);
        if ($result['success'] ?? false) {
            $message = $result['message'] ?? 'Rider added successfully.';
        } else {
            $error = $result['message'] ?? 'Failed to add rider.';
        }
    } elseif ($action === 'delete') {
        $riderId = (int)($_POST['rider_id'] ?? 0);
        if ($riderId > 0) {
            $result = deactivateRider($riderId);
            if ($result['success'] ?? false) {
                $message = $result['message'] ?? 'Rider removed successfully.';
            } else {
                $error = $result['message'] ?? 'Failed to remove rider.';
            }
        } else {
            $error = 'Invalid rider selected.';
        }
    }
}

$riders = getRiders(true);
$ridersTableMissing = !tableExists('riders');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Riders - Foodey Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
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

        .alert-info {
            background: #e0f2fe;
            color: #0c4a6e;
            border: 1px solid #bae6fd;
        }

        .form-card,
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fafafa;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff6b6b;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.12);
            background: #fff;
        }

        .btn-primary {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 0.7rem 1.4rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-danger {
            background: #ff4757;
            color: white;
            border: none;
            padding: 0.45rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.9rem 0.7rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f8f9fa;
            color: #2f3542;
            font-weight: 600;
        }

        .status-pill {
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            background: #e5e7eb;
            color: #4b5563;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .help-text {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 0.35rem;
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
                <li><a href="manage_riders.php" class="active"><i class="fas fa-motorcycle"></i> Manage Riders</a></li>
                <li><a href="reply.php"><i class="fas fa-envelope"></i> Reply Messages</a></li>
            </ul>
        </aside>

        <main class="content">
            <div class="page-header">
                <h1>Manage Riders</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($ridersTableMissing): ?>
                <div class="alert alert-info">Riders table not found. Please import the database schema before adding riders.</div>
            <?php endif; ?>

            <div class="form-card">
                <h2>Add Rider</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input class="form-control" type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input class="form-control" type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input class="form-control" type="text" id="full_name" name="full_name" placeholder="Defaults to username">
                            <div class="help-text">If left blank, the username will be used.</div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input class="form-control" type="text" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" type="email" id="email" name="email">
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button class="btn-primary" type="submit"><i class="fas fa-user-plus"></i> Add Rider</button>
                    </div>
                </form>
            </div>

            <div class="table-card">
                <h2>Riders</h2>
                <?php if (empty($riders)): ?>
                    <p>No riders found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riders as $rider): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($rider['username']); ?></td>
                                        <td><?php echo htmlspecialchars($rider['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($rider['phone'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($rider['email'] ?? ''); ?></td>
                                        <td>
                                            <?php $status = $rider['status'] ?? 'inactive'; ?>
                                            <span class="status-pill status-<?php echo htmlspecialchars($status); ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $rider['created_at'] ? date('M j, Y', strtotime($rider['created_at'])) : '-'; ?></td>
                                        <td>
                                            <?php if (($rider['status'] ?? '') === 'active'): ?>
                                                <form method="POST" action="" onsubmit="return confirm('Remove this rider?');" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="rider_id" value="<?php echo (int)$rider['id']; ?>">
                                                    <button class="btn-danger" type="submit"><i class="fas fa-user-times"></i> Remove</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="help-text">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
