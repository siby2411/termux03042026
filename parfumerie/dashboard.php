<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Dashboard";

if (!isLoggedIn()) redirect('/login.php');

// Stats
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_customers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$today_sales = $db->query("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE DATE(order_date)=CURDATE() AND payment_status='paid'")->fetchColumn();
$month_sales = $db->query("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE MONTH(order_date)=MONTH(CURDATE()) AND payment_status='paid'")->fetchColumn();
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetchColumn();

// Top products
$top_products = $db->query("SELECT p.product_name, SUM(oi.quantity) as total_sold FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY p.id ORDER BY total_sold DESC LIMIT 5")->fetchAll();

// Recent orders
$recent_orders = $db->query("SELECT o.*, c.first_name, c.last_name FROM orders o LEFT JOIN customers c ON o.customer_id=c.id ORDER BY o.order_date DESC LIMIT 10")->fetchAll();

include 'templates/header.php';
?>
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
    <div class="stat-card"><div class="flex justify-between"><div><p class="text-gray-500">Produits</p><h3 class="text-3xl font-bold"><?php echo $total_products; ?></h3></div><i class="fas fa-perfume text-4xl text-purple-500"></i></div></div>
    <div class="stat-card"><div class="flex justify-between"><div><p class="text-gray-500">Clients</p><h3 class="text-3xl font-bold"><?php echo $total_customers; ?></h3></div><i class="fas fa-users text-4xl text-blue-500"></i></div></div>
    <div class="stat-card"><div class="flex justify-between"><div><p class="text-gray-500">Ventes Aujourd'hui</p><h3 class="text-3xl font-bold"><?php echo formatPrice($today_sales); ?></h3></div><i class="fas fa-chart-line text-4xl text-green-500"></i></div></div>
    <div class="stat-card"><div class="flex justify-between"><div><p class="text-gray-500">Ventes Mois</p><h3 class="text-3xl font-bold"><?php echo formatPrice($month_sales); ?></h3></div><i class="fas fa-calendar text-4xl text-orange-500"></i></div></div>
    <div class="stat-card"><div class="flex justify-between"><div><p class="text-gray-500">Commandes en attente</p><h3 class="text-3xl font-bold"><?php echo $pending_orders; ?></h3></div><i class="fas fa-clock text-4xl text-red-500"></i></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow">
        <h2 class="text-2xl font-playfair font-bold mb-4">Top Produits</h2>
        <table class="w-full"><thead><tr class="border-b"><th class="text-left py-2">Produit</th><th class="text-right py-2">Vendus</th></tr></thead><tbody>
        <?php foreach($top_products as $p): ?><tr class="border-b"><td class="py-2"><?php echo $p['product_name']; ?></td><td class="text-right font-bold"><?php echo $p['total_sold']; ?></td></tr><?php endforeach; ?>
        </tbody></table>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow">
        <h2 class="text-2xl font-playfair font-bold mb-4">Paiements Rapides</h2>
        <div class="wave-payment mb-4 text-center" onclick="showWavePayment()"><i class="fab fa-wifi text-3xl mb-2"></i><p class="font-bold">Wave</p><p class="text-sm">Scanner QR Code</p></div>
        <div class="orange-payment text-center" onclick="showOrangePayment()"><i class="fas fa-mobile-alt text-3xl mb-2"></i><p class="font-bold">Orange Money</p><p class="text-sm">Paiement mobile</p></div>
    </div>
</div>

<div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="text-2xl font-playfair font-bold mb-4">Dernières Commandes</h2>
    <div class="overflow-x-auto"><table class="w-full"><thead><tr class="border-b"><th class="text-left py-2">N° Commande</th><th class="text-left py-2">Client</th><th class="text-left py-2">Date</th><th class="text-right py-2">Montant</th><th class="text-left py-2">Statut</th><th class="text-left py-2">Actions</th></tr></thead><tbody>
    <?php foreach($recent_orders as $order): ?><tr class="border-b"><td class="py-2 font-mono text-sm"><?php echo $order['order_number']; ?></td><td class="py-2"><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td><td class="py-2 text-sm"><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td><td class="py-2 text-right font-bold"><?php echo formatPrice($order['grand_total']); ?></td><td class="py-2"><span class="px-2 py-1 rounded-full text-xs <?php echo $order['order_status']=='delivered'?'bg-green-100 text-green-700':($order['order_status']=='pending'?'bg-yellow-100 text-yellow-700':'bg-gray-100'); ?>"><?php echo $order['order_status']; ?></span></td><td class="py-2"><button onclick="viewOrder(<?php echo $order['id']; ?>)" class="text-blue-600"><i class="fas fa-eye"></i></button></td></tr><?php endforeach; ?>
    </tbody></table></div>
</div>

<script>
function showWavePayment() { Swal.fire({title:'Paiement Wave',html:'<input id="waveAmount" placeholder="Montant" class="w-full px-3 py-2 border rounded mb-2"><input id="wavePhone" placeholder="Téléphone" class="w-full px-3 py-2 border rounded">',preConfirm:()=>{return fetch('/api/wave/initiate.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`amount=${document.getElementById('waveAmount').value}&phone=${document.getElementById('wavePhone').value}`}).then(r=>r.json());}}).then((r)=>{if(r.value?.qr_code) Swal.fire({title:'Scanner QR Code',imageUrl:r.value.qr_code});});}
function showOrangePayment() { Swal.fire({title:'Orange Money',html:'<input id="orangeAmount" placeholder="Montant" class="w-full px-3 py-2 border rounded mb-2"><input id="orangePhone" placeholder="Téléphone" class="w-full px-3 py-2 border rounded">',preConfirm:()=>{return fetch('/api/orange/initiate.php',{method:'POST',body:`amount=${document.getElementById('orangeAmount').value}&phone=${document.getElementById('orangePhone').value}`}).then(r=>r.json());}});}
function viewOrder(id) { window.location.href=`/order_details.php?id=${id}`; }
</script>
<?php include 'templates/footer.php'; ?>
