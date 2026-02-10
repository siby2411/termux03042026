<?php
// Fichier : tableau_stock.php - Audit et Suivi de Stock

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();

// 1. Récupérer le stock actuel de TOUS les produits
$stocks = [];
$result_stocks = $conn->query("SELECT id_produit, code_produit, designation, stock_actuel FROM produits ORDER BY designation ASC");
if ($result_stocks) {
    while ($row = $result_stocks->fetch_assoc()) {
        $stocks[$row['id_produit']] = $row;
        // Initialiser les totaux de mouvement
        $stocks[$row['id_produit']]['total_appro'] = 0;
        $stocks[$row['id_produit']]['total_vente'] = 0;
    }
}

// 2. Calculer le total des approvisionnements (Entrées)
$result_appro = $conn->query("SELECT id_produit, SUM(quantite_entree) as total_appro FROM approvisionnements GROUP BY id_produit");
if ($result_appro) {
    while ($row = $result_appro->fetch_assoc()) {
        if (isset($stocks[$row['id_produit']])) {
            $stocks[$row['id_produit']]['total_appro'] = $row['total_appro'];
        }
    }
}

// 3. Calculer le total des Ventes/BL (Sorties)
$sql_vente = "SELECT df.id_produit, SUM(df.quantite) as total_vente
              FROM details_facture df
              JOIN factures f ON df.id_facture = f.id_facture
              WHERE f.type_document IN ('FACTURE', 'BL')
              GROUP BY df.id_produit";

$result_vente = $conn->query($sql_vente);
if ($result_vente) {
    while ($row = $result_vente->fetch_assoc()) {
        if (isset($stocks[$row['id_produit']])) {
            $stocks[$row['id_produit']]['total_vente'] = $row['total_vente'];
        }
    }
}

$conn->close();

// 1. INCLUSION DU HEADER
include 'header.php';
?>

<h1 class="mb-4 text-dark">📈 Tableau de Bord d'Inventaire et Suivi de Stocks</h1>
<p><a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour au Tableau de Bord</a></p>

<div class="alert alert-info mt-3" role="alert">
    Ce tableau compare le **Stock Actuel (Réel DB)** avec le **Stock Théorique (calculé)** pour identifier les écarts et les alertes de rupture.
</div>

<?php if (count($stocks) > 0): ?>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle shadow-sm">
        <thead class="table-dark">
            <tr>
                <th class="text-center">Code</th>
                <th>Désignation</th>
                <th class="text-center">Total Appro (Entrées)</th>
                <th class="text-center">Total Ventes/BL (Sorties)</th>
                <th class="text-center text-info">Stock Théorique (Appro - Vente)</th>
                <th class="text-center text-warning">Stock Actuel (Réel DB)</th>
                <th class="text-center">Écart (Réel - Théorique)</th>
                <th class="text-center">Statut Alerte</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stocks as $s): ?>
            <?php 
                $stock_theorique = $s['total_appro'] - $s['total_vente'];
                $difference = $s['stock_actuel'] - $stock_theorique;
                
                // Définition du statut et des classes Bootstrap
                $class_alerte = '';
                $statut_texte = 'OK';
                
                if ($s['stock_actuel'] <= 0) {
                    $class_alerte = 'table-danger';
                    $statut_texte = 'RUPTURE IMMINENTE';
                } elseif ($s['stock_actuel'] <= 5) {
                    $class_alerte = 'table-warning';
                    $statut_texte = 'Stock Faible';
                }
                
                // Alerte si écart entre réel et théorique
                if ($difference !== 0) {
                    $class_ecart = 'table-danger';
                } else {
                    $class_ecart = '';
                }
            ?>
            <tr class="<?php echo $class_alerte; ?>">
                <td class="text-center"><?php echo htmlspecialchars($s['code_produit']); ?></td>
                <td class="fw-bold"><?php echo htmlspecialchars($s['designation']); ?></td>
                <td class="text-center"><?php echo $s['total_appro']; ?></td>
                <td class="text-center"><?php echo $s['total_vente']; ?></td>
                
                <td class="text-center text-info fw-bold bg-light">
                    <?php echo $stock_theorique; ?>
                </td>
                
                <td class="text-center text-warning fw-bolder fs-5">
                    <?php echo $s['stock_actuel']; ?>
                </td>
                
                <td class="text-center <?php echo $class_ecart; ?>">
                    <?php if ($difference !== 0): ?>
                        <span class="badge bg-danger">ÉCART: <?php echo $difference; ?></span>
                    <?php else: ?>
                        <span class="badge bg-success">0</span>
                    <?php endif; ?>
                </td>
                
                <td class="text-center">
                    <?php if ($statut_texte === 'RUPTURE IMMINENTE'): ?>
                        <span class="badge bg-danger fs-6"><?php echo $statut_texte; ?></span>
                    <?php elseif ($statut_texte === 'Stock Faible'): ?>
                        <span class="badge bg-warning text-dark"><?php echo $statut_texte; ?></span>
                    <?php else: ?>
                        <span class="badge bg-success">OK</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>
    <div class="alert alert-warning">
        Aucun produit n'est enregistré dans l'inventaire.
    </div>
<?php endif; ?>

<?php
// 2. INCLUSION DU FOOTER
include 'footer.php';
?>
