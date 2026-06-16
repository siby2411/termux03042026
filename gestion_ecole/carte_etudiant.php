<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$id = intval($_GET['id'] ?? 0);

$query = "SELECT e.nom, e.prenom, e.code_etudiant, c.nom_class, e.adresse, ce.photo_path
          FROM etudiants e
          JOIN classes c ON e.classe_id = c.id
          LEFT JOIN cartes_etudiants ce ON e.id = ce.etudiant_id
          WHERE e.id = $id";

$result = $conn->query($query);
$e = $result ? $result->fetch_assoc() : null;

if (!$e) { die("<h1>Étudiant introuvable</h1>"); }

$default_img = 'assets/default_avatar.png';
$img_src = (!empty($e['photo_path']) && file_exists($e['photo_path'])) ? $e['photo_path'] : $default_img;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carte Étudiant - OMEGA CONSULTING</title>
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #e3f2fd; font-family: 'Segoe UI', Arial, sans-serif; }
        .badge-card { width: 520px; height: 310px; background: linear-gradient(135deg, #2196f3 0%, #bbdefb 100%); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 25px; display: flex; position: relative; border: 1px solid #90caf9; }
        .left-side { width: 35%; border-right: 2px solid #ffffff; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; }
        .right-side { width: 65%; padding-left: 30px; display: flex; flex-direction: column; justify-content: center; }
        .brand-name { font-family: 'Arial Black', sans-serif; font-size: 14px; text-align: center; color: #ffffff; letter-spacing: 1px; margin-bottom: 15px; text-transform: uppercase; }
        .photo-frame { width: 100px; height: 100px; border-radius: 12px; border: 3px solid #ffffff; object-fit: cover; box-shadow: 0 5px 15px rgba(0,0,0,0.2); background: #f8f9fa; }
        .info-label { color: #1565c0; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin-top: 15px; font-weight: bold; }
        .info-value { color: #0d47a1; font-weight: 700; font-size: 17px; margin-top: 2px; }
        .code-id { background: #0d47a1; color: white; padding: 3px 8px; border-radius: 5px; font-family: monospace; margin-top: 8px; font-size: 13px; }
        .qr-code { margin-top: 12px; }
        .footer-badge { position: absolute; bottom: 20px; right: 25px; font-size: 11px; color: #0d47a1; font-weight: bold; }
    </style>
</head>
<body>

<div class="badge-card">
    <div class="left-side">
        <div class="brand-name">OMEGA<br>CONSULTING</div>
        <img src="<?= htmlspecialchars($img_src) ?>" class="photo-frame" alt="Photo">
        <div class="code-id"><?= htmlspecialchars($e['code_etudiant'] ?? 'N/A') ?></div>

        <div class="qr-code">
            <?php
            $url_verif = "http://127.0.0.1:8000/verification.php?id=" . $id;
            $qr_src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($url_verif);
            ?>
            <a href="<?= $url_verif ?>" target="_blank" title="Cliquez pour vérifier l'étudiant">
                <img src="<?= $qr_src ?>" alt="QR Code" style="width: 70px; height: 70px; border: 2px solid white; background: white; padding: 2px;">
            </a>
        </div>
    </div>
    <div class="right-side">
        <div class="info-label">Nom & Prénom</div>
        <div class="info-value"><?= htmlspecialchars(($e['nom'] ?? '') . ' ' . ($e['prenom'] ?? '')) ?></div>
        <div class="info-label">Classe</div>
        <div class="info-value"><?= htmlspecialchars($e['nom_class'] ?? 'N/A') ?></div>
        <div class="info-label">Adresse</div>
        <div class="info-value"><?= htmlspecialchars($e['adresse'] ?? 'N/A') ?></div>
    </div>
    <div class="footer-badge">2026 | CARTE PROFESSIONNELLE</div>
</div>

</body>
</html>
