function addToCart(productId, quantity) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('cart_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartIcon(data.cart_count);
        } else {
            alert('Failed to add to cart.');
        }
    });
}

function updateCart(productId, quantity) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('cart_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to see changes in totals
            location.reload();
        } else {
            alert('Failed to update cart.');
        }
    });
}

function removeFromCart(productId) {
     const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('product_id', productId);

    fetch('cart_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
             // Reload the page to remove the item from view
            location.reload();
        } else {
            alert('Failed to remove item from cart.');
        }
    });
}


function updateCartIcon(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.classList.remove('cart-bounce');
        // Trigger reflow to restart animation
        void cartCountElement.offsetWidth; 
        cartCountElement.classList.add('cart-bounce');
    }
}