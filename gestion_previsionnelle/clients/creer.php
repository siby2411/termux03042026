<?php
$page_title = "Créer un Nouveau Client";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $codepostal = $_POST['codepostal'] ?? '';

    if (!empty($nom) && !empty($contact)) {
        try {
            $query = "INSERT INTO Clients (Nom, Contact, Telephone, Adresse, Ville, CodePostal) 
                      VALUES (:nom, :contact, :telephone, :adresse, :ville, :codepostal)";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':contact', $contact);
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':adresse', $adresse);
            $stmt->bindParam(':ville', $ville);
            $stmt->bindParam(':codepostal', $codepostal);
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Client ajouté avec succès. Redirection en cours...</div>";
                header("Refresh: 2; URL=index.php");
            } else {
                $message = "<div class='alert alert-danger'>Impossible d'ajouter le client.</div>";
            }
        } catch(PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur SQL: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Veuillez remplir les champs obligatoires (Nom et Contact).</div>";
    }
}
?>

<h1 class="mt-4"><i class="fas fa-user-plus me-2"></i> Créer un Nouveau Client</h1>
<p class="text-muted">Enregistrement d'un nouveau partenaire commercial.</p>
<hr>

<?= $message ?>

<div class="card shadow-lg p-4">
    <form method="POST" action="creer.php">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom de l'Entreprise</label>
                <input type="text" class="form-control" name="nom" required>
            </div>
            <div class="col-md-6">
                <label for="contact" class="form-label">Contact Principal</label>
                <input type="text" class="form-control" name="contact" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="tel" class="form-control" name="telephone">
            </div>
            <div class="col-md-6">
                <label for="adresse" class="form-label">Adresse Complète</label>
                <input type="text" class="form-control" name="adresse">
            </div>
        </div>
         <div class="row mb-3">
            <div class="col-md-6">
                <label for="ville" class="form-label">Ville</label>
                <input type="text" class="form-control" name="ville">
            </div>
            <div class="col-md-6">
                <label for="codepostal" class="form-label">Code Postal</label>
                <input type="text" class="form-control" name="codepostal">
            </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-chevron-left me-2"></i> Retour à la Liste</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Enregistrer le Client</button>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
