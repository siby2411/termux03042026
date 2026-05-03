<?php
require_once '../../includes/config/config.php';
require_once '../../includes/classes/Database.php';

$db = new Database();
$conn = $db->getConnection();

// Récupérer les données financières
$sql = "SELECT SUM(cout_estime) AS total_estimations, COUNT(*) AS nombre_diagnostics FROM diagnostics WHERE etat = 'Facturé'";
$result = $conn->query($sql);
$etat_financier = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État Financier - Omega Garage</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <img src="../../images/banniere_omega.png" alt="Omega Informatique CONSULTING" class="banniere">
        <nav>
            <ul>
                <li><a href="../../index.php"><i class="fas fa-home"></i> Accueil</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1><i class="fas fa-chart-line"></i> État Financier</h1>
        <div class="etat-financier">
            <div class="financier-card">
                <h2><i class="fas fa-coins"></i> Total des Estimations</h2>
                <p><?= number_format($etat_financier['total_estimations'], 0, ',', ' ') ?> FCFA</p>
            </div>
            <div class="financier-card">
                <h2><i class="fas fa-clipboard-list"></i> Nombre de Diagnostics Facturés</h2>
                <p><?= $etat_financier['nombre_diagnostics'] ?></p>
            </div>
        </div>
    </main>
    <footer>
        <p>© 2026 Omega Informatique CONSULTING – Tous droits réservés</p>
    </footer>
</body>
</html>
