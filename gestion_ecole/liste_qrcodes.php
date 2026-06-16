<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$etudiants = $conn->query("SELECT id, nom, prenom, code_etudiant FROM etudiants");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des QR Codes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h2>Annuaire des QR Codes Étudiants</h2>
    <table class="table table-bordered">
        <tr><th>Code</th><th>Nom</th><th>QR Code</th></tr>
        <?php while($e = $etudiants->fetch_assoc()): 
            $url = "http://127.0.0.1:8000/verification.php?id=" . $e['id'];
            $qr = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($url);
        ?>
        <tr>
            <td><?= $e['code_etudiant'] ?></td>
            <td><?= $e['nom'] . ' ' . $e['prenom'] ?></td>
            <td><img src="<?= $qr ?>" alt="QR"></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
