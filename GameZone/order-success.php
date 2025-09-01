<?php
include 'includes/header.php';

// Retrieve order ID (from GET parameter or session)
$order_id = isset($_GET['order_id']) 
    ? htmlspecialchars($_GET['order_id'], ENT_QUOTES, 'UTF-8') 
    : ($_SESSION['order_id'] ?? null);
?>
<canvas id="confetti-canvas"></canvas>

<div class="container mx-auto px-4 py-20 text-center">
    <div class="bg-slate-800 p-10 rounded-lg shadow-lg max-w-2xl mx-auto">
        <!-- Success Icon -->
        <svg class="w-24 h-24 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

        <!-- Success Title -->
        <h1 class="text-4xl font-['Orbitron'] font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-cyan-400">
            Order Placed Successfully!
        </h1>

        <!-- Success Message -->
        <p class="mt-4 text-lg text-gray-300">
            Thank you for your purchase. We've received your order
            <?php if ($order_id): ?> 
                <br><span class="text-green-400 font-semibold">Order Reference: #<?= htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?> 
            and will process it shortly.
        </p>

        <!-- Call-to-Action -->
        <a href="products.php" class="mt-8 inline-block neon-button">
            Continue Shopping
        </a>
    </div>
</div>

<!-- Confetti Script -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const myCanvas = document.getElementById('confetti-canvas');
    const myConfetti = confetti.create(myCanvas, { resize: true, useWorker: true });

    const duration = 5000;
    const end = Date.now() + duration;

    (function frame() {
        myConfetti({ particleCount: 2, angle: 60, spread: 55, origin: { x: 0 } });
        myConfetti({ particleCount: 2, angle: 120, spread: 55, origin: { x: 1 } });

        if (Date.now() < end) requestAnimationFrame(frame);
    })();
});
</script>

<?php include 'includes/footer.php'; ?>
