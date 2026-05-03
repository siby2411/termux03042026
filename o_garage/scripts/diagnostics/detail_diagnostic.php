<?php
require_once '../../includes/config/config.php';
require_once '../../includes/classes/Database.php';

$db = new Database();
$conn = $db->getConnection();

// Récupérer l'ID du diagnostic depuis l'URL
$id_diagnostic = intval($_GET['id'] ?? 0);

// Récupérer les détails du diagnostic
$sql = "SELECT d.*, c.nom AS nom_client, c.prenom AS prenom_client, c.telephone AS telephone_client, c.immatriculation_vehicule,
               m1.nom AS nom_mecanicien_1, m1.prenom AS prenom_mecanicien_1, m1.telephone AS telephone_mecanicien_1,
               m2.nom AS nom_mecanicien_2, m2.prenom AS prenom_mecanicien_2, m2.telephone AS telephone_mecanicien_2
        FROM diagnostics d
        LEFT JOIN clients c ON d.id_client = c.id_client
        LEFT JOIN mecaniciens m1 ON d.id_mecanicien_1 = m1.id_mecanicien
        LEFT JOIN mecaniciens m2 ON d.id_mecanicien_2 = m2.id_mecanicien
        WHERE d.id_diagnostic = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_diagnostic);
$stmt->execute();
$result = $stmt->get_result();
$diagnostic = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Diagnostic - Omega Garage</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <img src="../../images/banniere_omega.png" alt="Omega Informatique CONSULTING" class="banniere">
        <nav>
            <ul>
                <li><a href="../../index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="liste_diagnostics.php"><i class="fas fa-list"></i> Liste Diagnostics</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1><i class="fas fa-info-circle"></i> Détails du Diagnostic #<?= $diagnostic['id_diagnostic'] ?></h1>
        <div class="diagnostic-detail">
            <div class="detail-section">
                <h2><i class="fas fa-user"></i> Informations Client</h2>
                <p><strong>Nom :</strong> <?= htmlspecialchars($diagnostic['nom_client'] . ' ' . $diagnostic['prenom_client']) ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($diagnostic['telephone_client']) ?></p>
                <p><strong>Véhicule :</strong> <?= htmlspecialchars($diagnostic['immatriculation_vehicule']) ?></p>
            </div>
            <div class="detail-section">
                <h2><i class="fas fa-clipboard-list"></i> Diagnostic</h2>
                <p><strong>Date :</strong> <?= (new DateTime($diagnostic['date_diagnostic']))->format('d/m/Y H:i') ?></p>
                <p><strong>État :</strong> <span class="etat <?= strtolower(str_replace(' ', '-', $diagnostic['etat'])) ?>">
                    <?= htmlspecialchars($diagnostic['etat']) ?></span></p>
                <p><strong>Symptômes :</strong> <?= nl2br(htmlspecialchars($diagnostic['symptomes'])) ?></p>
                <p><strong>Diagnostic :</strong> <?= nl2br(htmlspecialchars($diagnostic['diagnostic'])) ?></p>
                <p><strong>Pièces à remplacer :</strong> <?= nl2br(htmlspecialchars($diagnostic['pieces_a_remplacer'])) ?></p>
                <p><strong>Main d'œuvre :</strong> <?= nl2br(htmlspecialchars($diagnostic['main_d_oeuvre'])) ?></p>
                <p><strong>Coût estimé :</strong> <?= number_format($diagnostic['cout_estime'], 0, ',', ' ') ?> FCFA</p>
            </div>
            <div class="detail-section">
                <h2><i class="fas fa-user-cog"></i> Mécaniciens Assignés</h2>
                <div class="mecanicien">
                    <p><strong>Mécanicien 1 :</strong>
                        <?= !empty($diagnostic['nom_mecanicien_1']) ? htmlspecialchars($diagnostic['nom_mecanicien_1'] . ' ' . $diagnostic['prenom_mecanicien_1']) : 'Non assigné' ?><br>
                        <?= !empty($diagnostic['telephone_mecanicien_1']) ? 'Tél: ' . htmlspecialchars($diagnostic['telephone_mecanicien_1']) : '' ?></p>
                </div>
                <div class="mecanicien">
                    <p><strong>Mécanicien 2 :</strong>
                        <?= !empty($diagnostic['nom_mecanicien_2']) ? htmlspecialchars($diagnostic['nom_mecanicien_2'] . ' ' . $diagnostic['prenom_mecanicien_2']) : 'Non assigné' ?><br>
                        <?= !empty($diagnostic['telephone_mecanicien_2']) ? 'Tél: ' . htmlspecialchars($diagnostic['telephone_mecanicien_2']) : '' ?></p>
                </div>
            </div>
            <div class="actions">
                <a href="modifier_diagnostic.php?id=<?= $diagnostic['id_diagnostic'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                <a href="liste_diagnostics.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
            </div>
        </div>
    </main>
    <footer>
        <p>© 2026 Omega Informatique CONSULTING – Tous droits réservés</p>
    </footer>
</body>
</html>
