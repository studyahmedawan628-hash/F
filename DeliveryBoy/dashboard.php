<?php
require_once __DIR__ . '/_auth.php';
requireDeliveryBoyAuth();

$me = deliveryBoySessionUser();
$deliveryBoyId = $me['id'];

$message = '';
$error = '';

$assignmentColumn = getOrderAssignmentColumn();
if ($assignmentColumn === null) {
    $error = 'Orders table is missing a rider assignment column (assigned_rider_id, rider_id, or assigned_delivery_boy_id).';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = (string)($_POST['new_status'] ?? '');
    $notes = trim((string)($_POST['notes'] ?? ''));

    $result = updateOrderStatusAsDeliveryBoy($orderId, $deliveryBoyId, $newStatus, $notes);
    if ($result['success'] ?? false) {
        $message = $result['message'] ?? 'Order updated.';
    } else {
        $error = $result['message'] ?? 'Failed to update order.';
    }
}

$statusFilter = strtolower(trim($_GET['status'] ?? ''));
$isHistoryFilter = in_array($statusFilter, ['delivered', 'cancelled'], true);

if ($isHistoryFilter) {
    header('Location: history.php?status=' . urlencode($statusFilter));
    exit();
}

// Default rider tab is "Out for Delivery"
if ($statusFilter === '') {
    $statusFilter = 'out_for_delivery';
}

$orders = getAssignedOrdersForDeliveryBoy($deliveryBoyId, $statusFilter ?: null, false);

// Stats for this delivery boy
$stats = [
    'out_for_delivery' => 0,
    'delivered' => 0,
    'cancelled' => 0,
];

