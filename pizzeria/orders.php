<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Gestion des Commandes";

$status_filter = $_GET['status'] ?? '';
$where = $status_filter ? "WHERE order_status = '$status_filter'" : "";
$orders = $db->query("SELECT o.*, c.first_name, c.last_name FROM orders o LEFT JOIN customers c ON o.customer_id=c.id $where ORDER BY o.order_date DESC LIMIT 50")->fetchAll();

include 'templates/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-playfair font-bold">📦 Gestion des Commandes</h1>
    <a href="/pos.php" class="btn-pizza"><i class="fas fa-plus mr-2"></i>Nouvelle commande</a>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-4 border-b flex gap-2 flex-wrap">
        <a href="?status=" class="px-3 py-1 rounded <?php echo !$status_filter ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>">Toutes</a>
        <a href="?status=pending" class="px-3 py-1 rounded <?php echo $status_filter=='pending' ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>">⏳ En attente</a>
        <a href="?status=confirmed" class="px-3 py-1 rounded <?php echo $status_filter=='confirmed' ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>">✅ Confirmées</a>
        <a href="?status=preparing" class="px-3 py-1 rounded <?php echo $status_filter=='preparing' ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>">🍳 En préparation</a>
        <a href="?status=ready" class="px-3 py-1 rounded <?php echo $status_filter=='ready' ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>">🛵 Prêtes</a>
        <a href="?status=delivered" class="px-3 py-1 rounded <?php echo $status_filter=='delivered' ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>">📦 Livrées</a>
        <a href="?status=cancelled" class="px-3 py-1 rounded <?php echo $status_filter=='cancelled' ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>">❌ Annulées</a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr><th class="px-4 py-3 text-left">N° Commande</th><th class="px-4 py-3 text-left">Client</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-left">Paiement</th><th class="px-4 py-3 text-left">Statut</th><th class="px-4 py-3 text-center">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-sm font-bold"><?php echo $o['order_number']; ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?: 'Anonyme'; ?></td>
                    <td class="px-4 py-3"><?php echo $o['order_type']=='sur_place'?'🍽️ Sur place':($o['order_type']=='emporter'?'📦 Emporter':'🛵 Livraison'); ?></td>
                    <td class="px-4 py-3"><?php echo date('d/m/Y H:i', strtotime($o['order_date'])); ?></td>
                    <td class="px-4 py-3 text-right font-bold text-red-600"><?php echo formatPrice($o['grand_total']); ?></td>
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs <?php echo $o['payment_status']=='paid'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700'; ?>"><?php echo $o['payment_method']; ?></span></td>
                    <td class="px-4 py-3">
                        <select onchange="updateStatus(<?php echo $o['id']; ?>, this.value)" class="text-sm border rounded px-2 py-1">
                            <option value="pending" <?php echo $o['order_status']=='pending'?'selected':''; ?>>⏳ En attente</option>
                            <option value="confirmed" <?php echo $o['order_status']=='confirmed'?'selected':''; ?>>✅ Confirmée</option>
                            <option value="preparing" <?php echo $o['order_status']=='preparing'?'selected':''; ?>>🍳 En préparation</option>
                            <option value="ready" <?php echo $o['order_status']=='ready'?'selected':''; ?>>🛵 Prête</option>
                            <option value="delivered" <?php echo $o['order_status']=='delivered'?'selected':''; ?>>📦 Livrée</option>
                            <option value="cancelled" <?php echo $o['order_status']=='cancelled'?'selected':''; ?>>❌ Annulée</option>
                        </select>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button onclick="viewOrder(<?php echo $o['id']; ?>)" class="text-blue-600 hover:text-blue-800 mr-2" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="printOrder(<?php echo $o['id']; ?>)" class="text-gray-600 hover:text-gray-800" title="Imprimer">
                            <i class="fas fa-print"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($orders)): ?>
                <tr><td colspan="8" class="text-center py-8 text-gray-500">Aucune commande trouvée</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateStatus(id, status) {
    fetch('/api/update_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}&status=${status}`
    }).then(response => response.json())
      .then(data => {
          if(data.success) {
              Swal.fire('Succès', 'Statut mis à jour', 'success');
          } else {
              Swal.fire('Erreur', 'Erreur lors de la mise à jour', 'error');
          }
      });
}

function viewOrder(id) {
    window.location.href = `/order_details.php?id=${id}`;
}

function printOrder(id) {
    window.open(`/print_order.php?id=${id}`, '_blank');
}
</script>

<?php include 'templates/footer.php'; ?>
