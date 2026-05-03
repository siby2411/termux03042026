<?php
// /audit_stock_dormant.php
$page_title = "Audit du Stock Dormant & Optimisation BFR";
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Paramètres d'alerte
$seuil_jours_dormance = 90; // Produit considéré "dormant" si aucune vente depuis 3 mois

try {
    // Requête complexe pour croiser Stock Actuel et dernières ventes
    $query_audit = "
        SELECT 
            P.ProduitID,
            P.Nom,
            P.StockActuel,
            P.CUMP,
            (P.StockActuel * P.CUMP) AS ValeurStock,
            MAX(C.DateCommande) AS DateDerniereVente,
            DATEDIFF(NOW(), MAX(C.DateCommande)) AS JoursSansVente,
            COALESCE(SUM(DC.Quantite), 0) AS UnitesVendues12Mois
        FROM Produits P
        LEFT JOIN DetailsCommande DC ON P.ProduitID = DC.ProduitID
        LEFT JOIN Commandes C ON DC.CommandeID = C.CommandeID AND C.Statut = 'LIVREE'
        WHERE P.StockActuel > 0
        GROUP BY P.ProduitID
        ORDER BY ValeurStock DESC
    ";
    
    $stmt = $db->query($query_audit);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrégats pour le rapport
    $total_immobilise = 0;
    $total_dormant = 0;
    $liste_critique = [];

    foreach ($produits as $p) {
        $total_immobilise += $p['ValeurStock'];
        if ($p['JoursSansVente'] > $seuil_jours_dormance || $p['DateDerniereVente'] === null) {
            $total_dormant += $p['ValeurStock'];
            $liste_critique[] = $p;
        }
    }

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur d'audit : " . $e->getMessage() . "</div>";
}
?>

<div class="container-fluid mt-4">
    <h1 class="text-center text-danger"><i class="fas fa-exclamation-triangle me-2"></i> Audit du Stock Dormant</h1>
    <p class="text-muted text-center">Identification des actifs immobilisés impactant négativement le BFR.</p>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-dark text-white shadow">
                <div class="card-body text-center">
                    <h5>Valeur Totale Stock</h5>
                    <h2 class="display-6"><?= number_format($total_immobilise, 2, ',', ' ') ?> €</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow">
                <div class="card-body text-center">
                    <h5>Cash "Dormant" (> 90j)</h5>
                    <h2 class="display-6"><?= number_format($total_dormant, 2, ',', ' ') ?> €</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark shadow">
                <div class="card-body text-center">
                    <h5>Poids du Dormant / Total</h5>
                    <h2 class="display-6"><?= ($total_immobilise > 0) ? round(($total_dormant / $total_immobilise) * 100, 1) : 0 ?> %</h2>
                </div>
            </div>
        </div>
    </div>

    

    <div class="card shadow mt-5 border-danger">
        <div class="card-header bg-danger text-white fw-bold">
            <i class="fas fa-list me-2"></i> Top des Produits Critiques (Rétention de Cash)
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end">Valeur (€)</th>
                        <th class="text-center">Dernière Vente</th>
                        <th class="text-center">Statut</th>
                        <th>Action BFR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($liste_critique as $item): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($item['Nom']) ?></td>
                        <td class="text-end"><?= $item['StockActuel'] ?></td>
                        <td class="text-end fw-bold text-danger"><?= number_format($item['ValeurStock'], 2, ',', ' ') ?></td>
                        <td class="text-center">
                            <?= $item['DateDerniereVente'] ? date('d/m/Y', strtotime($item['DateDerniereVente'])) : '<span class="badge bg-secondary">Jamais vendu</span>' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger"><?= $item['JoursSansVente'] ?? '∞' ?> jours d'inactivité</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Déstocker / Promo</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info mt-4 shadow-sm">
        <h5><i class="fas fa-lightbulb me-2"></i> Recommandation d'Ingénierie Financière</h5>
        <p class="mb-0">Chaque euro récupéré ici réduit mécaniquement votre **Besoin en Fonds de Roulement (BFR)** et augmente votre **Trésorerie Nette** sans avoir recours à l'emprunt.</p>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
