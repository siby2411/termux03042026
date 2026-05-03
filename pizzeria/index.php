<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Accueil - Omega Pizzeria";

// Récupérer les produits en vedette
$featured = $db->query("SELECT * FROM products WHERE is_featured=1 AND is_available=1 LIMIT 6")->fetchAll();
$pizzas = $db->query("SELECT * FROM products WHERE category_id=1 AND is_available=1 LIMIT 4")->fetchAll();

include 'templates/header.php';
?>

<!-- Banner Slider -->
<div class="banner-slider mb-12">
    <div class="slide active">
        <div class="relative h-96 overflow-hidden rounded-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-red-900/70 to-orange-900/70"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center">
                <h2 class="text-5xl md:text-6xl font-playfair font-bold mb-4">Pizza Margherita</h2>
                <p class="text-xl mb-6">La vraie pizza italienne au Sénégal</p>
                <button onclick="window.location.href='/pos.php'" class="btn-pizza">Commander maintenant</button>
            </div>
        </div>
    </div>
    <div class="slide">
        <div class="relative h-96 overflow-hidden rounded-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-orange-900/70 to-yellow-900/70"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center">
                <h2 class="text-5xl md:text-6xl font-playfair font-bold mb-4">Burger Dakar</h2>
                <p class="text-xl mb-6">Le goût unique du Sénégal</p>
                <button onclick="window.location.href='/pos.php'" class="btn-pizza">Découvrir</button>
            </div>
        </div>
    </div>
    <div class="slide">
        <div class="relative h-96 overflow-hidden rounded-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-green-900/70 to-teal-900/70"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center">
                <h2 class="text-5xl md:text-6xl font-playfair font-bold mb-4">Livraison Gratuite</h2>
                <p class="text-xl mb-6">Pour toute commande > 15000 CFA</p>
                <button onclick="window.location.href='/pos.php'" class="btn-pizza">Profiter</button>
            </div>
        </div>
    </div>
    <button class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/75" onclick="prevSlide()">❮</button>
    <button class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/75" onclick="nextSlide()">❯</button>
</div>

<!-- Produits en Vedette -->
<h2 class="text-3xl font-playfair font-bold mb-6 text-center">⭐ Nos spécialités</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
    <?php foreach($featured as $product): ?>
    <div class="product-card group">
        <div class="h-48 bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center relative">
            <i class="fas fa-pizza-slice text-6xl text-red-500 group-hover:scale-110 transition"></i>
            <?php if($product['discount_percentage'] > 0): ?>
                <div class="absolute top-4 right-4 bg-red-500 text-white px-2 py-1 rounded-full text-sm">-<?php echo $product['discount_percentage']; ?>%</div>
            <?php endif; ?>
        </div>
        <div class="p-4">
            <h3 class="font-bold text-lg"><?php echo htmlspecialchars($product['product_name']); ?></h3>
            <p class="text-gray-600 text-sm mt-1"><?php echo substr($product['description'] ?? '', 0, 60); ?>...</p>
            <div class="flex justify-between items-center mt-3">
                <span class="text-2xl font-bold text-red-600"><?php echo formatPrice($product['unit_price']); ?></span>
                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn-pizza py-2 px-4 text-sm">
                    <i class="fas fa-shopping-cart mr-1"></i> Commander
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Bannière Promotion -->
<div class="bg-gradient-to-r from-red-600 to-orange-600 rounded-2xl p-8 mb-12 text-white text-center">
    <h2 class="text-3xl font-playfair font-bold mb-2">🍕 Menu Famille</h2>
    <p class="text-lg mb-4">2 grandes pizzas + 2 boissons + 1 dessert = 15000 CFA</p>
    <button onclick="window.location.href='/pos.php'" class="bg-white text-red-600 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">Commander</button>
</div>

<!-- Pizzas populaires -->
<h2 class="text-3xl font-playfair font-bold mb-6 text-center">🍕 Nos pizzas populaires</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach($pizzas as $pizza): ?>
    <div class="product-card">
        <div class="h-40 bg-gray-100 flex items-center justify-center">
            <i class="fas fa-pizza-slice text-5xl text-red-400"></i>
        </div>
        <div class="p-3">
            <h3 class="font-bold"><?php echo htmlspecialchars($pizza['product_name']); ?></h3>
            <p class="text-red-600 font-bold mt-2"><?php echo formatPrice($pizza['unit_price']); ?></p>
            <button onclick="addToCart(<?php echo $pizza['id']; ?>)" class="mt-2 w-full btn-pizza py-1 text-sm">Ajouter</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Infos pratiques -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
    <div class="bg-white rounded-2xl p-6 text-center shadow">
        <i class="fas fa-truck text-4xl text-red-500 mb-3"></i>
        <h3 class="font-bold text-lg">Livraison rapide</h3>
        <p class="text-gray-500 text-sm">Livraison gratuite à partir de 15000 CFA</p>
    </div>
    <div class="bg-white rounded-2xl p-6 text-center shadow">
        <i class="fas fa-utensils text-4xl text-red-500 mb-3"></i>
        <h3 class="font-bold text-lg">Qualité garantie</h3>
        <p class="text-gray-500 text-sm">Ingrédients frais chaque jour</p>
    </div>
    <div class="bg-white rounded-2xl p-6 text-center shadow">
        <i class="fas fa-clock text-4xl text-red-500 mb-3"></i>
        <h3 class="font-bold text-lg">Ouvert 7/7</h3>
        <p class="text-gray-500 text-sm">11h - 23h, livraison jusqu'à minuit</p>
    </div>
</div>

<script>
let slideIndex = 0;
const slides = document.querySelectorAll('.slide');
function showSlide(n) { slides.forEach(s => s.classList.remove('active')); slideIndex = (n + slides.length) % slides.length; slides[slideIndex].classList.add('active'); }
function nextSlide() { showSlide(slideIndex + 1); }
function prevSlide() { showSlide(slideIndex - 1); }
setInterval(nextSlide, 5000);

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
