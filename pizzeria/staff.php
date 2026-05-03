<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Gestion du Personnel";

// Traitement CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_staff') {
        $stmt = $db->prepare("INSERT INTO staff (first_name, last_name, position, phone, email, hire_date, base_salary) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['position'], $_POST['phone'], $_POST['email'] ?? '', $_POST['hire_date'], $_POST['base_salary']]);
        $success = "Employé ajouté";
    } elseif ($action == 'add_attendance') {
        $stmt = $db->prepare("INSERT INTO staff_attendance (staff_id, attendance_date, check_in, check_out, status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE check_in=VALUES(check_in), check_out=VALUES(check_out), status=VALUES(status)");
        $stmt->execute([$_POST['staff_id'], $_POST['attendance_date'], $_POST['check_in'], $_POST['check_out'], $_POST['status']]);
        $success = "Pointage enregistré";
    } elseif ($action == 'add_bonus') {
        $stmt = $db->prepare("INSERT INTO staff_bonuses (staff_id, bonus_date, bonus_type, amount, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['staff_id'], $_POST['bonus_date'], $_POST['bonus_type'], $_POST['amount'], $_POST['reason']]);
        $success = "Prime ajoutée";
    } elseif ($action == 'process_payroll') {
        $payroll_number = 'PAY-' . date('Ym') . '-' . rand(1000, 9999);
        $stmt = $db->prepare("INSERT INTO payroll (payroll_number, staff_id, month_year, base_salary, net_salary, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$payroll_number, $_POST['staff_id'], $_POST['month_year'], $_POST['base_salary'], $_POST['base_salary']]);
        $success = "Paie générée";
    }
}

// Récupération des données
$staff = $db->query("SELECT * FROM staff WHERE is_active=1 ORDER BY position, first_name")->fetchAll();
$attendance_today = $db->query("SELECT a.*, s.first_name, s.last_name, s.position FROM staff_attendance a JOIN staff s ON a.staff_id=s.id WHERE a.attendance_date=CURDATE()")->fetchAll();
$absent_today = $db->query("SELECT s.id, s.first_name, s.last_name FROM staff s WHERE s.is_active=1 AND s.id NOT IN (SELECT staff_id FROM staff_attendance WHERE attendance_date=CURDATE())")->fetchAll();

include 'templates/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="stat-card"><div><p class="text-gray-500">Total employés</p><h3 class="text-2xl font-bold"><?php echo count($staff); ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Présents aujourd'hui</p><h3 class="text-2xl font-bold text-green-600"><?php echo count($attendance_today); ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Absents aujourd'hui</p><h3 class="text-2xl font-bold text-red-600"><?php echo count($absent_today); ?></h3></div></div>
</div>

<!-- Onglets -->
<div class="mb-6 flex border-b flex-wrap">
    <button class="tab-btn active px-4 py-2 text-red-600 border-b-2 border-red-600" onclick="showTab('list')">👥 Liste employés</button>
    <button class="tab-btn px-4 py-2" onclick="showTab('attendance')">📅 Pointage</button>
    <button class="tab-btn px-4 py-2" onclick="showTab('schedule')">📆 Planning</button>
    <button class="tab-btn px-4 py-2" onclick="showTab('payroll')">💰 Paie</button>
    <button class="tab-btn px-4 py-2" onclick="showTab('add')">➕ Nouvel employé</button>
</div>

<!-- Tab Liste employés -->
<div id="tab-list" class="tab-content">
    <div class="bg-white rounded-2xl shadow overflow-x-auto">
        <table class="w-full"><thead class="bg-gray-50"><tr><th>Code</th><th>Nom complet</th><th>Poste</th><th>Téléphone</th><th>Salaire base</th><th>Actions</th></tr></thead><tbody>
        <?php foreach($staff as $s): ?><tr class="border-b"><td class="px-4 py-3 font-mono"><?php echo $s['employee_code']; ?></td><td class="px-4 py-3 font-semibold"><?php echo $s['first_name'] . ' ' . $s['last_name']; ?></td><td class="px-4 py-3"><?php echo $s['position']; ?></td><td class="px-4 py-3"><?php echo $s['phone']; ?></td><td class="px-4 py-3"><?php echo formatPrice($s['base_salary']); ?></td><td class="px-4 py-3"><button onclick="editStaff(<?php echo $s['id']; ?>)" class="text-blue-600"><i class="fas fa-edit"></i></button></td></tr><?php endforeach; ?>
        </tbody></table>
    </div>
</div>

<!-- Tab Pointage -->
<div id="tab-attendance" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-4 shadow">
            <h3 class="font-bold mb-3">✅ Pointage du jour</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_attendance">
                <div class="mb-2"><select name="staff_id" required class="w-full px-3 py-2 border rounded"><option value="">Sélectionner employé</option><?php foreach($staff as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo $s['first_name'] . ' ' . $s['last_name']; ?> (<?php echo $s['position']; ?>)</option><?php endforeach; ?></select></div>
                <div class="mb-2"><input type="date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-2"><input type="time" name="check_in" placeholder="Heure arrivée" class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-2"><input type="time" name="check_out" placeholder="Heure départ" class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-2"><select name="status" class="w-full px-3 py-2 border rounded"><option value="present">Présent</option><option value="late">Retard</option><option value="half_day">Demi-journée</option><option value="absent">Absent</option></select></div>
                <button type="submit" class="btn-pizza w-full">Enregistrer pointage</button>
            </form>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow">
            <h3 class="font-bold mb-3">📊 Pointages récents</h3>
            <div class="space-y-2 max-h-96 overflow-y-auto"><?php
            $recent = $db->query("SELECT a.*, s.first_name, s.last_name FROM staff_attendance a JOIN staff s ON a.staff_id=s.id ORDER BY a.attendance_date DESC LIMIT 20")->fetchAll();
            foreach($recent as $r): ?><div class="flex justify-between border-b py-2"><span><?php echo $r['first_name'] . ' ' . $r['last_name']; ?></span><span><?php echo $r['attendance_date']; ?></span><span class="<?php echo $r['status']=='present'?'text-green-600':($r['status']=='late'?'text-orange-600':'text-red-600'); ?>"><?php echo $r['status']; ?></span></div><?php endforeach; ?></div>
        </div>
    </div>
</div>

<!-- Tab Planning -->
<div id="tab-schedule" class="tab-content hidden">
    <div class="bg-white rounded-2xl p-6 shadow">
        <h3 class="font-bold text-xl mb-4">📆 Planning hebdomadaire</h3>
        <div class="overflow-x-auto">
            <table class="w-full"><thead><tr class="bg-gray-100"><th class="p-2">Employé</th><th class="p-2">Lun</th><th class="p-2">Mar</th><th class="p-2">Mer</th><th class="p-2">Jeu</th><th class="p-2">Ven</th><th class="p-2">Sam</th><th class="p-2">Dim</th></tr></thead><tbody>
            <?php foreach($staff as $s): ?><tr class="border-b"><td class="p-2 font-semibold"><?php echo $s['first_name']; ?></td><?php for($i=1;$i<=7;$i++): ?><td class="p-2 text-center"><?php $schedule = $db->query("SELECT start_time, end_time FROM work_schedules WHERE staff_id={$s['id']} AND day_of_week=ELT($i, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")->fetch(); if($schedule): ?><span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded"><?php echo substr($schedule['start_time'],0,5); ?>-<?php echo substr($schedule['end_time'],0,5); ?></span><?php else: ?><span class="text-xs text-gray-400">off</span><?php endif; ?></td><?php endfor; ?></tr><?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
</div>

<!-- Tab Paie -->
<div id="tab-payroll" class="tab-content hidden">
    <div class="bg-white rounded-2xl p-6 shadow">
        <h3 class="font-bold text-xl mb-4">💰 Génération paie</h3>
        <form method="POST" class="grid grid-cols-2 gap-4">
            <input type="hidden" name="action" value="process_payroll">
            <div><label>Employé</label><select name="staff_id" required class="w-full px-3 py-2 border rounded"><?php foreach($staff as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo $s['first_name'] . ' ' . $s['last_name']; ?></option><?php endforeach; ?></select></div>
            <div><label>Mois</label><input type="month" name="month_year" required class="w-full px-3 py-2 border rounded"></div>
            <div><label>Salaire base</label><input type="number" name="base_salary" required class="w-full px-3 py-2 border rounded"></div>
            <div class="flex items-end"><button type="submit" class="btn-pizza w-full">Générer bulletin</button></div>
        </form>
        
        <div class="mt-6"><h3 class="font-bold mb-3">📋 Bulletins générés</h3>
        <table class="w-full"><thead><tr class="border-b"><th>N° bulletin</th><th>Employé</th><th>Mois</th><th>Net à payer</th><th>Statut</th></tr></thead><tbody><?php $payrolls = $db->query("SELECT p.*, s.first_name, s.last_name FROM payroll p JOIN staff s ON p.staff_id=s.id ORDER BY p.created_at DESC LIMIT 20")->fetchAll(); foreach($payrolls as $p): ?><tr class="border-b"><td class="py-2"><?php echo $p['payroll_number']; ?></td><td><?php echo $p['first_name'] . ' ' . $p['last_name']; ?></td><td><?php echo date('m/Y', strtotime($p['month_year'])); ?></td><td class="font-bold"><?php echo formatPrice($p['net_salary']); ?></td><td><span class="px-2 py-1 rounded-full text-xs <?php echo $p['status']=='paid'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700'; ?>"><?php echo $p['status']; ?></span></td></tr><?php endforeach; ?></tbody></table></div>
    </div>
</div>

<!-- Tab Ajout employé -->
<div id="tab-add" class="tab-content hidden">
    <div class="bg-white rounded-2xl p-6 shadow max-w-2xl mx-auto">
        <h3 class="font-bold text-xl mb-4">➕ Nouvel employé</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_staff">
            <div class="grid grid-cols-2 gap-4">
                <div><label>Prénom</label><input type="text" name="first_name" required class="w-full px-3 py-2 border rounded"></div>
                <div><label>Nom</label><input type="text" name="last_name" required class="w-full px-3 py-2 border rounded"></div>
                <div><label>Poste</label><select name="position" class="w-full px-3 py-2 border rounded"><option value="chef">Chef</option><option value="cuisinier">Cuisinier</option><option value="serveur">Serveur</option><option value="caissier">Caissier</option><option value="livreur">Livreur</option></select></div>
                <div><label>Téléphone</label><input type="tel" name="phone" class="w-full px-3 py-2 border rounded"></div>
                <div><label>Email</label><input type="email" name="email" class="w-full px-3 py-2 border rounded"></div>
                <div><label>Date embauche</label><input type="date" name="hire_date" required class="w-full px-3 py-2 border rounded"></div>
                <div><label>Salaire base (CFA)</label><input type="number" name="base_salary" required class="w-full px-3 py-2 border rounded"></div>
            </div>
            <button type="submit" class="btn-pizza w-full mt-6">Ajouter employé</button>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
    document.getElementById(`tab-${tabName}`).classList.remove('hidden');
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active', 'text-red-600', 'border-b-2', 'border-red-600'));
    event.target.classList.add('active', 'text-red-600', 'border-b-2', 'border-red-600');
}
</script>

<?php include 'templates/footer.php'; ?>
