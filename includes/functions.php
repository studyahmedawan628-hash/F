<?php
require_once 'db.php';
function getMenuItems($category = null, $available_only = false) {
    global $pdo;
    
    $sql = "SELECT * FROM menu_items";
    $conditions = [];
    $params = [];
    
    if ($category) {
        $conditions[] = "category = ?";
        $params[] = $category;
    }
    
    if ($available_only) {
        $conditions[] = "is_available = 1";
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY category, name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT category FROM menu_items WHERE is_available = 1 ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getMenuItem($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function addMenuItem($data) {
    global $pdo;
    
    $sql = "INSERT INTO menu_items (name, description, price, category, image, is_available) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['name'],
        $data['description'],
        $data['price'],
        $data['category'],
        $data['image'] ?? 'default.jpg',
        $data['is_available'] ?? 1
    ]);
}

function updateMenuItem($id, $data) {
    global $pdo;
    
    $sql = "UPDATE menu_items SET 
            name = ?, 
            description = ?, 
            price = ?, 
            category = ?, 
            image = ?, 
            is_available = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['name'],
        $data['description'],
        $data['price'],
        $data['category'],
        $data['image'],
        $data['is_available'],
        $id
    ]);
}

function deleteMenuItem($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    return $stmt->execute([$id]);
}

