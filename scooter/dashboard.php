<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Dashboard - Omega Scooter";

$total_sales = $db->query("SELECT COALESCE(SUM(grand_total),0) FROM sales")->fetchColumn();
$total_repairs = $db->query("SELECT COUNT(*) FROM repairs")->fetchColumn();
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_customers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();

include 'templates/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="stat-card"><div><p class="text-gray-500">Chiffre d'affaires</p><h3 class="text-3xl font-bold text-green-600"><?php echo formatPrice($total_sales); ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Réparations</p><h3 class="text-3xl font-bold text-blue-600"><?php echo $total_repairs; ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Produits</p><h3 class="text-3xl font-bold"><?php echo $total_products; ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Clients</p><h3 class="text-3xl font-bold"><?php echo $total_customers; ?></h3></div></div>
</div>

<div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="text-xl font-bold mb-4">📊 Bienvenue sur le dashboard Omega Scooter</h2>
    <p class="text-gray-600">Gérez vos ventes, réparations et stocks depuis cette interface.</p>
</div>

<?php include 'templates/footer.php'; ?>
