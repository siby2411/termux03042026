<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

if ($conn->connect_error) {
    die("Erreur connexion: " . $conn->connect_error);
}

/* INSERT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $conn->prepare("
        INSERT INTO fournisseurs (nom, telephone, email, adresse)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssss",
        $_POST['nom'],
        $_POST['telephone'],
        $_POST['email'],
        $_POST['adresse']
    );

    $stmt->execute();

    header("Location: gestion_fournisseurs.php");
    exit;
}

/* LISTE */
$liste = $conn->query("
    SELECT * FROM fournisseurs
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Fournisseurs</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-zinc-950 text-white p-6">

<div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

<!-- FORM -->
<div class="bg-zinc-900 p-6 rounded-xl">

<h2 class="text-orange-500 font-black mb-4">
NOUVEAU FOURNISSEUR
</h2>

<form method="POST" class="space-y-3">

<input name="nom"
placeholder="Nom fournisseur"
class="w-full p-2 bg-zinc-800 rounded"
required>

<input name="telephone"
placeholder="Téléphone"
class="w-full p-2 bg-zinc-800 rounded">

<input name="email"
placeholder="Email"
class="w-full p-2 bg-zinc-800 rounded">

<textarea name="adresse"
placeholder="Adresse"
class="w-full p-2 bg-zinc-800 rounded"></textarea>

<button class="w-full bg-green-600 py-2 font-bold rounded">
ENREGISTRER
</button>

</form>

</div>

<!-- TABLE -->
<div class="md:col-span-2 bg-zinc-900 p-6 rounded-xl">

<h2 class="text-blue-500 font-black mb-4">
LISTE FOURNISSEURS
</h2>

<table class="w-full text-sm">

<thead class="text-gray-400 border-b border-zinc-700">
<tr>
<th class="text-left p-2">Nom</th>
<th class="text-left p-2">Téléphone</th>
<th class="text-left p-2">Email</th>
</tr>
</thead>

<tbody>

<?php while($f = $liste->fetch_assoc()): ?>

<tr class="border-b border-zinc-800">
<td class="p-2"><?= $f['nom'] ?></td>
<td class="p-2"><?= $f['telephone'] ?></td>
<td class="p-2"><?= $f['email'] ?></td>
</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>
</html>
