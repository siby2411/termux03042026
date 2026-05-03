<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Service Réparation - Omega Scooter";

$repairs = $db->query("SELECT * FROM repairs ORDER BY created_at DESC LIMIT 30")->fetchAll();

include 'templates/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl p-6 shadow">
            <h2 class="text-xl font-bold mb-4">🔧 Nouvelle demande de réparation</h2>
            <form id="repairForm">
                <div class="mb-3"><input type="text" id="customer_name" placeholder="Nom complet" required class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-3"><input type="tel" id="customer_phone" placeholder="Téléphone" required class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-3"><input type="text" id="scooter_brand" placeholder="Marque scooter" class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-3"><input type="text" id="scooter_model" placeholder="Modèle" class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-3"><textarea id="problem" placeholder="Description du problème" rows="3" required class="w-full px-3 py-2 border rounded"></textarea></div>
                <div class="mb-3"><input type="number" id="estimated_cost" placeholder="Coût estimé (CFA)" class="w-full px-3 py-2 border rounded"></div>
                <button type="submit" class="btn-scooter w-full">Enregistrer la demande</button>
            </form>
        </div>
    </div>
    
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl p-6 shadow">
            <h2 class="text-xl font-bold mb-4">📋 Demandes de réparation</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="p-2">N°</th><th>Client</th><th>Scooter</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($repairs as $r): ?>
                        <tr class="border-b">
                            <td class="p-2 font-mono"><?php echo $r['repair_number']; ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($r['customer_name']); ?></td>
                            <td class="p-2"><?php echo $r['scooter_brand'] . ' ' . $r['scooter_model']; ?></td>
                            <td class="p-2"><span class="px-2 py-1 rounded-full text-xs <?php echo $r['status']=='pending'?'bg-yellow-100 text-yellow-800':($r['status']=='completed'?'bg-green-100 text-green-800':'bg-blue-100'); ?>"><?php echo $r['status']; ?></span></td>
                            <td class="p-2"><button onclick="updateRepair(<?php echo $r['id']; ?>)" class="text-blue-600"><i class="fas fa-edit"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('repairForm').addEventListener('submit', async(e) => {
    e.preventDefault();
    const data = {
        customer_name: document.getElementById('customer_name').value,
        customer_phone: document.getElementById('customer_phone').value,
        scooter_brand: document.getElementById('scooter_brand').value,
        scooter_model: document.getElementById('scooter_model').value,
        problem_description: document.getElementById('problem').value,
        estimated_cost: document.getElementById('estimated_cost').value
    };
    const res = await fetch('/api/add_repair.php', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)});
    const result = await res.json();
    if(result.success) Swal.fire('Succès', 'Demande enregistrée', 'success').then(() => location.reload());
    else Swal.fire('Erreur', result.error, 'error');
});
</script>

<?php include 'templates/footer.php'; ?>
