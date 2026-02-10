<?php
$page_title = "Créer un Nouveau Fournisseur";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'] ?? '';
    $pays_origine = $_POST['pays_origine'] ?? '';
    $delai_moyen = $_POST['delai_livraison_moyen'] ?? 0;

    if (!empty($nom) && !empty($pays_origine)) {
        try {
            $query = "INSERT INTO Fournisseurs (Nom, PaysOrigine, DelaiLivraisonMoyen) 
                      VALUES (:nom, :pays, :delai)";
            
            $stmt = $db->prepare($query);
            
            // Nettoyage des données pour la sécurité
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':pays', $pays_origine);
            $stmt->bindParam(':delai', $delai_moyen, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Fournisseur ajouté avec succès. Redirection en cours...</div>";
                // Redirection après 2 secondes pour valider la création
                header("Refresh: 2; URL=index.php");
            } else {
                $message = "<div class='alert alert-danger'>Impossible d'ajouter le fournisseur.</div>";
            }
        } catch(PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur SQL: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Veuillez remplir tous les champs obligatoires.</div>";
    }
}

?>

<h1 class="mt-4"><i class="fas fa-user-plus me-2"></i> Créer un Nouveau Fournisseur</h1>
<p class="text-muted">Renseignez les informations de base du nouveau partenaire logistique.</p>
<hr>

<?= $message ?>

<div class="card shadow-sm p-4">
    <form method="POST" action="creer.php">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom du Fournisseur</label>
                <input type="text" class="form-control" name="nom" required>
            </div>
            <div class="col-md-6">
                <label for="pays" class="form-label">Pays d'Origine</label>
                <input type="text" class="form-control" name="pays_origine" required>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="delai_livraison" class="form-label">Délai Livraison Moyen (Jours)</label>
                <input type="number" class="form-control" name="delai_livraison_moyen" value="15" min="1" required>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-chevron-left me-2"></i> Retour à la Liste</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Enregistrer le Fournisseur</button>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
