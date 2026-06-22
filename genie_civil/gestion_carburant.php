<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost','root','','omega_multisectoriel');

if($conn->connect_error){
    die($conn->connect_error);
}

/* INSERT */
if($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt = $conn->prepare("
        INSERT INTO carburant(date_saisie, engin, litres, cout)
        VALUES (?,?,?,?)
    ");

    $stmt->bind_param(
        "ssdd",
        $_POST['date_saisie'],
        $_POST['engin'],
        $_POST['litres'],
        $_POST['cout']
    );

    $stmt->execute();
    header("Location: gestion_carburant.php");
    exit;
}

/* DATA */
$total = $conn->query("SELECT COALESCE(SUM(cout),0) t FROM carburant")->fetch_assoc()['t'];

$liste = $conn->query("
    SELECT * FROM carburant ORDER BY date_saisie DESC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Carburant</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white p-6">

<div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

<!-- FORM -->
<div class="bg-zinc-900 p-6 rounded-xl">
<h2 class="text-orange-500 font-black mb-4">CARBURANT</h2>

<div class="text-sm mb-4">
TOTAL: <span class="text-green-500 font-bold"><?= number_format($total,0,',',' ') ?></span>
</div>

<form method="POST" class="space-y-3">

<input type="date" name="date_saisie" class="w-full p-2 bg-zinc-800 rounded" required>

<input name="engin" placeholder="Engin"
class="w-full p-2 bg-zinc-800 rounded">

<input type="number" step="0.01" name="litres"
placeholder="Litres"
class="w-full p-2 bg-zinc-800 rounded">

<input type="number" step="0.01" name="cout"
placeholder="Coût"
class="w-full p-2 bg-zinc-800 rounded">

<button class="w-full bg-green-600 py-2 font-bold rounded">
ENREGISTRER
</button>

</form>
</div>

<!-- LISTE -->
<div class="md:col-span-2 bg-zinc-900 p-6 rounded-xl">

<h2 class="text-blue-500 font-black mb-4">HISTORIQUE</h2>

<table class="w-full text-sm">
<thead class="text-gray-400 border-b border-zinc-700">
<tr>
<th>Date</th>
<th>Engin</th>
<th>Litres</th>
<th>Coût</th>
</tr>
</thead>

<tbody>
<?php while($c=$liste->fetch_assoc()): ?>
<tr class="border-b border-zinc-800">
<td class="p-2"><?= $c['date_saisie'] ?></td>
<td class="p-2"><?= $c['engin'] ?></td>
<td class="p-2"><?= $c['litres'] ?></td>
<td class="p-2 text-green-500"><?= number_format($c['cout'],0,',',' ') ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>

</body>
</html>
