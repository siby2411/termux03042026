<?php
// /var/www/piece_auto/public/modules/historique_cump.php - Module d'Audit CUMP

$page_title = "Historique et Audit du Coût Unitaire Moyen Pondéré (CUMP)";
include '../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// --- 1. Récupération des données d'historique ---
// Requête pour joindre HISTORIQUE_CUMP avec PIECES (pour le nom) et ordonner par date
$query_history = "SELECT 
    hc.date_changement, 
    p.nom_piece,
    p.reference,
    hc.ancien_cump, 
    hc.nouveau_cump, 
    hc.quantite_entree, 
    hc.source_type,
    hc.source_id,
    (hc.nouveau_cump - hc.ancien_cump) AS ecart_cump
FROM HISTORIQUE_CUMP hc
JOIN PIECES p ON hc.piece_id = p.id_piece
ORDER BY hc.date_changement DESC";

$stmt_history = $db->prepare($query_history);
$stmt_history->execute();
$historique = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

?>

<h1><i class="fas fa-history"></i> <?= $page_title ?></h1>
<p class="lead">Traçabilité complète des événements ayant modifié la valorisation du stock (CUMP).</p>
<hr>

<?php if (count($historique) > 0): ?>
    
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Date & Heure</th>
                    <th>Référence Pièce</th>
                    <th>Nom Pièce</th>
                    <th class="text-center">Qté Entrée</th>
                    <th class="text-end">Ancien CUMP (€)</th>
                    <th class="text-end">Nouveau CUMP (€)</th>
                    <th class="text-center">Écart (€)</th>
                    <th>Source Événement</th>
                    <th>ID Source</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historique as $h): 
                    // Déterminer la classe de l'écart
                    $ecart = (float)$h['ecart_cump'];
                    if ($ecart > 0) {
                        $ecart_class = 'badge bg-danger';
                        $ecart_signe = '+';
                    } elseif ($ecart < 0) {
                        $ecart_class = 'badge bg-success';
                        $ecart_signe = '';
                    } else {
                        $ecart_class = 'badge bg-secondary';
                        $ecart_signe = '';
                    }

                    // Formater le type de source
                    $source_badge_class = 'badge bg-';
                    switch ($h['source_type']) {
                        case 'ACHAT':
                            $source_badge_class .= 'primary';
                            break;
                        case 'INITIAL':
                            $source_badge_class .= 'info';
                            break;
                        case 'AJUSTEMENT':
                            $source_badge_class .= 'warning';
                            break;
                        default:
                            $source_badge_class .= 'secondary';
                    }
                ?>
                    <tr>
                        <td><?= htmlspecialchars($h['date_changement']) ?></td>
                        <td><?= htmlspecialchars($h['reference']) ?></td>
                        <td><?= htmlspecialchars($h['nom_piece']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($h['quantite_entree']) ?></td>
                        <td class="text-end"><?= number_format($h['ancien_cump'], 2) ?></td>
                        <td class="text-end fw-bold"><?= number_format($h['nouveau_cump'], 2) ?></td>
                        <td class="text-center">
                            <span class="<?= $ecart_class ?>">
                                <?= $ecart_signe . number_format($ecart, 2) ?>
                            </span>
                        </td>
                        <td><span class="<?= $source_badge_class ?>"><?= htmlspecialchars($h['source_type']) ?></span></td>
                        <td>
                            <?php if ($h['source_type'] == 'ACHAT'): ?>
                                <a href="reception_commande_achat.php?id=<?= $h['source_id'] ?>" class="btn btn-sm btn-outline-secondary" title="Voir la commande">
                                    #<?= htmlspecialchars($h['source_id']) ?> <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($h['source_id']) ?? 'N/A' ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php 
    // Rappel de la formule de calcul pour la vérification
    echo '<div class="alert alert-info mt-4">
        <strong>Rappel du calcul du CUMP :</strong>
        <p>$$
        \\text{CUMP}_{\\text{nouveau}} = \\frac{(\\text{Stock}_{\\text{ancien}} \\times \\text{CUMP}_{\\text{ancien}}) + (\\text{Quantité}_{\\text{reçue}} \\times \\text{Prix}_{\\text{achat}})}{\\text{Stock}_{\\text{ancien}} + \\text{Quantité}_{\\text{reçue}}}
        $$</p>
    </div>';
    ?>

<?php else: ?>
    <div class="alert alert-info">Aucun événement n'a encore affecté le Coût Unitaire Moyen Pondéré.</div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
