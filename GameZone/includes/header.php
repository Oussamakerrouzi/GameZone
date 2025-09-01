<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameZone DZ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@800&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-body">
    <div id="particles-js"></div>
    <div class="main-container relative z-10">
        <header class="border-b border-white/10 sticky top-0 bg-[#110b2d]/80 backdrop-blur-sm z-50">
            <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
                <a href="index.php" class="font-heading text-3xl text-white hover:text-pink-400 transition-colors">GameZone DZ</a>
                <div class="hidden md:flex items-center space-x-8 text-gray-300">
                    <a href="index.php" class="nav-link-gameday">Home</a>
                    <a href="products.php" class="nav-link-gameday">Products</a>
                    <a href="contact.php" class="nav-link-gameday">Contact</a>
                    <?php if(is_admin()): ?><a href="admin-dashboard.php" class="nav-link-gameday text-yellow-400">Admin</a><?php endif; ?>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="cart.php" class="relative"><svg class="w-6 h-6 text-gray-300 hover:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-pink-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= get_cart_count() ?></span>
                    </a>
                     <?php if (is_logged_in()): ?>
                         <a href="logout.php" class="hidden sm:inline-block game-day-button" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hidden sm:inline-block game-day-button" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>
        <main>