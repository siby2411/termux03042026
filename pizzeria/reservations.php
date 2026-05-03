<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Réservation de table";

$tables = $db->query("SELECT * FROM tables_restaurant WHERE is_available=1")->fetchAll();

include 'templates/header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
    <h1 class="text-3xl font-playfair font-bold text-center mb-6">📅 Réservation de table</h1>
    
    <form id="reservationForm" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">Nom complet *</label><input type="text" id="name" required class="w-full px-3 py-2 border rounded-lg"></div>
            <div><label class="block text-sm font-medium mb-1">Téléphone *</label><input type="tel" id="phone" required class="w-full px-3 py-2 border rounded-lg"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">Date *</label><input type="date" id="date" required class="w-full px-3 py-2 border rounded-lg"></div>
            <div><label class="block text-sm font-medium mb-1">Heure *</label><input type="time" id="time" required class="w-full px-3 py-2 border rounded-lg"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">Nombre de personnes *</label><input type="number" id="guests" min="1" max="20" required class="w-full px-3 py-2 border rounded-lg"></div>
            <div><label class="block text-sm font-medium mb-1">Table préférée</label><select id="table" class="w-full px-3 py-2 border rounded-lg"><option value="">Automatique</option><?php foreach($tables as $t): ?><option value="<?php echo $t['table_number']; ?>">Table <?php echo $t['table_number']; ?> (<?php echo $t['capacity']; ?> pers)</option><?php endforeach; ?></select></div>
        </div>
        <div><label class="block text-sm font-medium mb-1">Demande spéciale</label><textarea id="request" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea></div>
        <button type="submit" class="btn-pizza w-full py-3">Confirmer la réservation</button>
    </form>
</div>

<script>
document.getElementById('reservationForm').addEventListener('submit', async(e) => {
    e.preventDefault();
    const data = {name: document.getElementById('name').value, phone: document.getElementById('phone').value, date: document.getElementById('date').value, time: document.getElementById('time').value, guests: document.getElementById('guests').value, table: document.getElementById('table').value, request: document.getElementById('request').value};
    const res = await fetch('/api/create_reservation.php', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)});
    const result = await res.json();
    if(result.success) Swal.fire('Succès!', `Réservation #${result.number} confirmée`, 'success');
    else Swal.fire('Erreur', result.error, 'error');
});
</script>

<?php include 'templates/footer.php'; ?>
