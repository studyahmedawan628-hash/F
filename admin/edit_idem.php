<?php
session_start();
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

function handleMenuImageUpload(array $file, string &$error): ?string {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Failed to upload image.';
        return null;
    }

    $maxSize = 3 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        $error = 'Image must be 3MB or smaller.';
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        $error = 'Unsupported image type. Use JPG, PNG, WEBP, or GIF.';
        return null;
    }

    $uploadDir = __DIR__ . '/../images/menu';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = 'menu_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetPath = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $error = 'Unable to save uploaded image.';
        return null;
    }

    return 'images/menu/' . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $itemId = (int)($_POST['item_id'] ?? 0);
        if ($itemId && deleteMenuItem($itemId)) {
            $message = 'Menu item deleted successfully.';
        } else {
            $error = 'Failed to delete menu item.';
        }
    } elseif ($action === 'add' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;

        if ($name === '' || $price === '' || $category === '') {
            $error = 'Name, price, and category are required.';
        } elseif (!is_numeric($price)) {
            $error = 'Price must be a valid number.';
        } else {
            $imagePath = null;
            if (isset($_FILES['image'])) {
                $imagePath = handleMenuImageUpload($_FILES['image'], $error);
            }

            if ($error === '') {
                if ($action === 'add') {
                    $result = addMenuItem([
                        'name' => $name,
                        'description' => $description,
                        'price' => $price,
                        'category' => $category,
                        'image' => $imagePath ?? '',
                        'is_available' => $isAvailable
                    ]);
                    if ($result) {
                        $message = 'Menu item added successfully.';
                    } else {
                        $error = 'Failed to add menu item.';
                    }
                } else {
                    $itemId = (int)($_POST['item_id'] ?? 0);
                    $existingItem = $itemId ? getMenuItem($itemId) : null;
                    if (!$existingItem) {
                        $error = 'Menu item not found.';
                    } else {
                        $imageToSave = $imagePath ?: $existingItem['image'];
                        $result = updateMenuItem($itemId, [
                            'name' => $name,
                            'description' => $description,
                            'price' => $price,
                            'category' => $category,
                            'image' => $imageToSave,
                            'is_available' => $isAvailable
                        ]);
                        if ($result) {
                            $message = 'Menu item updated successfully.';
                        } else {
                            $error = 'Failed to update menu item.';
                        }
                    }
                }
            }
        }
    }
}

$editItem = null;
if (isset($_GET['edit'])) {
    $editItem = getMenuItem((int)$_GET['edit']);
    if (!$editItem) {
        $error = 'Menu item not found.';
    }
}

$menuItems = getMenuItems(null, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - Foodey Admin</title>
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

        .menu-grid {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) 2fr;
            gap: 2rem;
            align-items: start;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 1.5rem;
        }

        .card h2 {
            margin-top: 0;
            color: #2f3542;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        textarea.form-control {
            min-height: 120px;
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #ff6b6b;
            color: white;
        }

        .btn-primary:hover {
            background: #ff4757;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-danger {
            background: #ff4757;
            color: white;
        }

        .btn-danger:hover {
            background: #e8413f;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .menu-table {
            width: 100%;
            border-collapse: collapse;
        }

        .menu-table th,
        .menu-table td {
            padding: 0.8rem;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: middle;
        }

        .menu-table th {
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #f1f2f6;
        }

        .availability {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .availability.available {
            background: #d4edda;
            color: #155724;
        }

        .availability.unavailable {
            background: #f8d7da;
            color: #721c24;
        }

        .table-actions {
            display: flex;
            gap: 0.4rem;
        }

        .current-image {
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.85rem;
            color: #666;
        }

        @media (max-width: 900px) {
            .menu-grid {
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
                <li><a href="edit_idem.php" class="active"><i class="fas fa-utensils"></i> Manage Menu</a></li>
                <li><a href="manage_orders.php"><i class="fas fa-list-alt"></i> Manage Orders</a></li>
                <li><a href="manage_riders.php"><i class="fas fa-motorcycle"></i> Manage Riders</a></li>
                <li><a href="reply.php"><i class="fas fa-envelope"></i> Reply Messages</a></li>
            </ul>
        </aside>

        <main class="content">
            <div class="page-header">
                <h1>Manage Menu</h1>
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

            <div class="menu-grid">
                <div class="card">
                    <h2><?php echo $editItem ? 'Edit Item' : 'Add New Item'; ?></h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'add'; ?>">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="item_id" value="<?php echo (int)$editItem['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Food Name</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                   value="<?php echo htmlspecialchars($editItem['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" step="0.01" id="price" name="price" class="form-control" required
                                   value="<?php echo htmlspecialchars($editItem['price'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" class="form-control" required
                                   value="<?php echo htmlspecialchars($editItem['category'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="image">Image</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                            <?php if (!empty($editItem['image'])): ?>
                                <?php
                                    $previewImage = $editItem['image'];
                                    if (!preg_match('/^https?:\/\//', $previewImage)) {
                                        $previewImage = '../' . ltrim($previewImage, '/');
                                    }
                                ?>
                                <div class="current-image">
                                    <img src="<?php echo htmlspecialchars($previewImage); ?>" alt="Current image" class="item-image">
                                    <span>Current image</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_available" <?php echo ($editItem && !$editItem['is_available']) ? '' : 'checked'; ?>>
                                Available on menu
                            </label>
                        </div>

                        <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $editItem ? 'Update Item' : 'Add Item'; ?>
                            </button>
                            <?php if ($editItem): ?>
                                <a href="edit_idem.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <h2>Menu Items</h2>
                    <?php if (empty($menuItems)): ?>
                        <p>No menu items yet.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="menu-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menuItems as $item): ?>
                                    <?php
                                        $thumbnail = $item['image'] ?? '';
                                        if ($thumbnail !== '' && !preg_match('/^https?:\/\//', $thumbnail)) {
                                            $thumbnail = '../' . ltrim($thumbnail, '/');
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.8rem;">
                                                <?php if ($thumbnail): ?>
                                                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                                                <?php else: ?>
                                                    <div class="item-image"></div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                                    <small><?php echo htmlspecialchars($item['description']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td>$<?php echo number_format((float)$item['price'], 2); ?></td>
                                        <td>
                                            <span class="availability <?php echo $item['is_available'] ? 'available' : 'unavailable'; ?>">
                                                <?php echo $item['is_available'] ? 'Available' : 'Hidden'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="edit_idem.php?edit=<?php echo (int)$item['id']; ?>" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" onsubmit="return confirm('Delete this item permanently?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
