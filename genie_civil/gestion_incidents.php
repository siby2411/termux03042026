<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

$conn = new mysqli('localhost','root','','omega_multisectoriel');

if($conn->connect_error){
    die($conn->connect_error);
}

/* INSERT */
if($_SERVER['REQUEST_METHOD']==='POST'){

    $stmt = $conn->prepare("
        INSERT INTO incidents(date_incident, description, gravite)
        VALUES (?,?,?)
    ");

    $stmt->bind_param(
        "sss",
        $_POST['date_incident'],
        $_POST['description'],
        $_POST['gravite']
    );

    $stmt->execute();
    header("Location: gestion_incidents.php");
    exit;
}

/* DATA */
$liste = $conn->query("
    SELECT * FROM incidents ORDER BY date_incident DESC
");

require_once "hse_engine.php";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Gestion Incidents</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white p-6">

<div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

<!-- FORM -->
<div class="bg-zinc-900 p-6 rounded-xl">

<h2 class="text-red-500 font-black mb-4">
SÉCURITÉ CHANTIER
</h2>

<form method="POST" class="space-y-3">

<input type="date" name="date_incident"
class="w-full p-2 bg-zinc-800 rounded" required>

<textarea name="description"
class="w-full p-2 bg-zinc-800 rounded"
placeholder="Description incident"></textarea>

<select name="gravite"
class="w-full p-2 bg-zinc-800 rounded">

<option>Faible</option>
<option>Moyenne</option>
<option>Élevée</option>
<option>Critique</option>

</select>

<button class="w-full bg-red-600 py-2 font-bold rounded">
ENREGISTRER
</button>

</form>

</div>

<!-- DASHBOARD HSE -->
<div class="md:col-span-2 space-y-4">

<?php require_once "hse_engine.php"; ?>

<div class="bg-zinc-900 p-6 rounded-xl">

<h2 class="text-orange-500 font-black mb-4">
HISTORIQUE INCIDENTS
</h2>

<table class="w-full text-sm">

<tbody>

<?php while($i=$liste->fetch_assoc()): ?>

<tr class="border-b border-zinc-800">

<td class="p-2"><?= $i['date_incident'] ?></td>
<td class="p-2"><?= $i['description'] ?></td>

<td class="p-2">
<span class="text-red-400 font-bold">
<?= $i['gravite'] ?>
</span>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</div>

</body>
</html>
