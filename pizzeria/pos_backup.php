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
$cashier_info = $db->prepare("SELECT commission_rate FROM cashiers WHERE id = ?");
$cashier_info->execute([$cashier_id]);
$commission_rate = $cashier_info->fetchColumn() ?: 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point de Vente - Pizzeria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .product-btn { margin: 5px; padding: 15px; border-radius: 10px; transition: all 0.3s; }
        .product-btn:hover { transform: scale(1.05); background: #f39c12; color: white; }
        .cart-item { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .total { font-size: 1.5rem; font-weight: bold; color: #e74c3c; }
        .category-filter { margin-bottom: 20px; }
        .category-btn { margin: 5px; padding: 8px 20px; border-radius: 25px; }
        .category-btn.active { background: #e74c3c; color: white; border-color: #e74c3c; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="fas fa-pizza-slice"></i> Point de Vente - Pizzeria</span>
            <span class="navbar-text text-white">
                <i class="fas fa-user"></i> <?= htmlspecialchars($cashier_name) ?>
                <a href="logout.php" class="btn btn-sm btn-danger ms-3"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </span>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Colonne des produits -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Produits</h5>
                        <div class="category-filter">
                            <button class="btn btn-outline-secondary category-btn active" data-category="all">Tous</button>
                            <?php foreach($categories as $cat): ?>
                            <button class="btn btn-outline-secondary category-btn" data-category="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row" id="products-container">
                            <?php foreach($products as $product): ?>
                            <div class="col-md-4 product-item" data-category="<?= $product['category_id'] ?>">
                                <button class="btn btn-light product-btn w-100" onclick="addToCart(<?= $product['id'] ?>, '<?= addslashes($product['product_name']) ?>', <?= $product['unit_price'] ?>)">
                                    <i class="fas fa-pizza-slice"></i>
                                    <strong><?= htmlspecialchars($product['product_name']) ?></strong><br>
                                    <?= formatPrice($product['unit_price']) ?>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne du panier -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-shopping-cart"></i> Panier</h5>
                    </div>
                    <div class="card-body" style="max-height: 50vh; overflow-y: auto;" id="cart-items">
                        <p class="text-muted">Aucun article dans le panier</p>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-6">
                                <strong>Total:</strong>
                                <span class="total" id="cart-total">0 CFA</span>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-primary w-100" onclick="checkout()">
                                    <i class="fas fa-credit-card"></i> Payer
                                </button>
                            </div>
                        </div>
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
            container.innerHTML = '<p class="text-muted">Aucun article dans le panier</p>';
            totalSpan.innerText = '0 CFA';
            return;
        }

        let total = 0;
        container.innerHTML = '';
        cart.forEach((item, index) => {
            total += item.price * item.quantity;
            container.innerHTML += `
                <div class="cart-item">
                    <div class="row">
                        <div class="col-6">${item.name}</div>
                        <div class="col-2">
                            <input type="number" class="form-control form-control-sm" value="${item.quantity}" min="1" onchange="updateQuantity(${index}, this.value)">
                        </div>
                        <div class="col-3">${(item.price * item.quantity).toLocaleString()} CFA</div>
                        <div class="col-1">
                            <button class="btn btn-sm btn-danger" onclick="removeItem(${index})"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;
        });
        totalSpan.innerText = total.toLocaleString() + ' CFA';
    }

    function updateQuantity(index, quantity) {
        cart[index].quantity = parseInt(quantity);
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        updateCartDisplay();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        updateCartDisplay();
    }

    function checkout() {
        if (cart.length === 0) {
            alert('Panier vide');
            return;
        }
        
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        if (confirm(`Total à payer: ${total.toLocaleString()} CFA\nConfirmer le paiement?`)) {
            fetch('process_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ items: cart, total: total })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Commande validée!');
                    cart = [];
                    updateCartDisplay();
                } else {
                    alert('Erreur: ' + data.message);
                }
            });
        }
    }

    // Filtre par catégorie
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
