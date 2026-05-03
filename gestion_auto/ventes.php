<?php
/**
 * OMEGA AUTO - VENTES.PHP
 * Liste des transactions et chiffre d'affaires
 */
require_once 'config.php';
$db = Database::getInstance();

try {
    // Jointure pour récupérer le nom du véhicule vendu
    $sql = "SELECT v.*, vh.immatriculation, m.nom as modele_nom, mk.nom as marque_nom 
            FROM ventes v
            JOIN vehicules vh ON v.vehicule_id = vh.id
            JOIN modeles m ON vh.modele_id = m.id
            JOIN marques mk ON m.marque_id = mk.id
            ORDER BY v.date_vente DESC";
    
    $stmt = $db->getConnection()->query($sql);
    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcul du chiffre d'affaires total
    $total_ca = 0;
    foreach($ventes as $vente) {
        $total_ca += (float)$vente['prix_final'];
    }

} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Erreur de base de données : " . $e->getMessage() . "</div>");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Ventes - OMEGA AUTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-cash-stack me-2 text-success"></i>Journal des Ventes</h1>
            <div class="text-end">
                <span class="text-muted d-block">Chiffre d'Affaires Total</span>
                <span class="h3 fw-bold text-success"><?= number_format($total_ca, 0, ',', ' ') ?> FCFA</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Véhicule</th>
                            <th>Client</th>
                            <th>Paiement</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ventes)): ?>
                            <tr><td colspan="5" class="text-center py-4">Aucune vente enregistrée pour le moment.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($ventes as $v): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($v['date_vente'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($v['marque_nom'] . ' ' . $v['modele_nom']) ?></strong><br>
                                <small class="text-muted"><?= $v['immatriculation'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($v['client_nom']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= strtoupper($v['mode_paiement']) ?></span></td>
                            <td class="text-end fw-bold"><?= number_format((float)$v['prix_final'], 0, ',', ' ') ?> FCFA</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            <a href="vehicules.php" class="btn btn-outline-primary">Retour au Parc</a>
        </div>
    </div>
</body>
</html>
