<?php
// /var/www/piece_auto/modules/analyse_stock_mouvements.php
include_once '../config/Database.php';
include_once '../includes/auth_check.php'; 
include '../includes/header.php';

$page_title = "Analyse des Mouvements de Stock";
$database = new Database();
$db = $database->getConnection();

// --- 1. FONCTIONNALITÉ D'ENREGISTREMENT DE MOUVEMENTS (À intégrer dans les autres modules) ---
// Cette fonction devra être appelée lors d'une vente (quantité négative) ou d'un achat/inventaire (quantité positive)
function log_mouvement($db, $id_piece, $quantite, $type, $ref_externe = null) {
    $query = "INSERT INTO MOUVEMENTS_STOCK (id_piece, date_mouvement, quantite_change, type_mouvement, reference_externe) 
              VALUES (:id_p, NOW(), :qte, :type, :ref_ext)";
    $stmt = $db->prepare($query);
    return $stmt->execute([
        ':id_p' => $id_piece, 
        ':qte' => $quantite, 
        ':type' => $type, 
        ':ref_ext' => $ref_externe
    ]);
}

// --- 2. RÉCUPÉRATION DES 100 DERNIERS MOUVEMENTS ---
$query_mouvements = "
    SELECT 
        MS.date_mouvement, MS.quantite_change, MS.type_mouvement, MS.reference_externe,
        P.reference_sku, P.nom_piece
    FROM MOUVEMENTS_STOCK MS
    JOIN PIECES P ON MS.id_piece = P.id_piece
    ORDER BY MS.date_mouvement DESC
    LIMIT 100";

$stmt_mouvements = $db->query($query_mouvements);
$mouvements = $stmt_mouvements->fetchAll(PDO::FETCH_ASSOC);

function format_qte($qte) {
    $class = $qte > 0 ? 'text-success' : ($qte < 0 ? 'text-danger' : 'text-muted');
    return "<span class='$class'>" . number_format($qte) . "</span>";
}
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-history"></i> Historique des Mouvements de Stock</h2>
        <p class="text-muted">Analyse des entrées (Achats) et des sorties (Ventes) pour la prise de décision stratégique.</p>

        <div class="card p-4 shadow">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Référence Pièce</th>
                            <th>Désignation</th>
                            <th class="text-end">Quantité</th>
                            <th>Type de Mouvement</th>
                            <th>Réf. Externe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mouvements)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucun mouvement de stock enregistré. (Les logiques d'enregistrement doivent être implémentées dans les modules de Vente et d'Achat).</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mouvements as $mouv): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($mouv['date_mouvement'])) ?></td>
                                    <td><?= htmlspecialchars($mouv['reference_sku']) ?></td>
                                    <td><?= htmlspecialchars($mouv['nom_piece']) ?></td>
                                    <td class="text-end"><?= format_qte($mouv['quantite_change']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $mouv['quantite_change'] > 0 ? 'success' : 'danger' ?>"><?= htmlspecialchars($mouv['type_mouvement']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($mouv['reference_externe'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>
