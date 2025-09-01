<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'], $_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$action = trim($_POST['action']);
$product_id = (int)$_POST['product_id'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch product stock safely
$stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit();
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit();
}

$stock = (int)$product['stock'];

switch ($action) {
    case 'add':
        $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

        if ($stock <= 0) {
            echo json_encode(['success' => false, 'message' => 'Out of stock.']);
            exit();
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
            $_SESSION['cart'][$product_id]['quantity'] = min($new_quantity, $stock); // cap at stock
        } else {
            $_SESSION['cart'][$product_id] = ['quantity' => min($quantity, $stock)];
        }
        break;

    case 'update':
        $quantity = (int)$_POST['quantity'];
        if ($quantity > 0 && $quantity <= $stock) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } elseif ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        }
        break;

    case 'remove':
        unset($_SESSION['cart'][$product_id]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        exit();
}

echo json_encode([
    'success' => true,
    'cart_count' => get_cart_count(),
    'message' => 'Cart updated successfully.'
]);
