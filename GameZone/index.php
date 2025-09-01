<?php 
include 'includes/header.php'; 

// Fetch 4 random products
$result = $conn->query("SELECT * FROM products ORDER BY RAND() LIMIT 4");

// Fallback in case query fails
if (!$result) {
    die("Error fetching products: " . $conn->error);
}

$hero_image = 'images/gamer-hero-blue.png';
?>

<!-- Hero Section -->
<div class="container mx-auto px-4 py-20">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <div class="text-center md:text-left">
            <h1 class="font-heading text-6xl md:text-8xl leading-tight">
                GEAR UP.<br>
                <span class="text-gradient-pink">GAME ON.</span>
            </h1>
            <p class="mt-4 max-w-lg mx-auto md:mx-0 text-lg text-gray-300">
                The ultimate destination for high-performance gaming peripherals in Algeria. 
                Dominate your game with gear that keeps up.
            </p>
            <a href="products.php" class="mt-8 inline-block game-day-button">Explore All Gear</a>
        </div>
        <div>
            <img src="<?= htmlspecialchars($hero_image) ?>" alt="Gamer with headphones" class="w-full max-w-md mx-auto">
        </div>
    </div>
</div>

<!-- Featured Products -->
<div class="container mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h2 class="font-heading text-5xl md:text-6xl text-gradient-pink">Featured Products</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php if ($result->num_rows > 0): ?>
            <?php while($product = $result->fetch_assoc()): ?>
                <div class="game-day-card rounded-lg overflow-hidden">
                    <a href="product-detail.php?id=<?= urlencode($product['id']) ?>">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-full h-56 object-cover">
                    </a>
                    <div class="p-5 flex flex-col justify-between h-52">
                        <div>
                            <h3 class="font-heading text-2xl">
                                <a href="product-detail.php?id=<?= urlencode($product['id']) ?>" 
                                   class="hover:text-pink-400">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h3>
                            <p class="text-pink-400 font-bold mt-2 text-2xl font-['Roboto']">
                                <?= format_dzd($product['price']) ?>
                            </p>
                        </div>
                        <button 
                            onclick="addToCart(<?= (int)$product['id'] ?>, 1)" 
                            class="add-to-cart-btn">
                            Add to Cart
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-400 text-lg text-center col-span-4">No products available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