try {
    if ($assignmentColumn !== null) {
        $stmt = $pdo->prepare("
            SELECT status, COUNT(*) AS total
            FROM orders
            WHERE {$assignmentColumn} = ?
            GROUP BY status
        ");
        $stmt->execute([$deliveryBoyId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $normalized = normalizeOrderStatus($row['status']);
            $count = (int)$row['total'];
            if (in_array($normalized, ['assigned', 'pending', 'preparing', 'confirmed', 'placed', 'out_for_delivery'], true)) {
                $stats['out_for_delivery'] += $count;
            } elseif ($normalized === 'delivered') {
                $stats['delivered'] += $count;
            } elseif ($normalized === 'cancelled') {
                $stats['cancelled'] += $count;
            }
        }
    }
} catch (Exception $e) {
    // Keep the page functional even if stats query fails.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard - Foodey</title>
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
        .user-card strong {
            display: block;
            font-size: 1rem;
        }
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
        .nav a i {
            width: 20px;
            text-align: center;
        }
        .nav a:hover,
        .nav a.active {
            background: rgba(96, 165, 250, 0.18);
            color: white;
        }
        .content {
            padding: 2rem 2.2rem 2.4rem;
        }
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.7rem;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .page-head h1 {
            margin: 0;
            color: #0f172a;
            font-size: 1.85rem;
        }
        .page-head p {
            margin: 0.25rem 0 0;
            color: #64748b;
        }
        .actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }
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
            font-weight: 700;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        }
        .btn-link.primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
        }
        .alerts {
            margin-bottom: 1.25rem;
        }
        .alert {
            padding: 0.95rem 1.1rem;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 0.75rem;
            border: 1px solid transparent;
        }
        .alert-success {
            background: #dcfce7;
            border-color: #bbf7d0;
            color: #166534;
        }
        .alert-error {
            background: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.4rem;
        }
        .stat {
            background: white;
            border-radius: 16px;
            padding: 1.25rem 1.25rem 1.15rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .stat .label {
            color: #64748b;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .stat .value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 0.25rem;
        }
        .filters {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-bottom: 1.15rem;
        }
        .filter-chip {
            padding: 0.6rem 0.95rem;
            border-radius: 999px;
            background: white;
            border: 1px solid #e5e7eb;
            text-decoration: none;
            color: #0f172a;
            font-weight: 700;
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
            gap: 0.6rem;
            flex-wrap: wrap;
        }
        .card-head h2 {
            margin: 0;
            font-size: 1.15rem;
            color: #0f172a;
        }
        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }
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
            font-weight: 800;
            background: #f8fafc;
        }
        tr:hover td {
            background: #f8fafc;
        }
        .order-number {
            font-weight: 800;
            color: #0f172a;
        }
        .muted {
            color: #64748b;
            font-size: 0.92rem;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.82rem;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-preparing { background: #dbeafe; color: #1e40af; }
        .status-out_for_delivery { background: #e0f2fe; color: #0c4a6e; }
        .status-delivered { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .row-actions {
            display: flex;
            gap: 0.45rem;
            flex-wrap: wrap;
        }
        .btn-small {
            padding: 0.55rem 0.75rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: white;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .btn-small.primary {
            border: none;
            background: #2563eb;
            color: white;
        }
        .btn-small.success {
            border: none;
            background: #16a34a;
            color: white;
        }
        .btn-small.warn {
            border: none;
            background: #dc2626;
            color: white;
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
            .panel {
                grid-template-columns: 1fr;
            }
            .sidebar {
                position: sticky;
                top: 0;
                z-index: 10;
            }
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 680px) {
            .content {
                padding: 1.6rem 1.2rem 2rem;
            }
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="panel">
        <?php $__active = 'dashboard'; require __DIR__ . '/_sidebar.php'; ?>

        <main class="content">
            <div class="page-head">
                <div>
                    <h1>Assigned Orders</h1>
                    <p>Only orders assigned to you are visible and actionable.</p>
                </div>
                <div class="actions">
                    <a class="btn-link" href="history.php"><i class="fas fa-clock-rotate-left"></i> View History</a>
                    <a class="btn-link primary" href="../admin/manage_orders.php"><i class="fas fa-user-shield"></i> Admin Panel</a>
                </div>
            </div>

            <div class="alerts">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </div>

            <section class="stats">
                <div class="stat">
                    <div class="label">Out for Delivery</div>
                    <div class="value"><?php echo (int)$stats['out_for_delivery']; ?></div>
                </div>
                <div class="stat">
                    <div class="label">Delivered</div>
                    <div class="value"><?php echo (int)$stats['delivered']; ?></div>
                </div>
                <div class="stat">
                    <div class="label">Cancelled</div>
                    <div class="value"><?php echo (int)$stats['cancelled']; ?></div>
                </div>
            </section>

            <div class="filters">
                <?php
                $chips = [
                    'out_for_delivery' => 'Out for Delivery',
                ];
                foreach ($chips as $value => $label):
                    $active = ($statusFilter === $value);
                ?>
                    <a class="filter-chip <?php echo $active ? 'active' : ''; ?>" href="dashboard.php<?php echo $value !== '' ? '?status=' . urlencode($value) : ''; ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </a>
                <?php endforeach; ?>
                <a class="filter-chip" href="history.php?status=delivered">Delivered</a>
                <a class="filter-chip" href="history.php?status=cancelled">Cancelled</a>
            </div>

            <section class="card">
                <div class="card-head">
                    <h2>Active Queue</h2>
                    <div class="muted"><?php echo count($orders); ?> order(s)</div>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="empty">
                        <i class="fas fa-box-open"></i>
                        <div>No active orders match this filter.</div>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                    $normalized = normalizeOrderStatus($order['status']);
                                    $badgeClass = orderStatusBadgeClass($order['status']);
                                    $canOutForDelivery = in_array($normalized, ['assigned', 'pending', 'preparing', 'confirmed', 'placed'], true);
                                    $canDeliver = $normalized === 'out_for_delivery';
                                    $canCancel = !in_array($normalized, ['delivered', 'cancelled'], true);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                                            <div class="muted">#<?php echo (int)$order['id']; ?> | <?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></div>
                                        </td>
                                        <td>
                                            <div><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></div>
                                            <div class="muted"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($order['customer_address']); ?></div>
                                            <?php if (!empty($order['notes'])): ?>
                                                <div class="muted">Note: <?php echo htmlspecialchars($order['notes']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>$<?php echo number_format((float)$order['total_amount'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo htmlspecialchars($badgeClass); ?>">
                                                <?php echo htmlspecialchars(orderStatusLabel($normalized)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="row-actions">
                                                <a class="btn-small" href="order.php?id=<?php echo (int)$order['id']; ?>">
                                                    <i class="fas fa-eye"></i> Details
                                                </a>

                                                <?php if ($canOutForDelivery): ?>
                                                    <form method="POST" action="dashboard.php?status=<?php echo urlencode($statusFilter); ?>" onsubmit="return confirm('Mark this order as Out for Delivery?');">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                                        <input type="hidden" name="new_status" value="out_for_delivery">
                                                        <button type="submit" class="btn-small primary">
                                                            <i class="fas fa-motorcycle"></i> Out for Delivery
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($canDeliver): ?>
                                                    <form method="POST" action="dashboard.php?status=<?php echo urlencode($statusFilter); ?>" onsubmit="return confirm('Mark this order as Delivered?');">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                                        <input type="hidden" name="new_status" value="delivered">
                                                        <button type="submit" class="btn-small success">
                                                            <i class="fas fa-check"></i> Delivered
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($canCancel): ?>
                                                    <form method="POST" action="dashboard.php?status=<?php echo urlencode($statusFilter); ?>" onsubmit="return confirm('Cancel this order?');">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                                        <input type="hidden" name="new_status" value="cancelled">
                                                        <button type="submit" class="btn-small warn">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
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


