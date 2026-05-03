<?php
$page_title = "Détails de la Commande";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? die("<div class='alert alert-danger'>ID de commande manquant.</div>");

// --- 1. Récupérer les informations de la Commande Master ---
$query_master = "
    SELECT 
        c.CommandeID, c.DateCommande, c.MontantTotal, c.Statut, 
        cl.Nom AS NomClient, cl.ClientID
    FROM Commandes c
    JOIN Clients cl ON c.ClientID = cl.ClientID
    WHERE c.CommandeID = ?
";
$stmt_master = $db->prepare($query_master);
$stmt_master->bindParam(1, $id);
$stmt_master->execute();
$commande = $stmt_master->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    die("<div class='alert alert-danger'>Commande non trouvée.</div>");
}

// --- 2. Récupérer les Détails de la Commande ---
$query_details = "
    SELECT 
        d.Quantite, d.PrixUnitaireVente, 
        p.Nom AS NomProduit, p.CUMP, p.StockActuel
    FROM DetailsCommande d
    JOIN Produits p ON d.ProduitID = p.ProduitID
    WHERE d.CommandeID = ?
";
$stmt_details = $db->prepare($query_details);
$stmt_details->bindParam(1, $id);
$stmt_details->execute();
$details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

?>

<h1 class="mt-4"><i class="fas fa-search me-2"></i> Détails de la Commande #<?= htmlspecialchars($commande['CommandeID']) ?></h1>
<p class="text-muted">Client : **<?= htmlspecialchars($commande['NomClient']) ?>** | Statut : <span class="badge bg-<?= $commande['Statut'] == 'EN_COURS' ? 'warning' : 'success' ?>"><?= htmlspecialchars($commande['Statut']) ?></span></p>
<hr>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm h-100 bg-light">
            <div class="card-header bg-primary text-white">Résumé Financier</div>
            <div class="card-body">
                <p><strong>Date de Commande :</strong> <?= htmlspecialchars($commande['DateCommande']) ?></p>
                <h3 class="text-primary mt-3">Total Vente : <?= number_format($commande['MontantTotal'], 2, ',', ' ') ?> EUR</h3>
                
                <?php 
                $cout_marchandise_total = 0;
                foreach ($details as $detail) {
                    // Calcul du coût réel (CdMV) basé sur le CUMP actuel
                    $cout_marchandise_total += $detail['Quantite'] * $detail['CUMP'];
                }
                $marge_brute = $commande['MontantTotal'] - $cout_marchandise_total;
                $marge_pct = ($commande['MontantTotal'] > 0) ? ($marge_brute / $commande['MontantTotal']) * 100 : 0;
                ?>
                
                <p class="mt-4">Coût des Marchandises Vendues (estimé) : <?= number_format($cout_marchandise_total, 2, ',', ' ') ?> EUR</p>
                <p class="fs-5 text-<?= $marge_brute > 0 ? 'success' : 'danger' ?>">Marge Brute : <?= number_format($marge_brute, 2, ',', ' ') ?> EUR (<?= number_format($marge_pct, 2) ?> %)</p>

                <div class="mt-4 d-flex justify-content-end">
                    <a href="modifier.php?id=<?= $commande['CommandeID'] ?>" class="btn btn-info me-2"><i class="fas fa-edit me-2"></i> Modifier</a>
                    <button class="btn btn-success"><i class="fas fa-file-invoice-dollar me-2"></i> Finaliser/Facturer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-secondary text-white">Lignes de la Commande</div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Qté</th>
                            <th>Prix Unitaire</th>
                            <th>Coût Unitaire (CUMP)</th>
                            <th>Total Ligne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $detail): ?>
                        <tr>
                            <td><?= htmlspecialchars($detail['NomProduit']) ?></td>
                            <td><?= htmlspecialchars($detail['Quantite']) ?></td>
                            <td><?= number_format($detail['PrixUnitaireVente'], 2, ',', ' ') ?></td>
                            <td><?= number_format($detail['CUMP'], 2, ',', ' ') ?></td>
                            <td><?= number_format($detail['Quantite'] * $detail['PrixUnitaireVente'], 2, ',', ' ') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
