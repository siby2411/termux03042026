<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Commandes";

$orders = $db->query("SELECT o.*, c.first_name, c.last_name FROM orders o LEFT JOIN customers c ON o.customer_id=c.id ORDER BY o.order_date DESC LIMIT 50")->fetchAll();

include 'templates/header.php';
?>
<div class="bg-white rounded-2xl shadow overflow-hidden"><table class="w-full"><thead class="bg-gray-50"><tr><th class="px-4 py-3">N° Commande</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Paiement</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Actions</th></tr></thead><tbody>
<?php foreach($orders as $o): ?><tr class="border-b"><td class="px-4 py-3 font-mono text-sm"><?php echo $o['order_number']; ?></td><td class="px-4 py-3"><?php echo htmlspecialchars($o['first_name'] . ' ' . $o['last_name']); ?></td><td class="px-4 py-3"><?php echo date('d/m/Y', strtotime($o['order_date'])); ?></td><td class="px-4 py-3 text-right font-bold"><?php echo formatPrice($o['grand_total']); ?></td>
<td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs <?php echo $o['payment_status']=='paid'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700'; ?>"><?php echo $o['payment_status']; ?></span></td>
<td class="px-4 py-3"><select onchange="updateStatus(<?php echo $o['id']; ?>, this.value)" class="text-sm border rounded px-2 py-1"><option <?php echo $o['order_status']=='pending'?'selected':''; ?>>pending</option><option <?php echo $o['order_status']=='confirmed'?'selected':''; ?>>confirmed</option><option <?php echo $o['order_status']=='delivered'?'selected':''; ?>>delivered</option><option <?php echo $o['order_status']=='cancelled'?'selected':''; ?>>cancelled</option></select></td>
<td class="px-4 py-3"><button onclick="viewOrder(<?php echo $o['id']; ?>)" class="text-blue-600"><i class="fas fa-eye"></i></button></td></tr><?php endforeach; ?>
</tbody></table></div>
<script>function updateStatus(id,status){fetch('/api/update_order.php',{method:'POST',body:`id=${id}&status=${status}`}).then(()=>Swal.fire('Succès','Statut mis à jour','success'));} function viewOrder(id){window.location.href=`/order_details.php?id=${id}`;}</script>
<?php include 'templates/footer.php'; ?>
