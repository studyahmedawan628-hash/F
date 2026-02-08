<?php
require_once __DIR__ . '/_auth.php';
$__me = deliveryBoySessionUser();
$__active = $__active ?? 'dashboard';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="brand"><i class="fas fa-motorcycle"></i> Foodey</div>
        <div class="sidebar-sub">Delivery Partner Panel</div>
        <div class="user-card">
            <strong><?php echo htmlspecialchars($__me['name']); ?></strong>
            <div class="muted">@<?php echo htmlspecialchars($__me['username']); ?></div>
        </div>
    </div>
    <ul class="nav">
        <li><a class="<?php echo $__active === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a class="<?php echo $__active === 'history' ? 'active' : ''; ?>" href="history.php"><i class="fas fa-clock-rotate-left"></i> Delivery History</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>

