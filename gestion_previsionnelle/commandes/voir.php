<?php
$page_title = "Détail de la Commande";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php'; 

$database = new Database();
$db = $database->getConnection();
$commande_id = isset($_GET['id']) ? $_GET['id'] : die('ERREUR: ID de commande non spécifié.');

try {
    // 1. Récupération des infos de base de la commande
    $query_master = "
        SELECT 
            Cmd.CommandeID, 
            C.Nom AS NomClient, 
            Cmd.DateCommande, 
            Cmd.MontantTotal, 
            Cmd.ReferenceFacture,
            Cmd.Statut,
            C.Telephone,
            C.Contact
        FROM Commandes Cmd
        JOIN Clients C ON Cmd.ClientID = C.ClientID
        WHERE Cmd.CommandeID = :id
    ";
    $stmt_master = $db->prepare($query_master);
    $stmt_master->bindParam(':id', $commande_id);
    $stmt_master->execute();
    $commande = $stmt_master->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        die("<div class='alert alert-danger mt-4'>Commande non trouvée.</div>");
    }

    // 2. Récupération des lignes de détail
    $query_details = "
        SELECT 
            P.Nom AS NomProduit, 
            DC.Quantite, 
            DC.PrixVenteUnitaire, 
            DC.CUMP_Au_Moment_Vente,
            (DC.Quantite * DC.PrixVenteUnitaire) AS TotalLigneVente,
            (DC.Quantite * DC.CUMP_Au_Moment_Vente) AS TotalCout,
            ((DC.Quantite * DC.PrixVenteUnitaire) - (DC.Quantite * DC.CUMP_Au_Moment_Vente)) AS MargeLigne
        FROM DetailsCommande DC
        JOIN Produits P ON DC.ProduitID = P.ProduitID
        WHERE DC.CommandeID = :id
    ";
    $stmt_details = $db->prepare($query_details);
    $stmt_details->bindParam(':id', $commande_id);
    $stmt_details->execute();
    $details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div class='alert alert-danger mt-4'>Erreur de récupération des données: " . $e->getMessage() . "</div>");
}

// Calcul des totaux
$total_marge_brute = array_sum(array_column($details, 'MargeLigne'));
$total_cout = array_sum(array_column($details, 'TotalCout'));
?>

<h1 class="mt-4 text-center"><i class="fas fa-file-invoice me-2"></i> Détail de la Commande #<?= htmlspecialchars($commande['CommandeID']) ?></h1>
<p class="text-muted text-center">Facture : **<?= htmlspecialchars($commande['ReferenceFacture'] ?? 'N/A') ?>** | Statut : <span class="badge bg-success"><?= htmlspecialchars($commande['Statut']) ?></span></p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-10">

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">Informations Générales</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Client :</strong> <?= htmlspecialchars($commande['NomClient']) ?></p>
                        <p><strong>Contact :</strong> <?= htmlspecialchars($commande['Contact']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date Commande :</strong> <?= date('d/m/Y', strtotime($commande['DateCommande'])) ?></p>
                        <p><strong>Téléphone :</strong> <?= htmlspecialchars($commande['Telephone']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-lg mb-4 border-0">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="fas fa-boxes me-2"></i> Lignes de Commande & Analyse de Marge
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-custom mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">Prix Vente U. (€)</th>
                                <th class="text-end">CUMP U. (€)</th>
                                <th class="text-end">Total Vente (€)</th>
                                <th class="text-end">Marge Ligne (€)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($details as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['NomProduit']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($item['Quantite']) ?></td>
                                <td class="text-end"><?= number_format($item['PrixVenteUnitaire'], 2, ',', ' ') ?></td>
                                <td class="text-end text-danger"><?= number_format($item['CUMP_Au_Moment_Vente'], 2, ',', ' ') ?></td>
                                <td class="text-end fw-bold"><?= number_format($item['TotalLigneVente'], 2, ',', ' ') ?></td>
                                <td class="text-end text-success fw-bold"><?= number_format($item['MargeLigne'], 2, ',', ' ') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="4" class="text-end">TOTAL COÛT DES VENTES (CDV)</td>
                                <td class="text-end text-danger"><?= number_format($total_cout, 2, ',', ' ') ?></td>
                                <td class="text-end"></td>
                            </tr>
                            <tr class="table-primary fw-bold fs-5">
                                <td colspan="4" class="text-end">TOTAL VENTE HT</td>
                                <td class="text-end"><?= number_format($commande['MontantTotal'], 2, ',', ' ') ?></td>
                                <td class="text-end"></td>
                            </tr>
                             <tr class="table-success fw-bold fs-5">
                                <td colspan="4" class="text-end">MARGE BRUTE TOTALE</td>
                                <td></td>
                                <td class="text-end"><?= number_format($total_marge_brute, 2, ',', ' ') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-chevron-left me-2"></i> Retour à la Liste</a>
            </div>

    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
