<?php
include 'includes/header.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_items = $_SESSION['cart'];
$total = 0;
$products = [];

if (!empty($cart_items)) {
    // Sanitize IDs to avoid SQL issues
    $ids = array_map('intval', array_keys($cart_items));
    $ids_list = implode(',', $ids);

    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids_list)");

    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $cart_items[$row['id']];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $products[] = $row;
        $total += $row['subtotal'];
    }
}
?>

<div class="container mx-auto px-4 py-16">
    <h1 class="font-heading text-4xl lg:text-5xl text-gradient-pink mb-8">Your Cart</h1>

    <?php if (empty($products)): ?>
        <p class="text-gray-300 text-lg">
            Your cart is empty. 
            <a href="products.php" class="text-pink-400 hover:underline">Continue shopping</a>.
        </p>
    <?php else: ?>
        <div class="grid gap-6">
            <?php foreach ($products as $product): ?>
                <div class="game-day-card p-6 grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
                    <!-- Product Image -->
                    <div>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-32 h-32 object-cover rounded-md shadow-lg shadow-black/30">
                    </div>

                    <!-- Product Info -->
                    <div>
                        <h2 class="font-heading text-xl text-white mb-2">
                            <?= htmlspecialchars($product['name']) ?>
                        </h2>
                        <p class="text-gray-400"><?= format_dzd($product['price']) ?> each</p>
                    </div>

                    <!-- Quantity Controls -->
                    <div class="flex items-center space-x-3">
                        <button onclick="updateCart(<?= $product['id'] ?>, <?= $product['quantity'] - 1 ?>)" 
                                class="px-3 py-1 rounded-lg bg-pink-500 hover:bg-pink-600 text-white">-</button>
                        <span class="text-lg font-bold"><?= $product['quantity'] ?></span>
                        <button onclick="updateCart(<?= $product['id'] ?>, <?= $product['quantity'] + 1 ?>)" 
                                class="px-3 py-1 rounded-lg bg-pink-500 hover:bg-pink-600 text-white">+</button>
                    </div>

                    <!-- Subtotal & Remove -->
                    <div class="text-right">
                        <p class="text-lg font-bold text-pink-400"><?= format_dzd($product['subtotal']) ?></p>
                        <button onclick="removeFromCart(<?= $product['id'] ?>)" 
                                class="mt-2 text-sm text-red-400 hover:underline">Remove</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Cart Summary -->
        <div class="mt-8 text-right">
            <h2 class="text-2xl font-bold text-white mb-4">
                Total: <span class="text-pink-400"><?= format_dzd($total) ?></span>
            </h2>
            <a href="checkout.php" class="game-day-button px-6 py-3 inline-block">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCart(productId, quantity) {
    if (quantity < 1) return;
    fetch('cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update&id=${productId}&quantity=${quantity}`
    })
    .then(res => res.ok && location.reload());
}

function removeFromCart(productId) {
    fetch('cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=remove&id=${productId}`
    })
    .then(res => res.ok && location.reload());
}
</script>

<?php include 'includes/footer.php'; ?>
