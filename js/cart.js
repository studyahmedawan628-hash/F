// Cart Management System
const CART_KEY = 'FoodeyCart';

// Initialize cart if not exists
function initCart() {
    if (!localStorage.getItem(CART_KEY)) {
        localStorage.setItem(CART_KEY, JSON.stringify([]));
    }
}

// Get cart items
function getCart() {
    initCart();
    return JSON.parse(localStorage.getItem(CART_KEY));
}

// Add item to cart
function addToCart(id, name, price, quantity = 1) {
    const cart = getCart();
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id,
            name,
            price: parseFloat(price),
            quantity
        });
    }
    
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
    showNotification(`${name} added to cart!`, 'success');
    
    // If on cart page, refresh display
    if (window.location.pathname.includes('cart.php')) {
        loadCartItems();
    }
}

// Remove item from cart
function removeFromCart(id) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
    showNotification('Item removed from cart', 'warning');
    
    if (window.location.pathname.includes('cart.php')) {
        loadCartItems();
    }
}

// Update item quantity
function updateQuantity(id, quantity) {
    if (quantity < 1) {
        removeFromCart(id);
        return;
    }
    
    const cart = getCart();
    const item = cart.find(item => item.id === id);
    
    if (item) {
        item.quantity = quantity;
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        updateCartCount();
        
        if (window.location.pathname.includes('cart.php')) {
            loadCartItems();
        }
    }
}

// Clear cart
function clearCart() {
    localStorage.setItem(CART_KEY, JSON.stringify([]));
    updateCartCount();
    
    if (window.location.pathname.includes('cart.php')) {
        loadCartItems();
    }
}

// Calculate cart total
function calculateTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Get cart count
function getCartCount() {
    const cart = getCart();
    return cart.reduce((count, item) => count + item.quantity, 0);
}

// Update cart count in header
function updateCartCount() {
    const countElements = document.querySelectorAll('.cart-count');
    const count = getCartCount();
    
    countElements.forEach(element => {
        element.textContent = count;
        if (count > 0) {
            element.style.display = 'flex';
        } else {
            element.style.display = 'none';
        }
    });
}

// Load cart items on cart page
function loadCartItems() {
    if (!document.getElementById('cart-items')) return;
    
    const cart = getCart();
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');
    const emptyCartElement = document.getElementById('empty-cart');
    const cartSummaryElement = document.getElementById('cart-summary');
    
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '';
        emptyCartElement.style.display = 'block';
        cartSummaryElement.style.display = 'none';
        return;
    }
    
    emptyCartElement.style.display = 'none';
    cartSummaryElement.style.display = 'block';
    
    let html = '';
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        html += `
            <div class="cart-item fade-in">
                <div class="item-info">
                    <h4>${item.name}</h4>
                    <p class="item-price">$${item.price.toFixed(2)} each</p>
                </div>
                <div class="item-quantity">
                    <button class="quantity-btn minus" onclick="updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                    <span class="quantity">${item.quantity}</span>
                    <button class="quantity-btn plus" onclick="updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                </div>
                <div class="item-total">
                    $${itemTotal.toFixed(2)}
                </div>
                <button class="remove-item" onclick="removeFromCart('${item.id}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
    
    cartItemsContainer.innerHTML = html;
    
    const subtotal = calculateTotal();
    const tax = subtotal * 0.1; // 10% tax
    const total = subtotal + tax;
    
    cartTotalElement.textContent = total.toFixed(2);
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax').textContent = tax.toFixed(2);
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type} fade-in-down`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#2ed573' : type === 'error' ? '#ff4757' : '#1e90ff'};
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        z-index: 9999;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    initCart();
    updateCartCount();
    
    if (window.location.pathname.includes('cart.php')) {
        loadCartItems();
    }
});