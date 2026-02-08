<?php
require_once __DIR__ . '/_auth.php';
requireDeliveryBoyAuth();

$me = deliveryBoySessionUser();
$deliveryBoyId = $me['id'];

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $newStatus = (string)($_POST['new_status'] ?? '');
    $notes = trim((string)($_POST['notes'] ?? ''));
    $result = updateOrderStatusAsDeliveryBoy($orderId, $deliveryBoyId, $newStatus, $notes);
    if ($result['success'] ?? false) {
        $message = $result['message'] ?? 'Order updated.';
    } else {
        $error = $result['message'] ?? 'Failed to update order.';
    }
}

$data = getOrderWithItems($orderId);
$order = $data['order'];
$items = $data['items'];

if (!$order) {
    $error = 'Order not found.';
}

if ($order) {
    $assignmentColumn = getOrderAssignmentColumn();
    if ($assignmentColumn === null || (int)($order[$assignmentColumn] ?? 0) !== $deliveryBoyId) {
        $error = 'This order is not assigned to you.';
        $order = null;
        $items = [];
    }
}

$auditRows = [];
if ($order && tableExists('order_status_log')) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM order_status_log WHERE order_id = ? ORDER BY created_at DESC");
        $stmt->execute([$orderId]);
        $auditRows = $stmt->fetchAll();
    } catch (Exception $e) {
        $auditRows = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Foodey Delivery</title>
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
            margin-bottom: 1.4rem;
            flex-wrap: wrap;
        }
        .topbar h1 {
            margin: 0;
            color: #0f172a;
            font-size: 1.85rem;
        }
        .topbar p {
            margin: 0.3rem 0 0;
            color: #64748b;
        }
        .top-actions {
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
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }
        .grid {
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 1.15rem;
        }
        .card {
            background: white;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.07);
            overflow: hidden;
        }
        .card-head {
            padding: 1.1rem 1.35rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }
        .card-head h2 {
            margin: 0;
            color: #0f172a;
            font-size: 1.15rem;
        }
        .card-body {
            padding: 1.15rem 1.35rem 1.35rem;
        }
        .alert {
            padding: 0.95rem 1.1rem;
            border-radius: 12px;
            font-weight: 700;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }
        .alert-success { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
        .alert-error { background: #fee2e2; border-color: #fecaca; color: #991b1b; }
        .meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem 1rem;
        }
        .meta .item {
            padding: 0.7rem 0.8rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        .meta .label {
            font-size: 0.82rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .meta .value {
            margin-top: 0.25rem;
            font-weight: 800;
            color: #0f172a;
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.85rem 0.6rem;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
            vertical-align: middle;
        }
        th {
            font-size: 0.83rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
        }
        .totals {
            margin-top: 0.75rem;
            display: grid;
            gap: 0.35rem;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            color: #0f172a;
        }
        .status-actions {
            display: grid;
            gap: 0.6rem;
        }
        .status-actions form {
            display: grid;
            gap: 0.45rem;
        }
        .notes-input {
            width: 100%;
            padding: 0.7rem 0.8rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            font-family: inherit;
            resize: vertical;
            min-height: 70px;
        }
        .btn {
            padding: 0.78rem 1rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: white;
            font-weight: 900;
            cursor: pointer;
        }
        .btn.primary { background: #2563eb; border: none; color: white; }
        .btn.success { background: #16a34a; border: none; color: white; }
        .btn.danger { background: #dc2626; border: none; color: white; }
        .audit {
            max-height: 360px;
            overflow: auto;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }
        .audit-row {
            padding: 0.8rem 0.9rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .audit-row:last-child { border-bottom: none; }
        .audit-meta {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.2rem;
        }
        @media (max-width: 1100px) {
            .panel { grid-template-columns: 1fr; }
            .sidebar { position: sticky; top: 0; z-index: 10; }
            .grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 700px) {
            .content { padding: 1.5rem 1.1rem 2rem; }
            .meta { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="panel">
        <?php $__active = 'dashboard'; require __DIR__ . '/_sidebar.php'; ?>

        <main class="content">
            <div class="topbar">
                <div>
                    <h1>Order Details</h1>
                    <p>Review the order, then update the delivery status.</p>
                </div>
                <div class="top-actions">
                    <a class="btn-link" href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                    <a class="btn-link" href="history.php"><i class="fas fa-clock-rotate-left"></i> History</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($order): ?>
                <?php
                $normalized = normalizeOrderStatus($order['status']);
                $badgeClass = orderStatusBadgeClass($order['status']);
                $canOutForDelivery = in_array($normalized, ['assigned', 'pending', 'preparing', 'confirmed', 'placed'], true);
                $canDeliver = $normalized === 'out_for_delivery';
                $canCancel = !in_array($normalized, ['delivered', 'cancelled'], true);
                $allowedNextStatuses = [];
                if ($canOutForDelivery) {
                    $allowedNextStatuses[] = 'out_for_delivery';
                }
                if ($canDeliver) {
                    $allowedNextStatuses[] = 'delivered';
                }
                if ($canCancel) {
                    $allowedNextStatuses[] = 'cancelled';
                }
                ?>
                <div class="grid">
                    <section class="card">
                        <div class="card-head">
                            <h2><?php echo htmlspecialchars($order['order_number']); ?></h2>
                            <span class="badge <?php echo htmlspecialchars($badgeClass); ?>">
                                <?php echo htmlspecialchars(orderStatusLabel($normalized)); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="meta">
                                <div class="item">
                                    <div class="label">Customer</div>
                                    <div class="value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                </div>
                                <div class="item">
                                    <div class="label">Phone</div>
                                    <div class="value"><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                </div>
                                <div class="item">
                                    <div class="label">Email</div>
                                    <div class="value"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                </div>
                                <div class="item">
                                    <div class="label">Ordered At</div>
                                    <div class="value"><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></div>
                                </div>
                                <div class="item" style="grid-column: 1 / -1;">
                                    <div class="label">Delivery Address</div>
                                    <div class="value"><?php echo htmlspecialchars($order['customer_address']); ?></div>
                                </div>
                                <?php if (!empty($order['notes'])): ?>
                                    <div class="item" style="grid-column: 1 / -1;">
                                        <div class="label">Customer Notes</div>
                                        <div class="value"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div style="margin-top: 1rem;">
                                <h3 style="margin: 0 0 0.6rem; color: #0f172a;">Items</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                                                <td><?php echo (int)$item['quantity']; ?></td>
                                                <td>$<?php echo number_format((float)$item['price'], 2); ?></td>
                                                <td>$<?php echo number_format((float)$item['total_price'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="totals">
                                    <div class="row"><span>Subtotal</span><span>$<?php echo number_format((float)$order['subtotal'], 2); ?></span></div>
                                    <div class="row"><span>Tax</span><span>$<?php echo number_format((float)$order['tax'], 2); ?></span></div>
                                    <div class="row" style="font-size: 1.05rem;"><span>Total</span><span>$<?php echo number_format((float)$order['total_amount'], 2); ?></span></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card-head">
                            <h2>Update Status</h2>
                            <div style="font-weight: 800; color: #64748b;">Order #<?php echo (int)$order['id']; ?></div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($allowedNextStatuses)): ?>
                                <form method="POST" onsubmit="return confirm('Update order status?');" style="display: grid; gap: 0.6rem;">
                                    <input type="hidden" name="action" value="update_status">
                                    <div class="form-group">
                                        <label for="rider-status" style="font-weight: 900; color: #0f172a; margin-bottom: 0.35rem; display: block;">Select New Status</label>
                                        <select id="rider-status" name="new_status" class="form-control" required style="padding: 0.8rem 0.9rem; border-radius: 12px; border: 1px solid #e5e7eb; background: #fff;">
                                            <option value="" selected disabled>Choose status</option>
                                            <?php foreach ($allowedNextStatuses as $nextStatus): ?>
                                                <option value="<?php echo htmlspecialchars($nextStatus); ?>">
                                                    <?php echo htmlspecialchars(orderStatusLabel($nextStatus)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="rider-notes" style="font-weight: 900; color: #0f172a; margin-bottom: 0.35rem; display: block;">Notes</label>
                                        <textarea id="rider-notes" name="notes" class="notes-input" placeholder="Optional notes about this status update"></textarea>
                                        <div class="muted" style="margin-top: 0.25rem;">Provide a reason if you cancel the order.</div>
                                    </div>
                                    <button class="btn primary" type="submit"><i class="fas fa-sync-alt"></i> Update Status</button>
                                </form>
                            <?php else: ?>
                                <div style="padding: 0.9rem 1rem; border-radius: 12px; background: #f8fafc; border: 1px solid #e5e7eb; color: #64748b; font-weight: 700;">
                                    No further status updates are allowed for this order.
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($auditRows)): ?>
                                <div style="margin-top: 1.2rem;">
                                    <h3 style="margin: 0 0 0.6rem; color: #0f172a;">Status History</h3>
                                    <div class="audit">
                                        <?php foreach ($auditRows as $row): ?>
                                            <div class="audit-row">
                                                <div style="font-weight: 900; color: #0f172a;">
                                                    <?php echo htmlspecialchars(orderStatusLabel($row['from_status'])); ?>
                                                    →
                                                    <?php echo htmlspecialchars(orderStatusLabel($row['to_status'])); ?>
                                                </div>
                                                <div class="audit-meta">
                                                    <?php echo htmlspecialchars($row['actor_type']); ?>
                                                    <?php if (!empty($row['notes'])): ?>
                                                        • <?php echo htmlspecialchars($row['notes']); ?>
                                                    <?php endif; ?>
                                                    • <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
