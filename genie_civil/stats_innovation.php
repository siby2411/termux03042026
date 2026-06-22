<?php
$conn = new mysqli('localhost','root','','omega_multisectoriel');
require_once "hse_engine.php";

$total = $conn->query("SELECT SUM(montant_reel) t FROM depenses_details")->fetch_assoc()['t'];

$fraude = $conn->query("
SELECT libelle,
montant_prevu,
montant_reel,
CASE WHEN montant_reel > montant_prevu*1.3 THEN 'FRAUDE_SUSPECTE' ELSE 'OK' END AS status
FROM depenses_details
");
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.tailwindcss.com"></script>
<title>IA ANALYTICS</title>
</head>

<body class="bg-black text-white p-6">

<h1>IA AUDIT ENGINE</h1>

<h2>Total : <?= number_format($total,0,',',' ') ?> FCFA</h2>

<table class="w-full mt-4 text-sm">
<tr>
<th>Libellé</th>
<th>Prévu</th>
<th>Réel</th>
<th>Status IA</th>
</tr>

<?php while($f = $fraude->fetch_assoc()): ?>
<tr class="border-b border-zinc-800">
<td><?= $f['libelle'] ?></td>
<td><?= $f['montant_prevu'] ?></td>
<td><?= $f['montant_reel'] ?></td>
<td class="<?= $f['status']=='FRAUDE_SUSPECTE'?'text-red-500':'text-green-500' ?>">
<?= $f['status'] ?>
</td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
