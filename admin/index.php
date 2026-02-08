<?php
session_start();
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
$stats = getDashboardStats();
$recentOrders = getAllOrders(10); // Last 10 orders
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Foodey</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
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
    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <li><a href="edit_idem.php"><i class="fas fa-utensils"></i> Manage Menu</a></li>
    <li><a href="manage_orders.php"><i class="fas fa-list-alt"></i> Manage Orders</a></li>
    <li><a href="manage_riders.php"><i class="fas fa-motorcycle"></i> Manage Riders</a></li>
    <li><a href="reply.php"><i class="fas fa-envelope"></i> Reply Messages</a></li>
</ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="content-header">
                <h1>Dashboard Overview</h1>
                <p>Welcome to the Foodey Admin Panel - <?php echo date('F j, Y'); ?></p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card fade-in-up delay-1">
                    <div class="stat-icon" style="background: #2ed573;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <p class="stat-number"><?php echo $stats['total_orders']; ?></p>
                        <p class="stat-change">All time orders</p>
                    </div>
                </div>
                
                <div class="stat-card fade-in-up delay-2">
                    <div class="stat-icon" style="background: #1e90ff;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pending Orders</h3>
                        <p class="stat-number"><?php echo $stats['pending_orders']; ?></p>
                        <p class="stat-change">Awaiting processing</p>
                    </div>
                </div>
                
                <div class="stat-card fade-in-up delay-3">
                    <div class="stat-icon" style="background: #ffa502;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Today's Orders</h3>
                        <p class="stat-number"><?php echo $stats['today_orders']; ?></p>
                        <p class="stat-change">Orders today</p>
                    </div>
                </div>
                
                <div class="stat-card fade-in-up delay-4">
                    <div class="stat-icon" style="background: #ff4757;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Revenue</h3>
                        <p class="stat-number">$<?php echo number_format($stats['total_revenue'], 2); ?></p>
                        <p class="stat-change">All time revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders Table -->
            <div class="recent-orders fade-in-up delay-2">
                <div class="table-header">
                    <h2>Recent Orders</h2>
                    <a href="manage_orders.php" class="btn btn-sm">View All Orders</a>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentOrders as $order): ?>
                            <tr>
                                <td><strong><?php echo $order['order_number']; ?></strong></td>
                                <td>
                                    <div class="customer-info">
                                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                        <small><?php echo $order['customer_email']; ?></small>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, g:i a', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
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
        
        .admin-content {
            padding: 2rem;
            overflow-y: auto;
        }
        
        .content-header {
            margin-bottom: 2rem;
        }
        
        .content-header h1 {
            color: #2f3542;
            margin-bottom: 0.5rem;
        }
        
        .content-header p {
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .stat-info h3 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2f3542;
            margin-bottom: 0.3rem;
        }
        
        .stat-change {
            font-size: 0.8rem;
            color: #888;
        }
        
        .recent-orders {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .table-header h2 {
            color: #2f3542;
            margin: 0;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            color: #666;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .customer-info {
            display: flex;
            flex-direction: column;
        }
        
        .customer-info small {
            color: #888;
            font-size: 0.8rem;
        }
        
        .order-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-preparing { background: #cce5ff; color: #004085; }
        .status-out_for_delivery { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .quick-actions {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .action-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            text-decoration: none;
            color: #2f3542;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            background: #ff6b6b;
            color: white;
            transform: translateY(-3px);
        }
        
        .action-card i {
            font-size: 2rem;
        }
        
        .action-card span {
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
