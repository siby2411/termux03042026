<?php
$page_title = "Modifier un Client Existant";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// Récupération de l'ID du client
$id = isset($_GET['id']) ? $_GET['id'] : die('ERREUR: ID de client non spécifié.');

// --- A. GESTION DU FORMULAIRE (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $codepostal = $_POST['codepostal'] ?? '';

    if (!empty($nom) && !empty($contact)) {
        try {
            $query = "UPDATE Clients 
                      SET Nom = :nom, Contact = :contact, Telephone = :telephone, Adresse = :adresse, Ville = :ville, CodePostal = :codepostal 
                      WHERE ClientID = :id";
            $stmt = $db->prepare($query);
            
            // Liaison des valeurs
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':contact', $contact);
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':adresse', $adresse);
            $stmt->bindParam(':ville', $ville);
            $stmt->bindParam(':codepostal', $codepostal);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Client mis à jour avec succès.</div>";
                // Re-charger les données pour afficher la mise à jour
            } else {
                $message = "<div class='alert alert-danger'>Impossible de mettre à jour le client.</div>";
            }
        } catch(PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur SQL: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Veuillez remplir les champs obligatoires (Nom et Contact).</div>";
    }
}

// --- B. RÉCUPÉRATION DES DONNÉES ACTUELLES ---
try {
    $query = "SELECT ClientID, Nom, Contact, Telephone, Adresse, Ville, CodePostal FROM Clients WHERE ClientID = :id LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        die("<div class='alert alert-danger mt-4'>Client non trouvé.</div>");
    }
} catch(PDOException $e) {
    die("<div class='alert alert-danger mt-4'>Erreur de récupération des données: " . $e->getMessage() . "</div>");
}
?>

<?= $message ?>

<div class="card shadow-lg p-4">
    <form method="POST" action="modifier.php?id=<?= htmlspecialchars($id) ?>">
        <h5 class="card-title mb-4">Modification de <?= htmlspecialchars($client['Nom']) ?></h5>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom de l'Entreprise</label>
                <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($client['Nom']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="contact" class="form-label">Contact Principal</label>
                <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($client['Contact']) ?>" required>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="tel" class="form-control" name="telephone" value="<?= htmlspecialchars($client['Telephone']) ?>">
            </div>
            <div class="col-md-6">
                <label for="adresse" class="form-label">Adresse Complète</label>
                <input type="text" class="form-control" name="adresse" value="<?= htmlspecialchars($client['Adresse']) ?>">
            </div>
        </div>
        
         <div class="row mb-3">
            <div class="col-md-6">
                <label for="ville" class="form-label">Ville</label>
                <input type="text" class="form-control" name="ville" value="<?= htmlspecialchars($client['Ville']) ?>">
            </div>
            <div class="col-md-6">
                <label for="codepostal" class="form-label">Code Postal</label>
                <input type="text" class="form-control" name="codepostal" value="<?= htmlspecialchars($client['CodePostal']) ?>">
            </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-chevron-left me-2"></i> Retour à la Liste</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Enregistrer les Modifications</button>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
