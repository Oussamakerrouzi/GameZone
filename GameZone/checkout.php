<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details for autofill
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($address)) {
        $errors[] = "Address is required.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            // Insert order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, address, phone, total_amount, status) VALUES (?, ?, ?, ?, 'Pending')");
            $total_amount = 0;

            foreach ($_SESSION['cart'] as $product_id => $qty) {
                $pstmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
                $pstmt->bind_param("i", $product_id);
                $pstmt->execute();
                $res = $pstmt->get_result();
                $product = $res->fetch_assoc();

                if ($product['stock'] < $qty) {
                    throw new Exception("Not enough stock for product ID $product_id.");
                }

                $total_amount += $product['price'] * $qty;
            }

            $stmt->bind_param("issd", $user_id, $address, $phone, $total_amount);
            $stmt->execute();
            $order_id = $stmt->insert_id;

            // Insert order items and update stock
            foreach ($_SESSION['cart'] as $product_id => $qty) {
                $pstmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
                $pstmt->bind_param("i", $product_id);
                $pstmt->execute();
                $res = $pstmt->get_result();
                $product = $res->fetch_assoc();

                $price = $product['price'];

                $istmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $istmt->bind_param("iiid", $order_id, $product_id, $qty, $price);
                $istmt->execute();

                // Reduce stock
                $ustmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $ustmt->bind_param("ii", $qty, $product_id);
                $ustmt->execute();
            }

            $conn->commit();
            $_SESSION['cart'] = [];
            $success = "Order placed successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Order failed: " . $e->getMessage();
        }
    }
}
?>

<div class="container mx-auto px-4 py-16">
    <div class="game-day-card rounded-lg p-8">
        <h1 class="text-3xl font-heading mb-6 text-gradient-pink">Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-600/20 border border-red-600 text-red-400 p-4 mb-6 rounded-lg">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-600/20 border border-green-600 text-green-400 p-4 mb-6 rounded-lg">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-300 mb-2">Full Name</label>
                    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled
                           class="w-full px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white">
                </div>

                <div>
                    <label class="block text-gray-300 mb-2">Email</label>
                    <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled
                           class="w-full px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white">
                </div>

                <div>
                    <label for="address" class="block text-gray-300 mb-2">Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
                           class="w-full px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white" required>
                </div>

                <div>
                    <label for="phone" class="block text-gray-300 mb-2">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           class="w-full px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white" required>
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full game-day-button">Place Order</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
