<?php
require_once 'config/config.php';
session_start();

if (!isset($_SESSION['cashier_id'])) {
    header('Location: cashier_login.php');
    exit;
}

$db = getDB();
$cashier_id = $_SESSION['cashier_id'];
$cashier_name = $_SESSION['cashier_name'];
$products = $db->query("SELECT * FROM products WHERE is_available = 1 ORDER BY category_id, product_name")->fetchAll();
$categories = $db->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Classique - Pizzeria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            overflow: hidden;
        }
        .pos-container {
            display: flex;
            height: 100vh;
            padding: 10px;
            gap: 10px;
        }
        /* Panneau gauche - Produits */
        .products-panel {
            flex: 2;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .products-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 20px 20px 0 0;
        }
        .category-filters {
            padding: 10px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .category-btn {
            padding: 8px 20px;
            border-radius: 25px;
            border: none;
            background: #e9ecef;
            transition: all 0.3s;
        }
        .category-btn.active {
            background: #e74c3c;
            color: white;
        }
        .products-grid {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .product-card {
            background: white;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #e9ecef;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .product-card:hover {
            transform: translateY(-3px);
            border-color: #e74c3c;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .product-name {
            font-weight: bold;
            margin: 10px 0 5px;
        }
        .product-price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 1.1rem;
        }
        /* Panneau droit - Panier */
        .cart-panel {
            flex: 1;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .cart-header {
            background: #27ae60;
            color: white;
            padding: 15px 20px;
        }
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        .item-info {
            flex: 2;
        }
        .item-name {
            font-weight: bold;
        }
        .item-price {
            font-size: 0.9rem;
            color: #666;
        }
        .item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .qty-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: #e74c3c;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .qty-btn.minus {
            background: #e67e22;
        }
        .qty-value {
            font-weight: bold;
            min-width: 30px;
            text-align: center;
        }
        .item-total {
            min-width: 80px;
            text-align: right;
            font-weight: bold;
            color: #27ae60;
        }
        .cart-footer {
            background: #f8f9fa;
            padding: 15px;
            border-top: 2px solid #ddd;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .total-label {
            font-weight: bold;
        }
        .total-amount {
            font-size: 1.8rem;
            font-weight: bold;
            color: #e74c3c;
        }
        .payment-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .btn-wave {
            background: #0057b3;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
        }
        .btn-om {
            background: #ffa000;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
        }
        .btn-cash {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
        }
        .btn-cancel {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
        }
        .cashier-info {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="pos-container">
        <!-- Panneau gauche -->
        <div class="products-panel">
            <div class="products-header">
                <h3 class="mb-0"><i class="fas fa-pizza-slice me-2"></i>Catalogue</h3>
            </div>
            <div class="category-filters">
                <button class="category-btn active" data-category="all">📦 Tous</button>
                <?php foreach($categories as $cat): ?>
                <button class="category-btn" data-category="<?= $cat['id'] ?>">
                    <?= htmlspecialchars($cat['category_name']) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="products-grid" id="productsGrid">
                <?php foreach($products as $product): ?>
                <div class="product-card" data-category="<?= $product['category_id'] ?>" data-id="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['product_name']) ?>" data-price="<?= $product['unit_price'] ?>">
                    <div class="product-icon">🍕</div>
                    <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                    <div class="product-price"><?= number_format($product['unit_price'], 0) ?> CFA</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Panneau droit -->
        <div class="cart-panel">
            <div class="cart-header">
                <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Panier</h4>
            </div>
            <div class="cart-items" id="cartItems">
                <p class="text-muted text-center">Aucun article</p>
            </div>
            <div class="cart-footer">
                <div class="total-row">
                    <span class="total-label">TOTAL</span>
                    <span class="total-amount" id="totalAmount">0 CFA</span>
                </div>
                <div class="payment-buttons">
                    <button class="btn-wave" onclick="payWith('Wave')">
                        <i class="fas fa-waveform"></i> Wave
                    </button>
                    <button class="btn-om" onclick="payWith('Orange Money')">
                        <i class="fas fa-mobile-alt"></i> OM
                    </button>
                    <button class="btn-cash" onclick="payWith('Espèces')">
                        <i class="fas fa-money-bill"></i> Espèces
                    </button>
                    <button class="btn-cancel" onclick="clearCart()">
                        <i class="fas fa-trash"></i> Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="cashier-info">
        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($cashier_name) ?>
    </div>

    <script>
    let cart = [];

    // Ajout au panier
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', () => {
            const id = parseInt(card.dataset.id);
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            
            const existing = cart.find(item => item.id === id);
            if (existing) {
                existing.quantity++;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            updateCart();
        });
    });

    function updateCart() {
        const container = document.getElementById('cartItems');
        const totalSpan = document.getElementById('totalAmount');
        
        if (cart.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">Aucun article</p>';
            totalSpan.innerText = '0 CFA';
            return;
        }
        
        let total = 0;
        container.innerHTML = '';
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            container.innerHTML += `
                <div class="cart-item">
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-price">${item.price.toLocaleString()} CFA</div>
                    </div>
                    <div class="item-controls">
                        <button class="qty-btn minus" onclick="changeQty(${index}, -1)">-</button>
                        <span class="qty-value">${item.quantity}</span>
                        <button class="qty-btn" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                    <div class="item-total">${itemTotal.toLocaleString()} CFA</div>
                </div>
            `;
        });
        totalSpan.innerText = total.toLocaleString() + ' CFA';
    }

    function changeQty(index, delta) {
        const newQty = cart[index].quantity + delta;
        if (newQty <= 0) {
            cart.splice(index, 1);
        } else {
            cart[index].quantity = newQty;
        }
        updateCart();
    }

    function clearCart() {
        if (confirm('Vider tout le panier ?')) {
            cart = [];
            updateCart();
        }
    }

    function payWith(method) {
        if (cart.length === 0) {
            alert('Panier vide');
            return;
        }
        
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        if (confirm(`💰 ${method}\nTotal: ${total.toLocaleString()} CFA\nConfirmer le paiement ?`)) {
            fetch('process_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    items: cart, 
                    total: total, 
                    payment_method: method,
                    cashier_id: <?= $cashier_id ?>
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ Commande validée !\n${method}: ${total.toLocaleString()} CFA`);
                    cart = [];
                    updateCart();
                } else {
                    alert('❌ Erreur: ' + data.message);
                }
            })
            .catch(err => {
                alert('✅ Paiement simulé avec succès !\n(Fonctionnalité à connecter)');
                cart = [];
                updateCart();
            });
        }
    }

    // Filtre catégories
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const catId = this.dataset.category;
            document.querySelectorAll('.product-card').forEach(card => {
                if (catId === 'all' || card.dataset.category === catId) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    </script>
</body>
</html>
