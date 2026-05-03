<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Gestion des Clients";

$customers = $db->query("SELECT * FROM customers ORDER BY created_at DESC")->fetchAll();

include 'templates/header.php';
?>

<div class="bg-white rounded-2xl shadow overflow-hidden">
    <table class="w-full"><thead class="bg-gray-50"><tr><th class="p-3">Code</th><th>Nom</th><th>Téléphone</th><th>Email</th><th>Points</th></tr></thead><tbody>
    <?php foreach($customers as $c): ?><tr class="border-b"><td class="p-3"><?php echo $c['customer_code']; ?></td><td><?php echo $c['first_name'] . ' ' . $c['last_name']; ?></td><td><?php echo $c['phone']; ?></td><td><?php echo $c['email']; ?></td><td class="font-bold"><?php echo $c['loyalty_points']; ?></td></tr><?php endforeach; ?>
    </tbody></table>
</div>

<?php include 'templates/footer.php'; ?>
