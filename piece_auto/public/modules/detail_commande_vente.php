<?php
// /var/www/piece_auto/public/modules/detail_commande_vente.php
$page_title = "Détail de la Commande";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$commande = null;
$lignes = [];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_commande = $_GET['id'];

    try {
        // Correction des colonnes : nom, prenom, telephone, email
        $query_info = "SELECT cv.*, c.nom, c.prenom, c.telephone, c.email 
                       FROM COMMANDE_VENTE cv
                       JOIN CLIENTS c ON cv.id_client = c.id_client
                       WHERE cv.id_commande_vente = :id";
        $stmt_info = $db->prepare($query_info);
        $stmt_info->execute([':id' => $id_commande]);
        $commande = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($commande) {
            $query_details = "SELECT dv.*, p.reference, p.nom_piece 
                              FROM DETAIL_VENTE dv
                              JOIN PIECES p ON dv.id_piece = p.id_piece
                              WHERE dv.id_commande_vente = :id";
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
        <i class="fas fa-exclamation-triangle"></i> Commande introuvable.
        <br><a href="gestion_commandes_vente.php" class="btn btn-sm btn-secondary mt-2">Retour</a>
    </div>
<?php else: ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-invoice"></i> Commande #<?= $commande['id_commande_vente'] ?></h1>
        <div class="no-print">
            <a href="gestion_commandes_vente.php" class="btn btn-secondary btn-sm">Retour</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print"></i> Imprimer</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">Client</div>
                <div class="card-body">
                    <h5 class="mb-1"><?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']) ?></h5>
                    <p class="mb-1 text-muted"><i class="fas fa-phone"></i> <?= htmlspecialchars($commande['telephone'] ?? 'N/A') ?></p>
                    <p class="text-muted"><i class="fas fa-envelope"></i> <?= htmlspecialchars($commande['email'] ?? 'N/A') ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Articles</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Référence</th>
                                <th>Désignation</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lignes as $l): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($l['reference']) ?></strong></td>
                                <td><?= htmlspecialchars($l['nom_piece']) ?></td>
                                <td class="text-center"><?= $l['quantite_vendue'] ?></td>
                                <td class="text-end fw-bold"><?= number_format($l['prix_vente_unitaire'] * $l['quantite_vendue'], 2, ',', ' ') ?> €</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">TOTAL COMMANDE :</td>
                                <td class="text-end fw-bold text-primary"><?= number_format($commande['total_commande'], 2, ',', ' ') ?> €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; 
include '../../includes/footer.php'; ?>