function placeOrder($customerData, $cartItems) {
    global $pdo;
    
    if (!tableExists('orders') || !tableExists('order_items') || !tableExists('menu_items')) {
        return ['success' => false, 'error' => 'Database not initialized: missing required tables.'];
    }

    try {
        $pdo->beginTransaction();
        
        // Generate order number: ORD + YYYYMMDD + random 4 digits
        $datePart = date('Ymd');
        $randomPart = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $orderNumber = 'ORD' . $datePart . $randomPart;
        
        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $tax = $subtotal * 0.1; // 10% tax
        $total = $subtotal + $tax;
        
        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, customer_address, subtotal, tax, total_amount, notes) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $orderNumber,
            $customerData['name'],
            $customerData['email'],
            $customerData['phone'],
            $customerData['address'],
            $subtotal,
            $tax,
            $total,
            $customerData['notes'] ?? ''
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Insert order items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, item_name, quantity, price, total_price) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $menuItemLookupByName = $pdo->prepare("SELECT id FROM menu_items WHERE name = ? LIMIT 1");
        $menuItemLookupById = $pdo->prepare("SELECT id FROM menu_items WHERE id = ? LIMIT 1");
        
        foreach ($cartItems as $item) {
            $menuItemId = $item['id'] ?? null;
            if (!is_numeric($menuItemId)) {
                $menuItemId = null;
            }
            
            if ($menuItemId) {
                // Validate numeric IDs exist in menu_items to satisfy FK
                $menuItemLookupById->execute([(int)$menuItemId]);
                $resolvedId = $menuItemLookupById->fetchColumn();
                if ($resolvedId) {
                    $menuItemId = (int)$resolvedId;
                } else {
                    $menuItemId = null;
                }
            }
            
            // If ID is missing/invalid, resolve by item name
            if (!$menuItemId && !empty($item['name'])) {
                $menuItemLookupByName->execute([$item['name']]);
                $resolvedId = $menuItemLookupByName->fetchColumn();
                if ($resolvedId) {
                    $menuItemId = (int)$resolvedId;
                }
            }
            
            if (!$menuItemId) {
                throw new Exception("Menu item not found for: " . ($item['name'] ?? 'Unknown'));
            }
            
            $totalPrice = $item['price'] * $item['quantity'];
            $stmt->execute([
                $orderId,
                $menuItemId,
                $item['name'],
                $item['quantity'],
                $item['price'],
                $totalPrice
            ]);
        }
        
        $pdo->commit();
        return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
        
    } catch(Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
// Add these functions to your existing functions.php file

function getOrder($orderId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

function getOrderItems($orderId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT oi.*, mi.image 
        FROM order_items oi 
        LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function getAllOrders($limit = null, $status = null) {
    global $pdo;
    
    if (!tableExists('orders')) {
        return [];
    }

    $sql = "SELECT * FROM orders";
    $params = [];
    
    if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY order_date DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function updateOrderStatus($orderId, $status) {
    global $pdo;
    
    $validStatuses = [
        // Legacy statuses used by existing admin pages
        'pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled',
        // New workflow statuses
        'placed', 'confirmed', 'assigned', 'picked_up'
    ];
    if (!in_array($status, $validStatuses, true)) {
        return false;
    }
    
    $order = getOrder((int)$orderId);
    if (!$order) {
        return false;
    }
    $fromStatus = (string)$order['status'];
    $hasDeliveredAt = orderColumnExists('delivered_at');
    if ($hasDeliveredAt && $status === 'delivered') {
        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = ?, delivered_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
    } else {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    }
    $ok = $stmt->execute([$status, $orderId]);
    if ($ok && $fromStatus !== $status) {
        $actorType = 'system';
        $actorId = null;
        if (isset($_SESSION) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $actorType = 'admin';
        } elseif (isset($_SESSION) && isset($_SESSION['delivery_logged_in']) && $_SESSION['delivery_logged_in'] === true) {
            $actorType = 'delivery_boy';
            $actorId = (int)($_SESSION['delivery_boy_id'] ?? 0);
        }
        logOrderStatusChange((int)$orderId, $fromStatus, (string)$status, $actorType, $actorId, '');
    }
    return $ok;
}

/**
 * Workflow + delivery-boy support helpers.
 * These functions are written to be safe even if the database has not yet been migrated.
 */

function orderStatusLabels(): array {
    return [
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'placed' => 'Placed',
        'confirmed' => 'Confirmed',
        'assigned' => 'Assigned',
        'picked_up' => 'Picked Up',
    ];
}

function normalizeOrderStatus(string $status): string {
    return strtolower(trim($status));
}

function orderStatusBadgeClass(string $status): string {
    $normalized = normalizeOrderStatus($status);
    $map = [
        'pending' => 'status-pending',
        'preparing' => 'status-preparing',
        'out_for_delivery' => 'status-out_for_delivery',
        'placed' => 'status-pending',
        'confirmed' => 'status-preparing',
        'assigned' => 'status-out_for_delivery',
        'picked_up' => 'status-out_for_delivery',
        'delivered' => 'status-delivered',
        'cancelled' => 'status-cancelled',
    ];
    return $map[$normalized] ?? ('status-' . preg_replace('/[^a-z0-9_]/', '', $status));
}

function orderStatusLabel(string $status): string {
    $labels = orderStatusLabels();
    return $labels[strtolower($status)] ?? ucwords(str_replace('_', ' ', $status));
}

function workflowTransitions(): array {
    return [
        // Legacy/admin-friendly workflow
        'pending' => ['preparing', 'assigned', 'out_for_delivery', 'cancelled'],
        'preparing' => ['assigned', 'out_for_delivery', 'cancelled'],
        'assigned' => ['out_for_delivery', 'cancelled'],
        'out_for_delivery' => ['delivered', 'cancelled'],
        // Newer workflow variants kept for compatibility
        'placed' => ['confirmed', 'cancelled'],
        'confirmed' => ['assigned', 'out_for_delivery', 'cancelled'],
        'picked_up' => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
    ];
}

function canTransitionStatus(string $from, string $to): bool {
    $from = normalizeOrderStatus($from);
    $to = normalizeOrderStatus($to);
    $transitions = workflowTransitions();
    if (!isset($transitions[$from])) {
        return false;
    }
    return in_array($to, $transitions[$from], true);
}

function tableExists(string $tableName): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
        $stmt->execute([$tableName]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

function orderColumnExists(string $columnName): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'orders' AND column_name = ? LIMIT 1");
        $stmt->execute([$columnName]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

function getOrderAssignmentColumn(): ?string {
    // Support multiple possible schema variants.
    $candidates = ['assigned_delivery_boy_id', 'assigned_rider_id', 'rider_id'];
    foreach ($candidates as $col) {
        if (orderColumnExists($col)) {
            return $col;
        }
    }
    return null;
}

function logOrderStatusChange(int $orderId, string $fromStatus, string $toStatus, string $actorType, ?int $actorId = null, string $notes = ''): void {
    global $pdo;
    // 1) Legacy audit table if it exists
    if (tableExists('order_status_log')) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO order_status_log (order_id, from_status, to_status, actor_type, actor_id, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$orderId, $fromStatus, $toStatus, $actorType, $actorId, $notes]);
        } catch (Exception $e) {
            // Do not break flows if audit logging fails.
        }
    }
    // 2) Required orders_status_history table if it exists
    if (tableExists('orders_status_history')) {
        try {
            $role = $actorType === 'delivery_boy' ? 'rider' : $actorType;
            $stmt = $pdo->prepare("
                INSERT INTO orders_status_history (order_id, old_status, new_status, changed_by_role, changed_by_id, note)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$orderId, $fromStatus, $toStatus, $role, $actorId, $notes]);
        } catch (Exception $e) {
            // Do not break flows if audit logging fails.
        }
    }
}

function getOrderWithItems(int $orderId): array {
    $order = getOrder($orderId);
    if (!$order) {
        return ['order' => null, 'items' => []];
    }
    $items = getOrderItems($orderId);
    return ['order' => $order, 'items' => $items];
}

function getDeliveryBoys(): array {
    global $pdo;
    try {
        if (tableExists('riders')) {
            $stmt = $pdo->query("SELECT id, username, full_name, phone, status FROM riders WHERE status = 'active' ORDER BY full_name");
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                $r['is_active'] = ($r['status'] ?? 'active') === 'active' ? 1 : 0;
            }
            return $rows;
        }
        if (tableExists('delivery_boys')) {
            $stmt = $pdo->query("SELECT id, username, full_name, phone, is_active FROM delivery_boys WHERE is_active = 1 ORDER BY full_name");
            return $stmt->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        return [];
    }
}

function findDeliveryBoyByUsername(string $username) {
    global $pdo;
    try {
        if (tableExists('riders')) {
            $stmt = $pdo->prepare("SELECT * FROM riders WHERE username = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$username]);
            return $stmt->fetch();
        }
        if (tableExists('delivery_boys')) {
            $stmt = $pdo->prepare("SELECT * FROM delivery_boys WHERE username = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$username]);
            return $stmt->fetch();
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function verifyDeliveryBoyCredentials(string $username, string $password): array {
    $user = findDeliveryBoyByUsername($username);
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
    // Support both hashed and plain-text (for simple/demo setups).
    $hash = $user['password_hash'] ?? ($user['password'] ?? '');
    $valid = false;
    if ($hash && substr($hash, 0, 4) === '$2y$') {
        $valid = password_verify($password, $hash);
    } else {
        $valid = hash_equals((string)$hash, (string)$password);
    }
    if (!$valid) {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
    return ['success' => true, 'user' => $user];
}

function getRiders(bool $includeInactive = false): array {
    global $pdo;
    if (!tableExists('riders')) {
        return [];
    }
    try {
        $sql = "SELECT id, username, full_name, email, phone, status, last_login, created_at FROM riders";
        if (!$includeInactive) {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function riderUsernameExists(string $username): bool {
    global $pdo;
    if (!tableExists('riders')) {
        return false;
    }
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM riders WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

function addRider(array $data): array {
    global $pdo;
    if (!tableExists('riders')) {
        return ['success' => false, 'message' => 'Riders table not found. Please run the database migration.'];
    }
    $username = trim((string)($data['username'] ?? ''));
    $password = (string)($data['password'] ?? '');
    $fullName = trim((string)($data['full_name'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $phone = trim((string)($data['phone'] ?? ''));
    $status = (string)($data['status'] ?? 'active');

    if ($username === '' || $password === '') {
        return ['success' => false, 'message' => 'Username and password are required.'];
    }
    if ($fullName === '') {
        $fullName = $username;
    }
    if (riderUsernameExists($username) || findDeliveryBoyByUsername($username)) {
        return ['success' => false, 'message' => 'That username is already in use.'];
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    try {
        $stmt = $pdo->prepare("
            INSERT INTO riders (username, password, full_name, email, phone, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $ok = $stmt->execute([
            $username,
            $passwordHash,
            $fullName,
            $email !== '' ? $email : null,
            $phone !== '' ? $phone : null,
            $status === 'inactive' ? 'inactive' : 'active'
        ]);
        if ($ok) {
            return ['success' => true, 'message' => 'Rider added successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to add rider.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to add rider: ' . $e->getMessage()];
    }
}

function deactivateRider(int $riderId): array {
    global $pdo;
    if (!tableExists('riders')) {
        return ['success' => false, 'message' => 'Riders table not found.'];
    }
    try {
        $stmt = $pdo->prepare("UPDATE riders SET status = 'inactive' WHERE id = ?");
        $ok = $stmt->execute([$riderId]);
        if ($ok && $stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Rider removed successfully.'];
        }
        return ['success' => false, 'message' => 'Rider not found.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to remove rider: ' . $e->getMessage()];
    }
}

function assignOrderToDeliveryBoy(int $orderId, int $deliveryBoyId, string $adminName = 'admin'): array {
    global $pdo;
    $assignmentColumn = getOrderAssignmentColumn();
    if ($assignmentColumn === null) {
        return ['success' => false, 'message' => 'Database not migrated: no rider assignment column found on orders.'];
    }
    $order = getOrder($orderId);
    if (!$order) {
        return ['success' => false, 'message' => 'Order not found.'];
    }
    $currentStatus = normalizeOrderStatus($order['status']);
    if (in_array($currentStatus, ['delivered', 'cancelled'], true)) {
        return ['success' => false, 'message' => 'Cannot assign delivered or cancelled orders.'];
    }
    $fromStatus = $order['status'];
    $toStatus = $currentStatus === 'placed' ? 'assigned' : ($currentStatus === 'confirmed' ? 'assigned' : 'assigned');
    try {
        $stmt = $pdo->prepare("
            UPDATE orders
            SET {$assignmentColumn} = ?, assigned_at = CURRENT_TIMESTAMP, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $ok = $stmt->execute([$deliveryBoyId, $toStatus, $orderId]);
        if ($ok) {
            logOrderStatusChange($orderId, $fromStatus, $toStatus, 'admin', null, "Assigned by {$adminName} to rider #{$deliveryBoyId}");
            return ['success' => true, 'message' => 'Order assigned successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to assign order.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Assignment failed: ' . $e->getMessage()];
    }
}

function getAssignedOrdersForDeliveryBoy(int $deliveryBoyId, ?string $statusFilter = null, bool $historyOnly = false): array {
    global $pdo;
    $assignmentColumn = getOrderAssignmentColumn();
    if ($assignmentColumn === null) {
        return [];
    }
    try {
        $sql = "SELECT * FROM orders WHERE {$assignmentColumn} = ?";
        $params = [$deliveryBoyId];
        if ($historyOnly) {
            $sql .= " AND status IN ('delivered','cancelled')";
        } else {
            $sql .= " AND status NOT IN ('delivered','cancelled')";
        }
        if ($statusFilter) {
            $filter = strtolower($statusFilter);
            if ($filter === 'pending') {
                // Delivery-boy "Pending" = assigned but not yet picked up.
                $sql .= " AND status IN ('assigned','pending','preparing','confirmed')";
            } elseif ($filter === 'out_for_delivery') {
                // Rider's active queue should include ready/assigned orders too.
                $sql .= " AND status IN ('assigned','pending','preparing','confirmed','out_for_delivery')";
            } else {
                $sql .= " AND status = ?";
                $params[] = $filter;
            }
        }
        $sql .= " ORDER BY order_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function updateOrderStatusAsDeliveryBoy(int $orderId, int $deliveryBoyId, string $newStatus, string $notes = ''): array {
    global $pdo;
    $assignmentColumn = getOrderAssignmentColumn();
    if ($assignmentColumn === null) {
        return ['success' => false, 'message' => 'Database not migrated: assignment column is missing.'];
    }
    $newStatus = strtolower(trim($newStatus));
    $allowed = ['out_for_delivery', 'delivered', 'cancelled'];
    if (!in_array($newStatus, $allowed, true)) {
        return ['success' => false, 'message' => 'Invalid delivery status.'];
    }
    $order = getOrder($orderId);
    if (!$order) {
        return ['success' => false, 'message' => 'Order not found.'];
    }
    if ((int)($order[$assignmentColumn] ?? 0) !== $deliveryBoyId) {
        return ['success' => false, 'message' => 'You can only update orders assigned to you.'];
    }
    $current = normalizeOrderStatus($order['status']);
    if ($current === 'cancelled') {
        return ['success' => false, 'message' => 'Cannot update a cancelled order.'];
    }
    if ($current === 'delivered') {
        return ['success' => false, 'message' => 'Order is already delivered.'];
    }
    if ($newStatus === 'delivered' && $current === 'cancelled') {
        return ['success' => false, 'message' => 'Cannot deliver a cancelled order.'];
    }
    if ($newStatus === 'cancelled' && $current === 'delivered') {
        return ['success' => false, 'message' => 'Cannot cancel a delivered order.'];
    }
    if (!canTransitionStatus($current, $newStatus)) {
        return ['success' => false, 'message' => 'Invalid status transition.'];
    }
    $fromStatus = $order['status'];
    try {
        $hasDeliveredAt = orderColumnExists('delivered_at');
        if ($hasDeliveredAt && $newStatus === 'delivered') {
            $stmt = $pdo->prepare("
                UPDATE orders
                SET status = ?, delivered_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND {$assignmentColumn} = ?
            ");
        } else {
            $stmt = $pdo->prepare("
                UPDATE orders
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND {$assignmentColumn} = ?
            ");
        }
        $ok = $stmt->execute([$newStatus, $orderId, $deliveryBoyId]);
        if ($ok) {
            logOrderStatusChange($orderId, $fromStatus, $newStatus, 'delivery_boy', $deliveryBoyId, $notes);
            return ['success' => true, 'message' => 'Order status updated.'];
        }
        return ['success' => false, 'message' => 'Failed to update order status.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Status update failed: ' . $e->getMessage()];
    }
}

function searchOrders($searchTerm) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE order_number LIKE ? 
        OR customer_name LIKE ? 
        OR customer_email LIKE ? 
        OR customer_phone LIKE ?
        ORDER BY order_date DESC
    ");
    $searchTerm = "%$searchTerm%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

function getDashboardStats() {
    global $pdo;
    
    $stats = [
        'total_orders' => 0,
        'pending_orders' => 0,
        'today_orders' => 0,
        'total_revenue' => 0,
        'monthly_revenue' => 0,
    ];

    if (!tableExists('orders')) {
        return $stats;
    }

    try {
        // Total orders
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $stats['total_orders'] = (int)($stmt->fetch()['total'] ?? 0);
        
        // Pending orders
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = (int)($stmt->fetch()['total'] ?? 0);
        
        // Today's orders
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE DATE(order_date) = CURDATE()");
        $stats['today_orders'] = (int)($stmt->fetch()['total'] ?? 0);
        
        // Total revenue
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
        $stats['total_revenue'] = (float)($stmt->fetch()['total'] ?? 0);
        
        // Monthly revenue
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE()) AND status != 'cancelled'");
        $stats['monthly_revenue'] = (float)($stmt->fetch()['total'] ?? 0);
    } catch (Exception $e) {
        return $stats;
    }
    
    return $stats;
}
?>
