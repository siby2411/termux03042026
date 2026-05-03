<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Nos Scooters - Omega Scooter";

$category = $_GET['cat'] ?? 0;
$where = $category ? "WHERE p.category_id = $category AND p.is_available=1" : "WHERE p.is_available=1";
$products = $db->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC")->fetchAll();
$categories = $db->query("SELECT * FROM categories WHERE is_active=1")->fetchAll();

include 'templates/header.php';
?>

<div class="mb-6 flex gap-2 flex-wrap">
    <a href="?cat=0" class="px-4 py-2 rounded-full <?php echo $category==0 ? 'bg-red-600 text-white' : 'bg-gray-200 hover:bg-red-600 hover:text-white'; ?>">Tous</a>
    <?php foreach($categories as $c): ?>
    <a href="?cat=<?php echo $c['id']; ?>" class="px-4 py-2 rounded-full <?php echo $category==$c['id'] ? 'bg-red-600 text-white' : 'bg-gray-200 hover:bg-red-600 hover:text-white'; ?>"><?php echo $c['category_name']; ?></a>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($products as $p): ?>
    <div class="product-card">
        <div class="h-64 bg-gray-100 flex items-center justify-center">
            <i class="fas fa-motorcycle text-6xl text-gray-400"></i>
        </div>
        <div class="p-4">
            <h3 class="font-bold text-xl"><?php echo htmlspecialchars($p['product_name']); ?></h3>
            <p class="text-gray-600"><?php echo $p['brand'] . ' ' . $p['model']; ?></p>
            <p class="text-2xl font-bold text-red-600 mt-2"><?php echo formatPrice($p['unit_price']); ?></p>
            <div class="flex gap-2 mt-3">
                <button onclick="addToCart(<?php echo $p['id']; ?>)" class="btn-scooter flex-1">Ajouter au panier</button>
                <button onclick="buyNow(<?php echo $p['id']; ?>)" class="bg-green-600 text-white px-4 rounded-lg hover:bg-green-700">Acheter</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function addToCart(id) {
    fetch('/api/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + id + '&quantity=1'
    }).then(() => Swal.fire('Ajouté!', 'Produit ajouté au panier', 'success'));
}
function buyNow(id) {
    window.location.href = '/pos.php?product=' + id;
}
</script>

<?php include 'templates/footer.php'; ?>
