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
    <title>POS Moderne - Pizzeria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --accent: #e74c3c; }
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            font-family: 'Segoe UI', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        .product-btn {
            margin: 5px;
            padding: 15px;
            border-radius: 10px;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .product-btn:hover {
            transform: scale(1.05);
            background: #f39c12;
            color: white;
            border-color: #e74c3c;
        }
        .cart-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .total {
            font-size: 1.8rem;
            font-weight: bold;
            color: #e74c3c;
        }
        .category-btn {
            margin: 5px;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s;
        }
        .category-btn.active {
            background: #e74c3c;
            color: white;
        }
        .payment-methods {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .payment-btn {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        .payment-btn.wave { background: #0057b3; color: white; }
        .payment-btn.om { background: #ffa000; color: white; }
        .payment-btn.cash { background: #2ecc71; color: white; }
        .payment-btn:hover { transform: scale(1.02); opacity: 0.9; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-3">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-pizza-slice"></i> POS Moderne - Pizzeria</span>
            <div>
                <span class="text-white me-3"><i class="fas fa-user"></i> <?= htmlspecialchars($cashier_name) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="glass-card p-3">
                    <div class="category-filters mb-3">
                        <button class="btn btn-outline-secondary category-btn active" data-category="all">📦 Tous</button>
                        <?php foreach($categories as $cat): ?>
                        <button class="btn btn-outline-secondary category-btn" data-category="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="row" id="products-container" style="max-height: 65vh; overflow-y: auto;">
                        <?php foreach($products as $product): ?>
                        <div class="col-md-3 product-item" data-category="<?= $product['category_id'] ?>">
                            <button class="btn btn-light product-btn w-100" onclick="addToCart(<?= $product['id'] ?>, '<?= addslashes($product['product_name']) ?>', <?= $product['unit_price'] ?>)">
                                <i class="fas fa-pizza-slice fa-2x mb-2"></i><br>
                                <strong><?= htmlspecialchars($product['product_name']) ?></strong><br>
                                <span class="text-danger"><?= number_format($product['unit_price'], 0) ?> CFA</span>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-card">
                    <div class="card-header bg-success text-white rounded-top-20">
                        <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Panier</h4>
                    </div>
                    <div class="card-body" style="max-height: 55vh; overflow-y: auto;" id="cart-items">
                        <p class="text-muted text-center">Aucun article</p>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong class="fs-5">TOTAL</strong>
                            <span class="total" id="cart-total">0 CFA</span>
                        </div>
                        <div class="payment-methods">
                            <button class="payment-btn wave" onclick="checkout('Wave')">
                                <i class="fas fa-waveform"></i> Wave
                            </button>
                            <button class="payment-btn om" onclick="checkout('Orange Money')">
                                <i class="fas fa-mobile-alt"></i> OM
                            </button>
                            <button class="payment-btn cash" onclick="checkout('Espèces')">
                                <i class="fas fa-money-bill"></i> Cash
                            </button>
                        </div>
                        <button class="btn btn-danger w-100 mt-2" onclick="clearCart()">
                            <i class="fas fa-trash"></i> Vider le panier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let cart = [];

    function addToCart(id, name, price) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({ id, name, price, quantity: 1 });
        }
        updateCartDisplay();
    }

    function updateCartDisplay() {
        const container = document.getElementById('cart-items');
        const totalSpan = document.getElementById('cart-total');

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
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small>${item.price.toLocaleString()} CFA</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-warning" onclick="changeQty(${index}, -1)">-</button>
                            <span class="fw-bold mx-2">${item.quantity}</span>
                            <button class="btn btn-sm btn-success" onclick="changeQty(${index}, 1)">+</button>
                            <button class="btn btn-sm btn-danger" onclick="removeItem(${index})"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="fw-bold text-success">${itemTotal.toLocaleString()} CFA</div>
                    </div>
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
        updateCartDisplay();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        updateCartDisplay();
    }

    function clearCart() {
        if (cart.length > 0 && confirm('Vider tout le panier ?')) {
            cart = [];
            updateCartDisplay();
        }
    }

    function checkout(method) {
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
                    updateCartDisplay();
                } else {
                    alert('❌ Erreur: ' + data.message);
                }
            })
            .catch(() => {
                alert(`✅ Paiement ${method} simulé !\nMontant: ${total.toLocaleString()} CFA`);
                cart = [];
                updateCartDisplay();
            });
        }
    }

    // Filtre catégories
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const catId = this.dataset.category;
            document.querySelectorAll('.product-item').forEach(item => {
                if (catId === 'all' || item.dataset.category === catId) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    </script>
</body>
</html>
