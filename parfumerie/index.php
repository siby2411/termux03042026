<?php
require_once 'config/config.php';

$db = getDB();
$products = $db->query("SELECT * FROM products LIMIT 6")->fetchAll();
$featured = $db->query("SELECT * FROM products WHERE is_featured=1 LIMIT 4")->fetchAll();

include 'templates/header.php';
?>
<!-- Hero Section -->
<div class="relative h-96 rounded-2xl overflow-hidden mb-12">
    <div class="absolute inset-0 bg-gradient-to-r from-purple-900/80 to-pink-900/80"></div>
    <div class="absolute inset-0 flex items-center justify-center text-center text-white">
        <div>
            <h1 class="text-5xl md:text-6xl font-playfair font-bold mb-4">Parfumerie & Cosmétique</h1>
            <p class="text-xl mb-8">Découvrez notre collection exclusive de parfums de luxe</p>
            <button onclick="window.location.href='/products.php'" class="btn-luxury text-lg px-8 py-3">
                Acheter Maintenant
            </button>
        </div>
    </div>
</div>

<!-- Produits en Vedette -->
<h2 class="text-3xl font-playfair font-bold mb-6">✨ Produits en Vedette</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
    <?php foreach($featured as $product): ?>
    <div class="product-card group">
        <div class="h-64 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center relative overflow-hidden">
            <i class="fas fa-perfume text-7xl text-gray-400 group-hover:scale-110 transition-transform duration-300"></i>
            <?php if($product['discount_percentage'] > 0): ?>
                <div class="absolute top-4 right-4 bg-red-500 text-white px-2 py-1 rounded-full text-sm font-bold">
                    -<?php echo $product['discount_percentage']; ?>%
                </div>
            <?php endif; ?>
        </div>
        <div class="p-4">
            <h3 class="font-semibold text-lg mb-1"><?php echo htmlspecialchars($product['product_name']); ?></h3>
            <div class="flex justify-between items-center mt-2">
                <span class="text-2xl font-bold text-yellow-600"><?php echo formatPrice($product['unit_price']); ?></span>
                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="bg-gray-800 text-white px-4 py-2 rounded-full hover:bg-yellow-600 transition">
                    <i class="fas fa-shopping-cart mr-2"></i>Ajouter
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Bannière Publicitaire -->
<div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl p-8 mb-12 text-white text-center">
    <h2 class="text-3xl font-playfair font-bold mb-2">Collection Printemps 2026</h2>
    <p class="text-lg mb-4">Jusqu'à -30% sur une sélection de parfums</p>
    <button class="bg-white text-orange-600 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">
        Profiter de l'offre
    </button>
</div>

<!-- Nouveautés -->
<h2 class="text-3xl font-playfair font-bold mb-6">🆕 Nouveautés</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($products as $product): ?>
    <div class="product-card flex">
        <div class="w-32 h-32 bg-gray-100 flex items-center justify-center">
            <i class="fas fa-perfume text-4xl text-gray-400"></i>
        </div>
        <div class="flex-1 p-4">
            <h3 class="font-semibold"><?php echo htmlspecialchars($product['product_name']); ?></h3>
            <p class="text-yellow-600 font-bold mt-2"><?php echo formatPrice($product['unit_price']); ?></p>
            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="mt-2 text-yellow-600 hover:text-yellow-800">
                <i class="fas fa-shopping-cart"></i> Ajouter
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function addToCart(productId) {
    fetch('/api/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Ajouté!', 'Produit ajouté au panier', 'success');
            updateCartCount();
        }
    });
}
</script>

<?php include 'templates/footer.php'; ?>
