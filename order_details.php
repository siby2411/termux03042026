<?php
require_once 'config/config.php';
$db = getDB();
$id = $_GET['id'] ?? 0;

$order = $db->prepare("SELECT o.*, c.first_name, c.last_name, c.phone FROM orders o LEFT JOIN customers c ON o.customer_id=c.id WHERE o.id=?");
$order->execute([$id]);
$order = $order->fetch();

$items = $db->prepare("SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
$items->execute([$id]);
$items = $items->fetchAll();

include 'templates/header.php';
?>
<div class="bg-white rounded-2xl p-6 shadow"><h1 class="text-2xl font-playfair font-bold mb-4">Commande #<?php echo $order['order_number']; ?></h1>
<div class="grid grid-cols-2 gap-4 mb-6"><div><p><strong>Client:</strong> <?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p><p><strong>Téléphone:</strong> <?php echo $order['phone']; ?></p></div><div><p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p><p><strong>Statut:</strong> <?php echo $order['order_status']; ?></p><p><strong>Paiement:</strong> <?php echo $order['payment_status']; ?></p></div></div>
<table class="w-full"><thead><tr class="border-b"><th class="text-left py-2">Produit</th><th class="text-right py-2">Qté</th><th class="text-right py-2">Prix</th><th class="text-right py-2">Total</th></tr></thead><tbody><?php foreach($items as $i): ?><tr class="border-b"><td class="py-2"><?php echo $i['product_name']; ?></td><td class="py-2 text-right"><?php echo $i['quantity']; ?></td><td class="py-2 text-right"><?php echo formatPrice($i['unit_price']); ?></td><td class="py-2 text-right font-bold"><?php echo formatPrice($i['total_price']); ?></td></tr><?php endforeach; ?></tbody><tfoot><tr class="border-t font-bold"><td colspan="3" class="py-2 text-right">Total:</td><td class="py-2 text-right"><?php echo formatPrice($order['grand_total']); ?></td></tr></tfoot></table></div>
<?php include 'templates/footer.php'; ?>
