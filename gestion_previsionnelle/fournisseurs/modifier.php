<?php
$page_title = "Modifier un Fournisseur";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

$id = $_GET['id'] ?? die("<div class='alert alert-danger'>ID de fournisseur manquant.</div>");
$fournisseur = null;

// --- 1. Logique de MISE À JOUR (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'] ?? '';
    $pays_origine = $_POST['pays_origine'] ?? '';
    $delai_moyen = $_POST['delai_livraison_moyen'] ?? 0;

    try {
        $query = "UPDATE Fournisseurs 
                  SET Nom = :nom, PaysOrigine = :pays, DelaiLivraisonMoyen = :delai 
                  WHERE FournisseurID = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':pays', $pays_origine);
        $stmt->bindParam(':delai', $delai_moyen, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Fournisseur mis à jour avec succès.</div>";
            // Recharger les données pour que le formulaire affiche la mise à jour immédiate
        } else {
            $message = "<div class='alert alert-danger'>Impossible de mettre à jour le fournisseur.</div>";
        }
    } catch(PDOException $e) {
        $message = "<div class='alert alert-danger'>Erreur SQL: " . $e->getMessage() . "</div>";
    }
}

// --- 2. Logique de LECTURE (GET) ---
try {
    $query = "SELECT FournisseurID, Nom, PaysOrigine, DelaiLivraisonMoyen 
              FROM Fournisseurs 
              WHERE FournisseurID = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fournisseur) {
        die("<div class='alert alert-danger'>Fournisseur non trouvé.</div>");
    }
} catch(PDOException $e) {
    die("<div class='alert alert-danger'>Erreur de lecture: " . $e->getMessage() . "</div>");
}
?>

<h1 class="mt-4"><i class="fas fa-edit me-2"></i> Modifier : <?= htmlspecialchars($fournisseur['Nom']) ?></h1>
<p class="text-muted">Mettez à jour les informations du fournisseur.</p>
<hr>

<?= $message ?>

<div class="card shadow-sm p-4">
    <form method="POST" action="modifier.php?id=<?= $id ?>">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom du Fournisseur</label>
                <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($fournisseur['Nom']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="pays" class="form-label">Pays d'Origine</label>
                <input type="text" class="form-control" name="pays_origine" value="<?= htmlspecialchars($fournisseur['PaysOrigine']) ?>" required>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="delai_livraison" class="form-label">Délai Livraison Moyen (Jours)</label>
                <input type="number" class="form-control" name="delai_livraison_moyen" value="<?= htmlspecialchars($fournisseur['DelaiLivraisonMoyen']) ?>" min="1" required>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-chevron-left me-2"></i> Retour à la Liste</a>
            <button type="submit" class="btn btn-info"><i class="fas fa-sync me-2"></i> Sauvegarder les Modifications</button>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?><?php 
// Logique de modification fournisseur (similaire à clients/modifier.php)
// ...
