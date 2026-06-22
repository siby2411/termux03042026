<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

$conn = new mysqli('localhost','root','','omega_multisectoriel');

/* INSERT */
if($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt=$conn->prepare("
        INSERT INTO equipements(designation,date_acquisition,etat)
        VALUES (?,?,?)
    ");

    $stmt->bind_param(
        "sss",
        $_POST['designation'],
        $_POST['date_acquisition'],
        $_POST['etat']
    );

    $stmt->execute();
    header("Location: gestion_equipements.php");
    exit;
}

/* DATA */
$total = $conn->query("SELECT COUNT(*) t FROM equipements")->fetch_assoc()['t'];
$liste = $conn->query("SELECT * FROM equipements ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Equipements</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white p-6">

<div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

<!-- FORM -->
<div class="bg-zinc-900 p-6 rounded-xl">

<h2 class="text-cyan-500 font-black mb-4">EQUIPEMENTS</h2>

<div class="text-sm mb-4">
TOTAL: <span class="text-cyan-400 font-bold"><?= $total ?></span>
</div>

<form method="POST" class="space-y-3">

<input name="designation" placeholder="Désignation"
class="w-full p-2 bg-zinc-800 rounded">

<input type="date" name="date_acquisition"
class="w-full p-2 bg-zinc-800 rounded">

<select name="etat" class="w-full p-2 bg-zinc-800 rounded">
<option>Neuf</option>
<option>Bon état</option>
<option>Moyen</option>
<option>Hors service</option>
</select>

<button class="w-full bg-cyan-600 py-2 font-bold rounded">
AJOUTER
</button>

</form>

</div>

<!-- LISTE -->
<div class="md:col-span-2 bg-zinc-900 p-6 rounded-xl">

<table class="w-full text-sm">

<thead class="text-gray-400 border-b border-zinc-700">
<tr>
<th>Nom</th>
<th>Date</th>
<th>Etat</th>
</tr>
</thead>

<tbody>

<?php while($e=$liste->fetch_assoc()): ?>
<tr class="border-b border-zinc-800">
<td class="p-2"><?= $e['designation'] ?></td>
<td class="p-2"><?= $e['date_acquisition'] ?></td>
<td class="p-2 text-yellow-400"><?= $e['etat'] ?></td>
</tr>
<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>
</html>
