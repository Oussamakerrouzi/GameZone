<?php
include 'includes/header.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Admin guard
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

$message = '';

// Ensure DB connection exists
if (!isset($conn) || !$conn) {
    $message .= '<div class="message-error">Database connection not found. Check includes/db.php</div>';
}

// Handle Product Actions (Add/Edit/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete product
    if (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                $message = '<div class="message-success">Product deleted successfully.</div>';
            } else {
                $message = '<div class="message-error">Delete failed: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="message-error">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
        }
    }

    // Add or update product
    if (isset($_POST['save_product'])) {
        $id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
        $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
        $image_url = trim($_POST['current_image'] ?? 'images/default.jpg');

        if ($name === '') {
            $message = '<div class="message-error">Name is required.</div>';
        }

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = __DIR__ . "/images/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $safe_filename = preg_replace("/[^a-zA-Z0-9-_\.]/", "", basename($_FILES["image"]["name"]));
            $target_file = "images/" . time() . "_" . $safe_filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], __DIR__ . "/" . $target_file)) {
                $image_url = $target_file;
            } else {
                $message = '<div class="message-error">Error: Could not upload the image.</div>';
            }
        }

        if ($message === '') {
            if ($id === 0) {
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssdis", $name, $description, $price, $stock, $image_url);
                    if ($stmt->execute()) {
                        $message = '<div class="message-success">Product added successfully.</div>';
                    } else {
                        $message = '<div class="message-error">Insert failed: ' . htmlspecialchars($stmt->error) . '</div>';
                    }
                    $stmt->close();
                } else {
                    $message = '<div class="message-error">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
                }
            } else {
                $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image_url=? WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image_url, $id);
                    if ($stmt->execute()) {
                        $message = '<div class="message-success">Product updated successfully.</div>';
                    } else {
                        $message = '<div class="message-error">Update failed: ' . htmlspecialchars($stmt->error) . '</div>';
                    }
                    $stmt->close();
                } else {
                    $message = '<div class="message-error">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
                }
            }
        }
    }
}

// Fetch products and orders
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
if ($products === false) $message .= '<div class="message-error">DB Error (products): ' . htmlspecialchars($conn->error) . '</div>';

$orders = $conn->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
if ($orders === false) $message .= '<div class="message-error">DB Error (orders): ' . htmlspecialchars($conn->error) . '</div>';

// Load product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) $edit_product = $res->fetch_assoc();
        $stmt->close();
    } else {
        $message .= '<div class="message-error">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
    }
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="font-heading text-6xl md:text-7xl text-gradient-pink">Admin Dashboard</h1>
    </div>

    <?= $message ?>

    <!-- Add/Edit Product Form -->
    <div class="game-day-card rounded-lg p-6 mb-12">
        <h2 class="font-heading text-3xl mb-4"><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h2>
        <form action="admin-dashboard.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($edit_product['id'] ?? '') ?>">
            <input type="hidden" name="current_image" value="<?= htmlspecialchars($edit_product['image_url'] ?? 'images/default.jpg') ?>">
            
            <div class="md:col-span-2">
                <label class="font-bold">Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>" required class="form-input-gameday mt-1">
            </div>
            <div>
                <label class="font-bold">Price (DZD)</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($edit_product['price'] ?? '') ?>" required class="form-input-gameday mt-1">
            </div>
            <div>
                <label class="font-bold">Stock</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($edit_product['stock'] ?? '') ?>" required class="form-input-gameday mt-1">
            </div>
            <div class="md:col-span-2">
                <label class="font-bold">Description</label>
                <textarea name="description" required class="form-input-gameday mt-1 h-24"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="font-bold">Image</label>
                <input type="file" name="image" class="form-input-gameday file-input w-full">
            </div>
            <div class="md:col-span-2 text-right">
                <button type="submit" name="save_product" class="game-day-button">Save Product</button>
            </div>
        </form>
    </div>

    <!-- Products List -->
    <div class="game-day-card rounded-lg p-6 mb-12">
        <h2 class="font-heading text-3xl mb-4">Products List</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="p-3">ID</th>
                        <th class="p-3">Image</th>
                        <th class="p-3">Name</th>
                        <th class="p-3">Price</th>
                        <th class="p-3">Stock</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                            <td class="p-3"><?= $product['id'] ?></td>
                            <td class="p-3">
                                <img src="<?= htmlspecialchars($product['image_url'] ?: 'images/default.jpg') ?>" class="w-12 h-12 object-cover rounded" onerror="this.src='images/default.jpg'">
                            </td>
                            <td class="p-3"><?= htmlspecialchars($product['name']) ?></td>
                            <td class="p-3"><?= function_exists('format_dzd') ? format_dzd($product['price']) : htmlspecialchars($product['price']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($product['stock']) ?></td>
                            <td class="p-3">
                                <a href="admin-dashboard.php?edit=<?= $product['id'] ?>" class="text-yellow-400 hover:underline mr-4">Edit</a>
                                <form action="admin-dashboard.php" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <button type="submit" name="delete_product" class="text-red-500 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="p-3 text-center text-gray-400">No products found or a DB error occurred.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="game-day-card rounded-lg p-6">
        <h2 class="font-heading text-3xl mb-4">Recent Orders</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="p-3">Order ID</th>
                        <th class="p-3">User</th>
                        <th class="p-3">Total</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Date</th>
                    </tr>
                </thead>
                <tbody>
               
