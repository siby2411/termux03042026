<?php
// /achats/modifier.php
$page_title = "Modifier un Achat";
include_once __DIR__ . '/../config/db.php';

$database = new Database();
$db = $database->getConnection();
$message = '';
$achat_id = isset($_GET['id']) ? $_GET['id'] : die('ERREUR: ID d\'achat non spécifié.');

// --- LOGIQUE DE MODIFICATION (POST) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date_achat = $_POST['date_achat'] ?? '';
    $reference_facture = $_POST['reference_facture'] ?? '';
    
    try {
        $query = "UPDATE Achats SET DateAchat = :date, ReferenceFacture = :ref WHERE AchatID = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':date', $date_achat);
        $stmt->bindParam(':ref', $reference_facture);
        $stmt->bindParam(':id', $achat_id);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Achat #$achat_id mis à jour avec succès (Date/Référence).</div>";
        } else {
            $message = "<div class='alert alert-danger'>Échec de la mise à jour de l'achat.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Erreur SQL: " . $e->getMessage() . "</div>";
    }
}

// --- LOGIQUE DE LECTURE (GET ou après POST) ---
try {
    $query_read = "
        SELECT 
            A.AchatID, A.DateAchat, A.ReferenceFacture, A.Quantite, A.PrixUnitaireAchat, A.MontantTotal,
            P.Nom AS NomProduit, F.Nom AS NomFournisseur
        FROM Achats A
        JOIN Produits P ON A.ProduitID = P.ProduitID
        JOIN Fournisseurs F ON A.FournisseurID = F.FournisseurID
        WHERE A.AchatID = :id
        LIMIT 0,1
    ";
    $stmt_read = $db->prepare($query_read);
    $stmt_read->bindParam(':id', $achat_id);
    $stmt_read->execute();
    $achat = $stmt_read->fetch(PDO::FETCH_ASSOC);

    if (!$achat) {
        die("<div class='alert alert-danger mt-4'>Achat non trouvé.</div>");
    }
} catch (PDOException $e) {
    die("<div class='alert alert-danger mt-4'>Erreur de récupération: " . $e->getMessage() . "</div>");
}

include_once __DIR__ . '/../includes/header.php';
?>

<h1 class="mt-4 text-center"><i class="fas fa-edit me-2"></i> Modifier l'Achat #<?= htmlspecialchars($achat_id) ?></h1>
<p class="text-muted text-center">Modifiez uniquement les informations administratives (Date/Référence Facture). Le Stock et le CUMP ne sont pas recalculés.</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?= $message ?>
        
        <div class="card shadow-lg p-4">
            <form action="modifier.php?id=<?= $achat_id ?>" method="POST">
                <input type="hidden" name="achat_id" value="<?= $achat_id ?>">
                
                <h5 class="mb-3 text-primary"><i class="fas fa-file-invoice me-1"></i> Informations Administratives</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="date_achat" class="form-label">Date de l'Achat <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_achat" name="date_achat" value="<?= htmlspecialchars($achat['DateAchat']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="reference_facture" class="form-label">Référence Facture Fournisseur</label>
                        <input type="text" class="form-control" id="reference_facture" name="reference_facture" value="<?= htmlspecialchars($achat['ReferenceFacture']) ?>">
                    </div>
                </div>

                <h5 class="mb-3 text-secondary"><i class="fas fa-lock me-1"></i> Données de Stock (Non Modifiables)</h5>

                <div class="row mb-3 bg-light p-3 rounded">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Produit :</strong> <span class="fw-bold text-dark"><?= htmlspecialchars($achat['NomProduit']) ?></span></p>
                        <p class="mb-1"><strong>Fournisseur :</strong> <span class="text-muted"><?= htmlspecialchars($achat['NomFournisseur']) ?></span></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-1"><strong>Quantité Achetée :</strong> <span class="fw-bold text-danger"><?= htmlspecialchars($achat['Quantite']) ?></span></p>
                        <p class="mb-1"><strong>Prix U. Achat :</strong> <span class="fw-bold text-danger"><?= number_format($achat['PrixUnitaireAchat'], 2, ',', ' ') ?> €</span></p>
                        <p class="mb-1"><strong>Montant Total :</strong> <span class="fw-bold text-danger"><?= number_format($achat['MontantTotal'], 2, ',', ' ') ?> €</span></p>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-save me-2"></i> Enregistrer les Modifications</button>
                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
