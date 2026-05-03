<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Dashboard Pizzeria";

if (!isLoggedIn()) redirect('/login.php');

// Statistiques
$today_sales = $db->query("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE DATE(order_date)=CURDATE() AND payment_status='paid'")->fetchColumn();
$week_sales = $db->query("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE WEEK(order_date)=WEEK(CURDATE()) AND payment_status='paid'")->fetchColumn();
$month_sales = $db->query("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE MONTH(order_date)=MONTH(CURDATE()) AND payment_status='paid'")->fetchColumn();

// Alertes stock
$low_stock = $db->query("SELECT COUNT(*) FROM ingredients WHERE current_stock <= min_stock AND is_active=1")->fetchColumn();
$total_ingredients = $db->query("SELECT COUNT(*) FROM ingredients WHERE is_active=1")->fetchColumn();

// Commandes en attente
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetchColumn();

// Personnel présent aujourd'hui
$staff_present = $db->query("SELECT COUNT(*) FROM staff_attendance WHERE attendance_date=CURDATE() AND status='present'")->fetchColumn();

include 'templates/header.php';
?>

<!-- Widget Recherche avancée stock -->
<div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
    <h2 class="text-xl font-bold mb-4">🔍 Recherche avancée des stocks</h2>
    <div class="relative">
        <input type="text" id="stockSearch" placeholder="Rechercher un ingrédient (farine, tomate, fromage...)" 
               class="w-full px-4 py-3 pl-10 border rounded-xl focus:outline-none focus:border-red-500">
        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <div id="stockResults" class="absolute z-50 w-full bg-white rounded-lg shadow-lg mt-1 hidden max-h-96 overflow-y-auto"></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="stat-card"><div><p class="text-gray-500">Ventes aujourd'hui</p><h3 class="text-2xl font-bold text-green-600"><?php echo formatPrice($today_sales); ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Ventes semaine</p><h3 class="text-2xl font-bold text-orange-600"><?php echo formatPrice($week_sales); ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Ventes mois</p><h3 class="text-2xl font-bold text-blue-600"><?php echo formatPrice($month_sales); ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Alertes stock</p><h3 class="text-2xl font-bold text-red-600"><?php echo $low_stock; ?>/<?php echo $total_ingredients; ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Commandes en attente</p><h3 class="text-2xl font-bold text-yellow-600"><?php echo $pending_orders; ?></h3></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow">
        <h2 class="text-xl font-bold mb-4">⚠️ Alertes stock bas</h2>
        <div id="lowStockAlert" class="space-y-2 max-h-64 overflow-y-auto">
            <?php
            $low = $db->query("SELECT ingredient_name, current_stock, min_stock, unit FROM ingredients WHERE current_stock <= min_stock AND is_active=1 LIMIT 10")->fetchAll();
            foreach($low as $l): ?>
            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-200">
                <span class="font-semibold"><?php echo $l['ingredient_name']; ?></span>
                <span class="text-red-600">Stock: <?php echo $l['current_stock']; ?> <?php echo $l['unit']; ?></span>
                <span class="text-sm">Seuil: <?php echo $l['min_stock']; ?> <?php echo $l['unit']; ?></span>
                <a href="/supplies.php" class="text-blue-600 text-sm">📦 Approvisionner</a>
            </div>
            <?php endforeach; ?>
            <?php if(empty($low)): ?><p class="text-gray-500 text-center py-4">✅ Aucune alerte stock</p><?php endif; ?>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow">
        <h2 class="text-xl font-bold mb-4">📦 Produits les plus vendus</h2>
        <?php
        $top = $db->query("SELECT p.product_name, SUM(oi.quantity) as qty FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY p.id ORDER BY qty DESC LIMIT 5")->fetchAll();
        foreach($top as $t): ?>
        <div class="flex justify-between items-center border-b py-2"><span><?php echo $t['product_name']; ?></span><span class="font-bold"><?php echo $t['qty']; ?> vendus</span></div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Recherche en temps réel
const stockSearch = document.getElementById('stockSearch');
const stockResults = document.getElementById('stockResults');

stockSearch.addEventListener('input', function() {
    const query = this.value;
    if(query.length >= 2) {
        fetch(`/api/search_stock.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if(data.success && data.data.length > 0) {
                    stockResults.innerHTML = data.data.map(item => `
                        <div class="p-3 border-b hover:bg-gray-50 flex justify-between items-center">
                            <div>
                                <p class="font-semibold">${item.ingredient_name}</p>
                                <p class="text-sm text-gray-500">${item.category}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold ${item.status === 'danger' ? 'text-red-600' : 'text-green-600'}">${item.current_stock} ${item.unit}</p>
                                <p class="text-xs text-gray-500">${item.status_text}</p>
                            </div>
                        </div>
                    `).join('');
                    stockResults.classList.remove('hidden');
                } else {
                    stockResults.innerHTML = '<div class="p-3 text-center text-gray-500">Aucun ingrédient trouvé</div>';
                    stockResults.classList.remove('hidden');
                }
            });
    } else {
        stockResults.classList.add('hidden');
    }
});

// Cacher les résultats en cliquant ailleurs
document.addEventListener('click', function(e) {
    if(!stockSearch.contains(e.target) && !stockResults.contains(e.target)) {
        stockResults.classList.add('hidden');
    }
});
</script>

<?php include 'templates/footer.php'; ?>
