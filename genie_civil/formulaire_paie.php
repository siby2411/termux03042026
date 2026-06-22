<?php

$conn = new mysqli(
'localhost',
'root',
'',
'omega_multisectoriel'
);

if($_SERVER['REQUEST_METHOD']=='POST'){

$nom=$_POST['nom_complet'];
$specialite=$_POST['specialite'];
$type=$_POST['type_remuneration'];
$tarif=$_POST['tarif_base'];

$stmt=$conn->prepare("
INSERT INTO personnel
(
nom_complet,
specialite,
type_remuneration,
tarif_base
)
VALUES(?,?,?,?)
");

$stmt->bind_param(
"sssd",
$nom,
$specialite,
$type,
$tarif
);

$stmt->execute();

header("Location: gestion_rh.php");
exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Nouveau Personnel</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-zinc-950 text-white p-8">

<div class="max-w-3xl mx-auto bg-zinc-900 p-8 rounded-3xl">

<h1 class="text-2xl font-black text-blue-500 mb-8">
NOUVEL OUVRIER / TECHNICIEN
</h1>

<form method="post" class="space-y-4">

<input
name="nom_complet"
placeholder="Nom complet"
required
class="w-full p-3 bg-zinc-800 rounded">

<input
name="specialite"
placeholder="Spécialité"
required
class="w-full p-3 bg-zinc-800 rounded">

<select
name="type_remuneration"
class="w-full p-3 bg-zinc-800 rounded">

<option>Journalier</option>
<option>Forfait</option>
<option>Unitaire</option>

</select>

<input
type="number"
step="0.01"
name="tarif_base"
placeholder="Tarif de base"
required
class="w-full p-3 bg-zinc-800 rounded">

<button
class="w-full bg-blue-600 p-4 rounded-xl font-bold">

ENREGISTRER

</button>

</form>

</div>

</body>
</html>
