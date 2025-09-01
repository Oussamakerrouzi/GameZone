<?php
include 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit();
}

$product = $result->fetch_assoc();
$gallery_images = [];
if (!empty($product['gallery_images_json'])) {
    $gallery_images = json_decode($product['gallery_images_json'], true);
}
if (!in_array($product['image_url'], $gallery_images)) {
    array_unshift($gallery_images, $product['image_url']);
}
?>
<div class="container mx-auto px-4 py-16">
    <div class="game-day-card rounded-lg p-8 grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
        <!-- Image Gallery -->
        <div>
            <div class="mb-4">
                <img id="main-product-image" src="<?= htmlspecialchars($gallery_images[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-auto object-cover rounded-lg shadow-lg shadow-black/30">
            </div>
            <div class="flex space-x-2 overflow-x-auto p-2">
                <?php foreach($gallery_images as $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="Thumbnail" class="w-24 h-24 object-cover rounded-md cursor-pointer border-2 border-transparent hover:border-pink-500 transition-all duration-300 thumbnail-image">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Product Details -->
        <div>
            <h1 class="font-heading text-4xl lg:text-5xl text-gradient-pink mb-4"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="text-5xl font-bold text-pink-400 mb-6 font-['Roboto']"><?= format_dzd($product['price']) ?></p>
            <div class="border-t border-b border-white/10 py-6 mb-6">
                <h2 class="font-heading text-xl mb-3 text-white">Description</h2>
                <p class="text-gray-300 leading-relaxed"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="font-bold text-lg"><?= $product['stock'] > 0 ? '<span class="text-green-400">In Stock</span>' : '<span class="text-red-400">Out of Stock</span>' ?> (<?= $product['stock'] ?> available)</span>
            </div>
            <div class="mt-8">
                <button onclick="addToCart(<?= $product['id'] ?>, 1)" class="w-full game-day-button" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    <?= $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mainImage = document.getElementById('main-product-image');
    const thumbnails = document.querySelectorAll('.thumbnail-image');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', () => {
            mainImage.src = thumb.src;
            thumbnails.forEach(t => t.classList.remove('border-pink-500'));
            thumb.classList.add('border-pink-500');
        });
    });
});
</script>
<?php include 'includes/footer.php'; ?>