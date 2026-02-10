<?php
// /var/www/piece_auto/public/modules/impact_rappel.php
// Module pour analyser les ventes et les clients potentiellement impactés par un rappel de pièce.

$page_title = "Analyse d'Impact du Rappel";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php'; // Correction de la ligne 7

$database = new Database();
$db = $database->getConnection();

$message = '';
$rappel_data = null;
$ventes_impactees = [];
$id_piece_a_tester = (int)($_GET['id_piece'] ?? 0); // Utiliser id_piece pour la démo

try {
    // =================================================================================
    // 2. LOGIQUE DU RAPPORT
    // =================================================================================

    // --- A. Simulation : Récupération de la pièce concernée par le rappel ---
    if ($id_piece_a_tester > 0) {
        $query_piece = "SELECT id_piece, reference, nom_piece FROM PIECES WHERE id_piece = :id_piece";
        $stmt_piece = $db->prepare($query_piece);
        $stmt_piece->execute([':id_piece' => $id_piece_a_tester]);
        $rappel_data = $stmt_piece->fetch(PDO::FETCH_ASSOC);

        if ($rappel_data) {
            // --- B. Recherche des ventes impactées ---
            $query_ventes = "
                SELECT
                    cv.id_commande_vente,
                    cv.date_commande,
                    cv.montant_total,
                    c.nom,
                    c.prenom,
                    c.telephone,
                    c.email
                FROM COMMANDE_VENTE cv
                JOIN CLIENTS c ON cv.id_client = c.id_client
                JOIN LIGNE_VENTE lv ON cv.id_commande_vente = lv.id_commande_vente
                WHERE lv.id_piece = :id_piece_a_tester
                GROUP BY cv.id_commande_vente
                ORDER BY cv.date_commande DESC
            ";
            
            $stmt_ventes = $db->prepare($query_ventes);
            $stmt_ventes->execute([':id_piece_a_tester' => $id_piece_a_tester]);
            $ventes_impactees = $stmt_ventes->fetchAll(PDO::FETCH_ASSOC);

        } else {
            $message = '<div class="alert alert-warning">Veuillez sélectionner une pièce valide pour l\'analyse.</div>';
        }
    } else {
        $message = '<div class="alert alert-info">Veuillez entrer l\'ID d\'une pièce pour simuler un rappel et analyser son impact. (Ex: ajouter `?id_piece=1` dans l\'URL)</div>';
    }

} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Erreur de base de données lors de l\'analyse d\'impact : ' . $e->getMessage() . '</div>';
}

?>

<h1><i class="fas fa-exclamation-triangle"></i> <?= $page_title ?></h1>
<p class="lead">Recherche des commandes clients contenant la pièce spécifiée pour évaluer l'impact potentiel d'un rappel.</p>
<hr>

<?= $message ?>

<?php if ($rappel_data && !empty($ventes_impactees)): ?>
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            Analyse d'Impact pour la pièce : <strong><?= htmlspecialchars($rappel_data['reference'] . ' - ' . $rappel_data['nom_piece']) ?></strong>
        </div>
        <div class="card-body">
            <p><strong><?= count($ventes_impactees) ?></strong> commandes de vente contiennent cette pièce. Ces clients doivent être contactés.</p>
        </div>
    </div>

    <h3>Clients et Commandes Impactées</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>Commande N°</th>
                    <th>Date de Commande</th>
                    <th>Montant Total (€)</th>
                    <th>Client</th>
                    <th>Contact Téléphone</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventes_impactees as $vente): ?>
                <tr>
                    <td><?= htmlspecialchars($vente['id_commande_vente']) ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($vente['date_commande']))) ?></td>
                    <td><?= number_format($vente['montant_total'], 2, ',', ' ') ?></td>
                    <td><?= htmlspecialchars($vente['nom'] . ' ' . $vente['prenom']) ?></td>
                    <td><?= htmlspecialchars($vente['telephone'] ?? $vente['email']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
