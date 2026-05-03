<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Omega Scooter - Vente de scooters au Sénégal";

$featured = $db->query("SELECT * FROM products WHERE is_featured=1 AND is_available=1 LIMIT 6")->fetchAll();
$scooters = $db->query("SELECT * FROM products WHERE category_id=1 AND is_available=1 LIMIT 4")->fetchAll();

include 'templates/header.php';
?>

<!-- Banner Slider Horizontal avec images de scooters -->
<div class="banner-slider relative mb-12 rounded-2xl overflow-hidden shadow-2xl">
    <div class="slide active">
        <div class="relative h-96 bg-gradient-to-r from-red-800 to-orange-700">
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center z-10">
                <i class="fas fa-motorcycle text-7xl mb-4"></i>
                <h2 class="text-5xl md:text-6xl font-playfair font-bold mb-4">Yamaha TMAX 560</h2>
                <p class="text-xl mb-6">Le maxi-scooter sportif nouvelle génération</p>
                <button onclick="location.href='/products.php?cat=1'" class="btn-scooter text-lg px-8 py-3">Découvrir</button>
            </div>
        </div>
    </div>
    <div class="slide">
        <div class="relative h-96 bg-gradient-to-r from-blue-800 to-purple-700">
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center z-10">
                <i class="fas fa-motorcycle text-7xl mb-4"></i>
                <h2 class="text-5xl md:text-6xl font-playfair font-bold mb-4">Honda PCX 160</h2>
                <p class="text-xl mb-6">Élégance, économie et fiabilité</p>
                <button onclick="location.href='/products.php?cat=1'" class="btn-scooter text-lg px-8 py-3">Découvrir</button>
            </div>
        </div>
    </div>
    <div class="slide">
        <div class="relative h-96 bg-gradient-to-r from-green-800 to-teal-700">
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center z-10">
                <i class="fas fa-motorcycle text-7xl mb-4"></i>
                <h2 class="text-5xl md:text-6xl font-playfair font-bold mb-4">Piaggio Vespa</h2>
                <p class="text-xl mb-6">Le style italien iconique</p>
                <button onclick="location.href='/products.php?cat=1'" class="btn-scooter text-lg px-8 py-3">Découvrir</button>
            </div>
        </div>
    </div>
    <div class="slide">
        <div class="relative h-96 bg-gradient-to-r from-yellow-800 to-orange-700">
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center z-10">
                <i class="fas fa-motorcycle text-7xl mb-4"></i>
                <h2 class="text-5xl md:text-6xl font-playfair font-bold mb-4">Kymco Downtown</h2>
                <p class="text-xl mb-6">Confort et performance</p>
                <button onclick="location.href='/products.php?cat=1'" class="btn-scooter text-lg px-8 py-3">Découvrir</button>
            </div>
        </div>
    </div>
    <button class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-3 rounded-full hover:bg-black/75 transition z-20" onclick="prevSlide()">❮</button>
    <button class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-3 rounded-full hover:bg-black/75 transition z-20" onclick="nextSlide()">❯</button>
    <!-- Indicateurs -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 z-20">
        <span class="dot w-3 h-3 rounded-full bg-white opacity-50 cursor-pointer" onclick="goToSlide(0)"></span>
        <span class="dot w-3 h-3 rounded-full bg-white opacity-50 cursor-pointer" onclick="goToSlide(1)"></span>
        <span class="dot w-3 h-3 rounded-full bg-white opacity-50 cursor-pointer" onclick="goToSlide(2)"></span>
        <span class="dot w-3 h-3 rounded-full bg-white opacity-50 cursor-pointer" onclick="goToSlide(3)"></span>
    </div>
</div>

<!-- Marques -->
<div class="mb-12">
    <h2 class="text-3xl font-playfair font-bold text-center text-white mb-8">Nos marques partenaires</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl p-4 text-center shadow-lg hover:shadow-xl transition"><i class="fab fa-yamaha text-4xl text-red-600"></i><p class="font-semibold mt-2">Yamaha</p></div>
        <div class="bg-white rounded-xl p-4 text-center shadow-lg hover:shadow-xl transition"><i class="fas fa-motorcycle text-4xl text-blue-600"></i><p class="font-semibold mt-2">Honda</p></div>
        <div class="bg-white rounded-xl p-4 text-center shadow-lg hover:shadow-xl transition"><i class="fas fa-crown text-4xl text-purple-600"></i><p class="font-semibold mt-2">Piaggio</p></div>
        <div class="bg-white rounded-xl p-4 text-center shadow-lg hover:shadow-xl transition"><i class="fas fa-star text-4xl text-yellow-600"></i><p class="font-semibold mt-2">Kymco</p></div>
        <div class="bg-white rounded-xl p-4 text-center shadow-lg hover:shadow-xl transition"><i class="fas fa-bolt text-4xl text-green-600"></i><p class="font-semibold mt-2">SYM</p></div>
        <div class="bg-white rounded-xl p-4 text-center shadow-lg hover:shadow-xl transition"><i class="fas fa-shield-alt text-4xl text-gray-600"></i><p class="font-semibold mt-2">Shark</p></div>
    </div>
</div>

<!-- Scooters en vedette -->
<h2 class="text-3xl font-playfair font-bold text-white mb-6">🏍️ Scooters en vedette</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
    <?php foreach($featured as $p): ?>
    <div class="product-card group">
        <div class="h-64 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center relative overflow-hidden">
            <i class="fas fa-motorcycle text-7xl text-gray-400 group-hover:scale-110 transition-transform duration-300"></i>
            <?php if($p['discount_percentage'] > 0): ?>
                <div class="absolute top-4 right-4 bg-red-500 text-white px-2 py-1 rounded-full text-sm font-bold">-<?php echo $p['discount_percentage']; ?>%</div>
            <?php endif; ?>
        </div>
        <div class="p-4">
            <h3 class="font-bold text-xl"><?php echo htmlspecialchars($p['product_name']); ?></h3>
            <p class="text-gray-600"><?php echo $p['brand'] . ' ' . $p['model']; ?></p>
            <div class="flex justify-between items-center mt-3">
                <span class="text-2xl font-bold text-red-600"><?php echo formatPrice($p['unit_price']); ?></span>
                <button onclick="addToCart(<?php echo $p['id']; ?>)" class="btn-scooter py-2 px-4 text-sm">Commander</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Bannière promotion -->
<div class="bg-gradient-to-r from-yellow-500 to-orange-600 rounded-2xl p-8 mb-12 text-white text-center">
    <h2 class="text-3xl font-playfair font-bold mb-2">🎁 Offre spéciale</h2>
    <p class="text-xl mb-4">Casque offert pour l'achat de tout scooter !</p>
    <button onclick="location.href='/products.php'" class="bg-white text-orange-600 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">Profiter de l'offre</button>
</div>

<script>
let slideIndex = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

function showSlide(n) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('bg-white', 'opacity-100'));
    dots.forEach(dot => dot.classList.add('opacity-50'));
    slideIndex = (n + slides.length) % slides.length;
    slides[slideIndex].classList.add('active');
    dots[slideIndex].classList.remove('opacity-50');
    dots[slideIndex].classList.add('bg-white', 'opacity-100');
}
function nextSlide() { showSlide(slideIndex + 1); }
function prevSlide() { showSlide(slideIndex - 1); }
function goToSlide(n) { showSlide(n); }
setInterval(nextSlide, 5000);

function addToCart(productId) {
    fetch('/api/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + productId + '&quantity=1'
    }).then(() => Swal.fire('Ajouté!', 'Produit ajouté au panier', 'success'));
}
</script>

<?php include 'templates/footer.php'; ?>
