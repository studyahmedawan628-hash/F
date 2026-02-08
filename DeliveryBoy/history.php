<?php
require_once __DIR__ . '/_auth.php';
requireDeliveryBoyAuth();

$me = deliveryBoySessionUser();
$deliveryBoyId = $me['id'];

$statusFilter = strtolower(trim($_GET['status'] ?? ''));
if ($statusFilter !== '' && !in_array($statusFilter, ['delivered', 'cancelled'], true)) {
    $statusFilter = '';
}

$orders = getAssignedOrdersForDeliveryBoy($deliveryBoyId, $statusFilter ?: null, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery History - Foodey</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .panel {
            min-height: 100vh;
            background: #f4f6fb;
            display: grid;
            grid-template-columns: 260px 1fr;
        }
        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
            color: white;
            padding: 0;
            box-shadow: 4px 0 20px rgba(15, 23, 42, 0.2);
        }
        .sidebar-header {
            padding: 2rem 1.6rem 1.6rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-weight: 800;
            font-size: 1.35rem;
            color: #60a5fa;
            margin-bottom: 0.4rem;
        }
        .sidebar-sub {
            color: #cbd5e1;
            font-size: 0.92rem;
        }
        .user-card {
            margin-top: 1rem;
            padding: 0.85rem 0.95rem;
            border-radius: 12px;
            background: rgba(96, 165, 250, 0.12);
            border: 1px solid rgba(96, 165, 250, 0.2);
        }
        .user-card strong { display: block; font-size: 1rem; }
        .nav {
            list-style: none;
            padding: 1rem 0.5rem;
            margin: 0;
        }
        .nav a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #e5e7eb;
            text-decoration: none;
            padding: 0.95rem 1.1rem;
            border-radius: 12px;
            transition: all 0.2s ease;
            font-weight: 600;
        }
        .nav a i { width: 20px; text-align: center; }
        .nav a:hover,
        .nav a.active {
            background: rgba(96, 165, 250, 0.18);
            color: white;
        }
        .content {
            padding: 2rem 2.2rem 2.6rem;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.35rem;
            flex-wrap: wrap;
        }
        .topbar h1 { margin: 0; color: #0f172a; font-size: 1.85rem; }
        .topbar p { margin: 0.3rem 0 0; color: #64748b; }
        .btn-link {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.7rem 1rem;
            border-radius: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            color: #0f172a;
            text-decoration: none;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }
        .filters {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .filter-chip {
            padding: 0.6rem 0.95rem;
            border-radius: 999px;
            background: white;
            border: 1px solid #e5e7eb;
            text-decoration: none;
            color: #0f172a;
            font-weight: 800;
        }
        .filter-chip.active {
            background: rgba(37, 99, 235, 0.12);
            border-color: rgba(37, 99, 235, 0.25);
            color: #1d4ed8;
        }
        .card {
            background: white;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.07);
            overflow: hidden;
        }
        .card-head {
            padding: 1.1rem 1.4rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.6rem;
        }
        .card-head h2 { margin: 0; color: #0f172a; font-size: 1.15rem; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        th, td {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
            vertical-align: middle;
        }
        th {
            color: #475569;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            font-weight: 900;
            background: #f8fafc;
        }
        tr:hover td { background: #f8fafc; }
        .order-number { font-weight: 900; color: #0f172a; }
        .muted { color: #64748b; font-size: 0.92rem; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            font-weight: 900;
            font-size: 0.82rem;
        }
        .status-delivered { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .btn-small {
            padding: 0.55rem 0.75rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: white;
            font-weight: 900;
            cursor: pointer;
            text-decoration: none;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .empty {
            padding: 2.2rem 1.6rem 2.4rem;
            text-align: center;
            color: #64748b;
        }
        .empty i {
            font-size: 2.6rem;
            margin-bottom: 0.6rem;
            color: #94a3b8;
        }
        @media (max-width: 1100px) {
            .panel { grid-template-columns: 1fr; }
            .sidebar { position: sticky; top: 0; z-index: 10; }
        }
        @media (max-width: 700px) {
            .content { padding: 1.5rem 1.1rem 2rem; }
        }
    </style>
</head>
<body>
    <div class="panel">
        <?php $__active = 'history'; require __DIR__ . '/_sidebar.php'; ?>

        <main class="content">
            <div class="topbar">
                <div>
                    <h1>Delivery History</h1>
                    <p>Completed and cancelled deliveries assigned to you.</p>
                </div>
                <a class="btn-link" href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>

            <div class="filters">
                <?php
                $chips = [
                    '' => 'All History',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                ];
                foreach ($chips as $value => $label):
                    $active = ($statusFilter === $value) || ($statusFilter === '' && $value === '');
                ?>
                    <a class="filter-chip <?php echo $active ? 'active' : ''; ?>" href="history.php<?php echo $value !== '' ? '?status=' . urlencode($value) : ''; ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <section class="card">
                <div class="card-head">
                    <h2>Past Deliveries</h2>
                    <div class="muted"><?php echo count($orders); ?> order(s)</div>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="empty">
                        <i class="fas fa-clipboard-check"></i>
                        <div>No history yet for this filter.</div>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Address</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                    $normalized = normalizeOrderStatus($order['status']);
                                    $badgeClass = orderStatusBadgeClass($order['status']);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                                            <div class="muted">#<?php echo (int)$order['id']; ?> • <?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></div>
                                        </td>
                                        <td>
                                            <div><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></div>
                                            <div class="muted"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['customer_address']); ?></td>
                                        <td><strong>$<?php echo number_format((float)$order['total_amount'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo htmlspecialchars($badgeClass); ?>">
                                                <?php echo htmlspecialchars(orderStatusLabel($normalized)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a class="btn-small" href="order.php?id=<?php echo (int)$order['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
