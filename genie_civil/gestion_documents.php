<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

$conn = new mysqli('localhost','root','','omega_multisectoriel');

/* UPLOAD */
if($_SERVER['REQUEST_METHOD']==='POST'){

    $file = $_FILES['fichier']['name'];
    $tmp = $_FILES['fichier']['tmp_name'];

    $path = "uploads_documents/".$file;

    move_uploaded_file($tmp,$path);

    $stmt=$conn->prepare("
        INSERT INTO documents(titre,fichier,date_upload)
        VALUES (?,?,NOW())
    ");

    $stmt->bind_param("ss",
        $_POST['titre'],
        $file
    );

    $stmt->execute();

    header("Location: gestion_documents.php");
    exit;
}

$liste=$conn->query("SELECT * FROM documents ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Documents</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white p-6">

<div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

<!-- FORM -->
<div class="bg-zinc-900 p-6 rounded-xl">

<h2 class="text-yellow-500 font-black mb-4">DOCUMENTS</h2>

<form method="POST" enctype="multipart/form-data" class="space-y-3">

<input name="titre" placeholder="Titre document"
class="w-full p-2 bg-zinc-800 rounded">

<input type="file" name="fichier"
class="w-full p-2 bg-zinc-800 rounded">

<button class="w-full bg-yellow-600 py-2 font-bold rounded">
UPLOAD
</button>

</form>

</div>

<!-- LISTE -->
<div class="md:col-span-2 bg-zinc-900 p-6 rounded-xl">

<table class="w-full text-sm">

<thead class="text-gray-400 border-b border-zinc-700">
<tr>
<th>Titre</th>
<th>Fichier</th>
<th>Date</th>
</tr>
</thead>

<tbody>

<?php while($d=$liste->fetch_assoc()): ?>
<tr class="border-b border-zinc-800">
<td class="p-2"><?= $d['titre'] ?></td>
<td class="p-2 text-blue-400"><?= $d['fichier'] ?></td>
<td class="p-2"><?= $d['date_upload'] ?></td>
</tr>
<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>
</html>
