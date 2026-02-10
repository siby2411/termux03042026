<?php
// /var/www/piece_auto/public/modules/gestion_stock.php - (MISE À JOUR CUMP & VALORISATION)

$page_title = "Gestion et Valorisation du Stock";
include '../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// --- Récupération de la liste des pièces avec valorisation CUMP ---
$query_pieces = "SELECT 
    p.id_piece, p.nom_piece, p.reference, p.quantite_stock, p.prix_vente, 
    p.stock_minimum, p.stock_securite, p.cump_actuel, p.valeur_stock_total, 
    c.nom_categorie, m.nom_marque
FROM PIECES p
JOIN CATEGORIES c ON p.id_categorie = c.id_categorie
JOIN MARQUES m ON p.id_marque = m.id_marque
ORDER BY p.nom_piece";

$stmt_pieces = $db->prepare($query_pieces);
$stmt_pieces->execute();
$pieces = $stmt_pieces->fetchAll(PDO::FETCH_ASSOC);

// Calcul des totaux pour le tableau de bord
$valeur_stock_globale = array_sum(array_column($pieces, 'valeur_stock_total'));

?>

<h1><i class="fas fa-warehouse"></i> <?= $page_title ?></h1>
<p class="lead">Visualisation de l'état du stock, des seuils d'alerte et de la valorisation actuelle.</p>
<hr>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Valeur Stock Totale (CUMP)</h5>
                <p class="card-text fs-3"><?= number_format($valeur_stock_globale, 2) ?> €</p>
                <small>Somme des (CUMP x Qté Stock) de toutes les pièces.</small>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Audit CUMP</h5>
                <p class="card-text">Vérifiez l'historique des modifications du coût de revient.</p>
                <a href="historique_cump.php" class="btn btn-outline-dark">
                    <i class="fas fa-history"></i> Voir l'Historique CUMP
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (count($pieces) > 0): ?>
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Nom Pièce</th>
                <th>Marque</th>
                <th class="text-center">Stock Actuel</th>
                <th class="text-center">Seuil Min.</th>
                <th class="text-end">CUMP Actuel (€)</th>
                <th class="text-end">Prix Vente (€)</th>
                <th class="text-end">Valorisation Totale (€)</th>
                <th>Statut Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pieces as $p): 
                $quantite = (int)$p['quantite_stock'];
                $min = (int)$p['stock_minimum'];
                $sec = (int)$p['stock_securite'];

                if ($quantite <= $min) {
                    $statut_class = 'danger';
                    $statut_texte = 'Urgence: Sous Seuil Min.';
                } elseif ($quantite <= $sec) {
                    $statut_class = 'warning';
                    $statut_texte = 'Alerte: Sous Seuil Sécurité';
                } else {
                    $statut_class = 'success';
                    $statut_texte = 'OK';
                }
            ?>
                <tr>
                    <td><?= htmlspecialchars($p['reference']) ?></td>
                    <td><?= htmlspecialchars($p['nom_piece']) ?></td>
                    <td><?= htmlspecialchars($p['nom_marque']) ?></td>
                    <td class="text-center fw-bold"><?= $quantite ?></td>
                    <td class="text-center text-danger"><?= $min ?></td>
                    
                    <td class="text-end fw-bold text-info"><?= number_format($p['cump_actuel'], 2) ?></td>
                    <td class="text-end"><?= number_format($p['prix_vente'], 2) ?></td>
                    <td class="text-end fw-bold text-primary"><?= number_format($p['valeur_stock_total'], 2) ?></td>
                    
                    <td>
                        <span class="badge bg-<?= $statut_class ?>">
                            <?= $statut_texte ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">Aucune pièce n'est actuellement en stock.</div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
