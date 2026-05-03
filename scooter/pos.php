<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Point de Vente - Omega Scooter";

$products = $db->query("SELECT * FROM products WHERE is_available=1 AND stock_quantity>0 ORDER BY product_name")->fetchAll();
$customers = $db->query("SELECT * FROM customers ORDER BY first_name")->fetchAll();

include 'templates/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Liste des produits -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-lg p-4">
            <div class="mb-4">
                <input type="text" id="searchProduct" placeholder="🔍 Rechercher un produit (scooter, casque, pièce)..." class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:border-red-500">
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-96 overflow-y-auto" id="productsList">
                <?php foreach($products as $p): ?>
                <div class="product-card cursor-pointer p-3 text-center hover:shadow-lg transition" onclick='addToCart({id:<?php echo $p['id']; ?>, name:"<?php echo addslashes($p['product_name']); ?>", price:<?php echo $p['unit_price']; ?>, stock:<?php echo $p['stock_quantity']; ?>})'>
                    <div class="h-20 flex items-center justify-center">
                        <i class="fas fa-motorcycle text-4xl text-red-500"></i>
                    </div>
                    <p class="font-semibold text-sm mt-2"><?php echo htmlspecialchars($p['product_name']); ?></p>
                    <p class="text-red-600 font-bold"><?php echo formatPrice($p['unit_price']); ?></p>
                    <p class="text-xs text-gray-500">Stock: <?php echo $p['stock_quantity']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Panier -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-lg p-4 sticky top-24">
            <h2 class="text-xl font-bold mb-4"><i class="fas fa-shopping-cart text-red-500 mr-2"></i>Panier</h2>
            
            <select id="customerId" class="w-full px-3 py-2 border rounded-lg mb-4">
                <option value="">Client (optionnel)</option>
                <?php foreach($customers as $c): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo $c['first_name'] . ' ' . $c['last_name'] . ' - ' . $c['phone']; ?></option>
                <?php endforeach; ?>
            </select>
            
            <div id="cartItems" class="max-h-96 overflow-y-auto mb-4 bg-gray-50 rounded-lg p-2">
                <p class="text-gray-500 text-center py-8"><i class="fas fa-shopping-cart text-4xl mb-2 block"></i>Aucun produit</p>
            </div>
            
            <div class="border-t pt-4">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total:</span>
                    <span id="total" class="text-red-600">0 CFA</span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button onclick="processPayment('cash')" class="bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-money-bill-wave mr-1"></i>Espèces
                </button>
                <button onclick="processPayment('wave')" class="bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fab fa-wifi mr-1"></i>Wave
                </button>
                <button onclick="processPayment('orange_money')" class="bg-orange-600 text-white py-2 rounded-lg col-span-2 hover:bg-orange-700 transition">
                    <i class="fas fa-mobile-alt mr-1"></i>Orange Money
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(product) {
    const existing = cart.find(item => item.id === product.id);
    if(existing) {
        if(existing.quantity + 1 > product.stock) {
            Swal.fire('Stock insuffisant', `Stock disponible: ${product.stock}`, 'warning');
            return;
        }
        existing.quantity++;
    } else {
        if(1 > product.stock) {
            Swal.fire('Stock insuffisant', 'Produit en rupture', 'warning');
            return;
        }
        cart.push({...product, quantity: 1});
    }
    updateCart();
}

function updateCart() {
    const container = document.getElementById('cartItems');
    if(cart.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8"><i class="fas fa-shopping-cart text-4xl mb-2 block"></i>Aucun produit</p>';
    } else {
        container.innerHTML = cart.map((item, idx) => `
            <div class="flex justify-between items-center border-b py-2">
                <div class="flex-1">
                    <p class="font-semibold text-sm">${item.name}</p>
                    <p class="text-xs text-gray-500">${formatPrice(item.price)}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="updateQty(${idx}, -1)" class="w-6 h-6 bg-gray-200 rounded-full hover:bg-gray-300">-</button>
                    <span class="w-8 text-center">${item.quantity}</span>
                    <button onclick="updateQty(${idx}, 1)" class="w-6 h-6 bg-gray-200 rounded-full hover:bg-gray-300">+</button>
                    <button onclick="removeItem(${idx})" class="text-red-500 ml-2"><i class="fas fa-trash"></i></button>
                </div>
                <div class="w-24 text-right font-bold">${formatPrice(item.price * item.quantity)}</div>
            </div>
        `).join('');
    }
    calculateTotal();
}

function updateQty(idx, delta) {
    const newQty = cart[idx].quantity + delta;
    if(newQty < 1) {
        cart.splice(idx, 1);
    } else if(newQty > cart[idx].stock) {
        Swal.fire('Stock insuffisant', `Stock max: ${cart[idx].stock}`, 'warning');
        return;
    } else {
        cart[idx].quantity = newQty;
    }
    updateCart();
}

function removeItem(idx) {
    cart.splice(idx, 1);
    updateCart();
}

function calculateTotal() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('total').innerText = formatPrice(total);
}

function processPayment(method) {
    if(cart.length === 0) {
        Swal.fire('Erreur', 'Le panier est vide', 'error');
        return;
    }
    
    const customerId = document.getElementById('customerId').value;
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    Swal.fire({
        title: 'Confirmation',
        html: `<p>Confirmer la vente de <strong>${formatPrice(total)}</strong> ?</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '✅ Confirmer',
        cancelButtonText: '❌ Annuler'
    }).then((result) => {
        if(result.isConfirmed) {
            fetch('/api/create_sale.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    customer_id: customerId || null,
                    items: cart.map(item => ({id: item.id, name: item.name, price: item.price, quantity: item.quantity})),
                    total: total,
                    payment_method: method
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Succès!', `Vente #${data.sale_number} enregistrée`, 'success');
                    cart = [];
                    updateCart();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Erreur', data.error, 'error');
                }
            });
        }
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR').format(price) + ' CFA';
}

// Recherche de produits
document.getElementById('searchProduct').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    const products = document.querySelectorAll('#productsList .product-card');
    products.forEach(product => {
        const name = product.querySelector('p.font-semibold')?.innerText.toLowerCase() || '';
        product.style.display = name.includes(search) ? 'block' : 'none';
    });
});
</script>

<?php include 'templates/footer.php'; ?>
