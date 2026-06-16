<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$etudiants = $conn->query("SELECT id, nom, prenom, code_etudiant FROM etudiants");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion QR Codes - OMEGA ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
    <div class="container shadow-sm p-4 bg-white rounded">
        <h2 class="mb-4 text-primary"><i class="bi bi-qr-code-scan"></i> Annuaire des QR Codes Étudiants</h2>
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Code</th>
                    <th>Nom & Prénom</th>
                    <th>QR Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($e = $etudiants->fetch_assoc()): 
                    $url = "https://" . $_SERVER['HTTP_HOST'] . "/verification.php?id=" . $e['id'];
                    $qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($url);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($e['code_etudiant']) ?></strong></td>
                    <td><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?></td>
                    <td><img src="<?= $qr ?>" alt="QR" style="width: 70px; border: 1px solid #ddd; padding: 3px;"></td>
                    <td>
                        <div class="btn-group-vertical">
                            <a href="<?= $qr ?>" download="QR_<?= $e['code_etudiant'] ?>.png" class="btn btn-primary btn-sm mb-1">
                                <i class="bi bi-download"></i> Télécharger
                            </a>
                            <a href="https://wa.me/221776542803?text=Bonjour,%20voici%20le%20lien%20de%20vérification%20pour%20l'étudiant%20<?= urlencode($e['nom'] . ' ' . $e['prenom']) ?>:%20<?= urlencode($url) ?>" target="_blank" class="btn btn-success btn-sm">
                                <i class="bi bi-whatsapp"></i> WhatsApp (77 654 28 03)
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary mt-3">Retour à l'accueil</a>
    </div>
</body>
</html>
