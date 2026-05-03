<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../includes/auth_check.php';
// /var/www/piece_auto/public/modules/detail_commande_achat.php
$page_title = "Détail Commande Fournisseur";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$commande = null;
$lignes = [];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_commande = $_GET['id'];

    try {
        // 1. Récupération de l'entête de commande et infos fournisseur
        $query_info = "SELECT ca.*, f.nom_fournisseur, f.telephone, f.email 
                       FROM COMMANDES_ACHAT ca
                       JOIN FOURNISSEURS f ON ca.id_fournisseur = f.id_fournisseur
                       WHERE ca.id_commande_achat = :id";
        $stmt_info = $db->prepare($query_info);
        $stmt_info->execute([':id' => $id_commande]);
        $commande = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($commande) {
            // 2. Récupération des articles commandés
            $query_details = "SELECT lca.*, p.reference, p.nom_piece 
                              FROM LIGNES_COMMANDE_ACHAT lca
                              JOIN PIECES p ON lca.id_piece = p.id_piece
                              WHERE lca.id_commande_achat = :id";
            $stmt_details = $db->prepare($query_details);
            $stmt_details->execute([':id' => $id_commande]);
            $lignes = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Erreur SQL : ' . $e->getMessage() . '</div>';
    }
}

if (!$commande): ?>
    <div class="alert alert-warning mt-4">
        <i class="fas fa-exclamation-triangle"></i> Commande fournisseur introuvable.
        <br><a href="gestion_commandes_achat.php" class="btn btn-sm btn-secondary mt-2">Retour à la liste</a>
    </div>
<?php else: ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-contract"></i> Commande Achat #<?= $commande['id_commande_achat'] ?></h1>
        <div class="no-print">
            <a href="gestion_commandes_achat.php" class="btn btn-secondary btn-sm">Retour</a>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="fas fa-print"></i> Imprimer BC</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4 border-left-primary">
                <div class="card-header bg-dark text-white">Fournisseur</div>
                <div class="card-body">
                    <h5 class="text-primary"><?= htmlspecialchars($commande['nom_fournisseur']) ?></h5>
                    <p class="mb-1 small"><i class="fas fa-phone"></i> <?= htmlspecialchars($commande['telephone'] ?? 'N/A') ?></p>
                    <p class="mb-1 small"><i class="fas fa-envelope"></i> <?= htmlspecialchars($commande['email'] ?? 'N/A') ?></p>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Statut :</span>
                        <span class="badge bg-<?= $commande['statut'] == 'Reçue' ? 'success' : 'warning' ?>">
                            <?= $commande['statut'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Articles en commande</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Réf</th>
                                <th>Désignation</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">Prix Achat HT</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_general = 0;
                            foreach ($lignes as $l): 
                                $total_ligne = $l['quantite_commandee'] * $l['prix_achat_unitaire'];
                                $total_general += $total_ligne;
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($l['reference']) ?></strong></td>
                                <td><?= htmlspecialchars($l['nom_piece']) ?></td>
                                <td class="text-center"><?= $l['quantite_commandee'] ?></td>
                                <td class="text-end"><?= number_format($l['prix_achat_unitaire'], 2, ',', ' ') ?> €</td>
                                <td class="text-end fw-bold"><?= number_format($total_ligne, 2, ',', ' ') ?> €</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-warning">
                                <td colspan="4" class="text-end"><strong>MONTANT TOTAL COMMANDE :</strong></td>
                                <td class="text-end fw-bold"><?= number_format($total_general, 2, ',', ' ') ?> €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; 
include '../../includes/footer.php'; ?>
