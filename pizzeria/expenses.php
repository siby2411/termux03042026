<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Gestion des Charges";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expense_number = 'EXP-' . date('Ymd') . '-' . rand(1000,9999);
    $stmt = $db->prepare("INSERT INTO expenses (expense_number, expense_date, category, description, amount, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$expense_number, $_POST['date'], $_POST['category'], $_POST['description'], $_POST['amount'], $_POST['payment_method']]);
    $success = true;
}

$expenses = $db->query("SELECT * FROM expenses ORDER BY expense_date DESC LIMIT 50")->fetchAll();
$total_month = $db->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE MONTH(expense_date)=MONTH(CURDATE())")->fetchColumn();
$total_year = $db->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE YEAR(expense_date)=YEAR(CURDATE())")->fetchColumn();

include 'templates/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl p-6 shadow">
            <h2 class="text-xl font-bold mb-4">💰 Ajouter une charge</h2>
            <?php if(isset($success)): ?><div class="bg-green-100 text-green-700 p-2 rounded mb-4">Charge ajoutée</div><?php endif; ?>
            <form method="POST">
                <div class="mb-3"><label>Date</label><input type="date" name="date" required class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-3"><label>Catégorie</label><select name="category" class="w-full px-3 py-2 border rounded"><option value="ingredients">🥗 Ingrédients</option><option value="salary">👨‍🍳 Salaires</option><option value="rent">🏠 Loyer</option><option value="electricity">⚡ Électricité</option><option value="water">💧 Eau</option><option value="marketing">📢 Marketing</option><option value="maintenance">🔧 Maintenance</option></select></div>
                <div class="mb-3"><label>Description</label><textarea name="description" required class="w-full px-3 py-2 border rounded"></textarea></div>
                <div class="mb-3"><label>Montant (CFA)</label><input type="number" name="amount" required class="w-full px-3 py-2 border rounded"></div>
                <div class="mb-3"><label>Mode paiement</label><select name="payment_method" class="w-full px-3 py-2 border rounded"><option value="cash">Espèces</option><option value="wave">Wave</option><option value="orange_money">Orange Money</option></select></div>
                <button type="submit" class="btn-pizza w-full">Enregistrer</button>
            </form>
        </div>
    </div>
    
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl p-6 shadow">
            <div class="flex justify-between mb-4">
                <h2 class="text-xl font-bold">📋 Historique des charges</h2>
                <div><span class="text-sm">Ce mois: <strong class="text-red-600"><?php echo formatPrice($total_month); ?></strong></span><span class="text-sm ml-4">Année: <strong class="text-red-600"><?php echo formatPrice($total_year); ?></strong></span></div>
            </div>
            <div class="overflow-x-auto"><table class="w-full"><thead><tr class="border-b"><th class="text-left py-2">Date</th><th class="text-left py-2">Catégorie</th><th class="text-left py-2">Description</th><th class="text-right py-2">Montant</th></tr></thead><tbody>
            <?php foreach($expenses as $e): ?><tr class="border-b"><td class="py-2"><?php echo $e['expense_date']; ?></td><td class="py-2"><?php echo $e['category']; ?></td><td class="py-2"><?php echo substr($e['description'],0,50); ?></td><td class="py-2 text-right font-bold"><?php echo formatPrice($e['amount']); ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
