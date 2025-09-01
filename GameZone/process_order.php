<?php
require_once 'includes/db.php';
session_start();
require_once 'includes/functions.php';

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_logged_in() || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$shipping_address = trim($_POST['shipping_address'] ?? '');
$payment_method = $_POST['payment_method'] ?? '';

if (empty($shipping_address) || empty($payment_method)) {
    header('Location: checkout.php?error=missing_fields');
    exit();
}

$status = ($payment_method === 'cod') ? 'Pending COD' : 'Paid';

// 1. Calculate total price from DB (prevents tampering)
$total_price = 0;
$product_ids = array_keys($_SESSION['cart']);
if (empty($product_ids)) {
    header('Location: cart.php');
    exit();
}

$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$types = str_repeat('i', count($product_ids));

$stmt = $conn->prepare("SELECT id, price, stock FROM products WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$product_ids);
$stmt->execute();
$result = $stmt->get_result();
$products_from_db = [];
while ($row = $result->fetch_assoc()) {
    $products_from_db[$row['id']] = $row;
}
$stmt->close();

foreach ($_SESSION['cart'] as $product_id => $item) {
    if (isset($products_from_db[$product_id])) {
        $quantity = $item['quantity'];
        $stock = $products_from_db[$product_id]['stock'];
        
        if ($quantity > $stock) {
            // Prevent overselling
            header("Location: cart.php?error=stock&product_id=$product_id");
            exit();
        }
        
        $total_price += $products_from_db[$product_id]['price'] * $quantity;
    }
}

// 2. Start Transaction
$conn->begin_transaction();

try {
    // 3. Insert into 'orders'
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status, payment_method, shipping_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $user_id, $total_price, $status, $payment_method, $shipping_address);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // 4. Insert into 'order_items' & update stock
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($_SESSION['cart'] as $product_id => $item) {
        if (isset($products_from_db[$product_id])) {
            $price = $products_from_db[$product_id]['price'];
            $quantity = $item['quantity'];

            // Insert order item
            $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt_item->execute();

            // Update product stock
            $stmt_stock->bind_param("ii", $quantity, $product_id);
            $stmt_stock->execute();
        }
    }

    $stmt_item->close();
    $stmt_stock->close();

    // 5. Commit transaction
    $conn->commit();

    // 6. Clear cart & redirect
    unset($_SESSION['cart']);
    header('Location: order-success.php?order_id=' . $order_id);
    exit();

} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    error_log("Order failed: " . $exception->getMessage());
    header('Location: checkout.php?error=order_failed');
    exit();
}
