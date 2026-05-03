<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Gestion des Clients";

// Pagination
$page = $_GET['page'] ?? 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$where = $search ? "WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR phone LIKE '%$search%'" : "";
$customers = $db->query("SELECT c.*, COUNT(o.id) as total_orders, COALESCE(SUM(o.grand_total),0) as total_spent FROM customers c LEFT JOIN orders o ON c.id=o.customer_id $where GROUP BY c.id ORDER BY c.registration_date DESC LIMIT $limit OFFSET $offset")->fetchAll();
$total = $db->query("SELECT COUNT(*) FROM customers $where")->fetchColumn();

include 'templates/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-playfair font-bold">👥 Gestion des Clients</h1>
    <button onclick="openCustomerModal()" class="btn-pizza"><i class="fas fa-user-plus mr-2"></i>Nouveau client</button>
</div>

<div class="bg-white rounded-2xl p-4 mb-6">
    <form method="GET" class="flex gap-4">
        <input type="text" name="search" placeholder="Rechercher par nom ou téléphone..." value="<?php echo htmlspecialchars($search); ?>" class="flex-1 px-4 py-2 border rounded-lg">
        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg">Rechercher</button>
    </form>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr><th class="px-4 py-3 text-left">Client</th><th class="px-4 py-3 text-left">Contact</th><th class="px-4 py-3 text-center">Points</th><th class="px-4 py-3 text-right">Commandes</th><th class="px-4 py-3 text-right">Total dépensé</th><th class="px-4 py-3 text-center">Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach($customers as $c): ?>
            <tr class="border-b">
                <td class="px-4 py-3"><div class="font-semibold"><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></div><div class="text-xs text-gray-500">Code: <?php echo $c['customer_code']; ?></div></td>
                <td class="px-4 py-3"><div><i class="fas fa-phone-alt text-gray-400 mr-1"></i><?php echo $c['phone']; ?></div><?php if($c['email']): ?><div class="text-xs"><?php echo $c['email']; ?></div><?php endif; ?></td>
                <td class="px-4 py-3 text-center"><span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-sm"><?php echo $c['loyalty_points']; ?> pts</span></td>
                <td class="px-4 py-3 text-right"><?php echo $c['total_orders']; ?></td>
                <td class="px-4 py-3 text-right font-bold"><?php echo formatPrice($c['total_spent']); ?></td>
                <td class="px-4 py-3 text-center"><button onclick="editCustomer(<?php echo $c['id']; ?>)" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></button><button onclick="deleteCustomer(<?php echo $c['id']; ?>)" class="text-red-600"><i class="fas fa-trash"></i></button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Client -->
<div id="customerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-full max-w-md p-6">
        <div class="flex justify-between mb-4"><h2 id="modalTitle" class="text-2xl font-playfair font-bold">Client</h2><i class="fas fa-times cursor-pointer text-2xl" onclick="closeModal()"></i></div>
        <form id="customerForm" method="POST" action="/api/customer_crud.php">
            <input type="hidden" name="action" id="formAction" value="add"><input type="hidden" name="id" id="customerId">
            <div class="space-y-3">
                <div><label>Prénom *</label><input type="text" name="first_name" id="first_name" required class="w-full px-3 py-2 border rounded"></div>
                <div><label>Nom *</label><input type="text" name="last_name" id="last_name" required class="w-full px-3 py-2 border rounded"></div>
                <div><label>Téléphone *</label><input type="tel" name="phone" id="phone" required class="w-full px-3 py-2 border rounded"></div>
                <div><label>Email</label><input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded"></div>
                <div><label>Adresse</label><textarea name="address" id="address" rows="2" class="w-full px-3 py-2 border rounded"></textarea></div>
            </div>
            <div class="flex justify-end gap-3 mt-6"><button type="button" onclick="closeModal()" class="px-4 py-2 border rounded">Annuler</button><button type="submit" class="btn-pizza px-6 py-2">Enregistrer</button></div>
        </form>
    </div>
</div>

<script>
function openCustomerModal() { document.getElementById('modalTitle').innerText='Ajouter client'; document.getElementById('formAction').value='add'; document.getElementById('customerForm').reset(); document.getElementById('customerModal').classList.add('flex'); }
function closeModal() { document.getElementById('customerModal').classList.remove('flex'); }
function editCustomer(id) { fetch(`/api/get_customer.php?id=${id}`).then(r=>r.json()).then(d=>{if(d.success){document.getElementById('modalTitle').innerText='Modifier client'; document.getElementById('formAction').value='edit'; document.getElementById('customerId').value=d.customer.id; document.getElementById('first_name').value=d.customer.first_name; document.getElementById('last_name').value=d.customer.last_name; document.getElementById('phone').value=d.customer.phone; document.getElementById('email').value=d.customer.email; document.getElementById('address').value=d.customer.address; openCustomerModal();}}); }
function deleteCustomer(id) { Swal.fire({title:'Confirmation',text:'Supprimer ce client?',icon:'warning',showCancelButton:true}).then(r=>{if(r.isConfirmed){fetch('/api/customer_crud.php',{method:'POST',body:`action=delete&id=${id}`}).then(()=>location.reload());}}); }
</script>

<?php include 'templates/footer.php'; ?>
