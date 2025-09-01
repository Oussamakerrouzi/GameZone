<?php
// Format price in Algerian Dinar
function format_dzd($price) {
    return number_format($price, 0, '', ',') . ' DZD';
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get cart item count
function get_cart_count() {
    return isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
}
?>
