<?php
// /achats/index.php
$page_title = "Historique des Achats";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php'; 

$database = new Database();
$db = $database->getConnection();

$query = "
    SELECT 
        A.AchatID, 
        A.DateAchat, 
        A.ReferenceFacture,
        P.Nom AS NomProduit, 
        F.Nom AS NomFournisseur, 
        A.Quantite, 
        A.PrixUnitaireAchat, 
        A.MontantTotal
    FROM Achats A
    JOIN Produits P ON A.ProduitID = P.ProduitID
    JOIN Fournisseurs F ON A.FournisseurID = F.FournisseurID
    ORDER BY A.DateAchat DESC
";

try {
    $stmt = $db->query($query);
    $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur SQL: Impossible de charger l'historique des achats. " . $e->getMessage() . "</div>";
    $achats = [];
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-history me-2"></i> Historique des Achats</h1>
<p class="text-muted text-center">Liste de toutes les entrées de stock enregistrées.</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-12">

        <div class="d-flex justify-content-end mb-3">
            <a href="creer.php" class="btn btn-success"><i class="fas fa-plus me-2"></i> Enregistrer un Nouvel Achat</a>
        </div>

        <div class="card shadow-lg mb-4 border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-custom mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Date</th>
                                <th>Réf. Facture</th>
                                <th>Produit</th>
                                <th>Fournisseur</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">Prix U. Achat (€)</th>
                                <th class="text-end">Montant Total (€)</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($achats)): ?>
                            <?php foreach ($achats as $achat): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($achat['DateAchat'])) ?></td>
                                <td><?= htmlspecialchars($achat['ReferenceFacture']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($achat['NomProduit']) ?></td>
                                <td><?= htmlspecialchars($achat['NomFournisseur']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($achat['Quantite']) ?></td>
                                <td class="text-end"><?= number_format($achat['PrixUnitaireAchat'], 2, ',', ' ') ?></td>
                                <td class="text-end fw-bold"><?= number_format($achat['MontantTotal'], 2, ',', ' ') ?></td>
                                <td class="text-center">



<td class="text-center">
        <?php 
        // TEMPORAIRE : Affiche l'ID pour le débogage. Retirez après correction.
        echo "ID: " . $achat['AchatID']; 
        ?>
        <a href="modifier.php?id=<?= $achat['AchatID'] ?>" class="btn btn-sm btn-outline-warning" title="Modifier">
            <i class="fas fa-edit"></i>
        </a>
    </td>



   <a href="modifier.php?id=<?= $fourn['AchatID'] ?>" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i></a>


                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted p-4">Aucun achat enregistré.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
