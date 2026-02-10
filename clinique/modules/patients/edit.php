<?php
include '../../includes/header.php';
include '../../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: list.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $query = "UPDATE patients SET civilite = :civilite, nom = :nom, prenom = :prenom, date_naissance = :date_naissance, 
                  genre = :genre, adresse = :adresse, telephone = :telephone, email = :email, 
                  personne_urgence_nom = :personne_urgence_nom, personne_urgence_tel = :personne_urgence_tel 
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':civilite', $_POST['civilite']);
        $stmt->bindParam(':nom', $_POST['nom']);
        $stmt->bindParam(':prenom', $_POST['prenom']);
        $stmt->bindParam(':date_naissance', $_POST['date_naissance']);
        $stmt->bindParam(':genre', $_POST['genre']);
        $stmt->bindParam(':adresse', $_POST['adresse']);
        $stmt->bindParam(':telephone', $_POST['telephone']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':personne_urgence_nom', $_POST['personne_urgence_nom']);
        $stmt->bindParam(':personne_urgence_tel', $_POST['personne_urgence_tel']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            header("Location: list.php?success=Patient modifié avec succès");
            exit();
        } else {
            $error = "Erreur lors de la modification du patient";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupérer les données actuelles
try {
    $query = "SELECT * FROM patients WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        header("Location: list.php");
        exit();
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement du patient: " . $e->getMessage();
    $patient = [];
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-person-gear me-2"></i>Modifier le Patient
                </h2>
                <p class="text-muted mb-0">Modifier les informations du patient</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($patient)): ?>
                <form method="POST">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="civilite" class="form-label">Civilité *</label>
                                <select id="civilite" name="civilite" class="form-select" required>
                                    <option value="M" <?php echo $patient['civilite'] == 'M' ? 'selected' : ''; ?>>Monsieur</option>
                                    <option value="Mme" <?php echo $patient['civilite'] == 'Mme' ? 'selected' : ''; ?>>Madame</option>
                                    <option value="Mlle" <?php echo $patient['civilite'] == 'Mlle' ? 'selected' : ''; ?>>Mademoiselle</option>
                                    <option value="Enfant" <?php echo $patient['civilite'] == 'Enfant' ? 'selected' : ''; ?>>Enfant</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="genre" class="form-label">Genre *</label>
                                <select id="genre" name="genre" class="form-select" required>
                                    <option value="M" <?php echo $patient['genre'] == 'M' ? 'selected' : ''; ?>>Masculin</option>
                                    <option value="F" <?php echo $patient['genre'] == 'F' ? 'selected' : ''; ?>>Féminin</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" id="nom" name="nom" class="form-control" value="<?php echo htmlspecialchars($patient['nom']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo htmlspecialchars($patient['prenom']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="date_naissance" class="form-label">Date de Naissance *</label>
                                <input type="date" id="date_naissance" name="date_naissance" class="form-control" value="<?php echo htmlspecialchars($patient['date_naissance']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="telephone" class="form-label">Téléphone *</label>
                                <input type="tel" id="telephone" name="telephone" class="form-control" value="<?php echo htmlspecialchars($patient['telephone']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($patient['email']); ?>">
                    </div>

                    <div class="form-group mb-4">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea id="adresse" name="adresse" class="form-control" rows="3"><?php echo htmlspecialchars($patient['adresse']); ?></textarea>
                    </div>

                    <h5 class="mb-3">Personne à Contacter en Cas d'Urgence</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="personne_urgence_nom" class="form-label">Nom</label>
                                <input type="text" id="personne_urgence_nom" name="personne_urgence_nom" class="form-control" value="<?php echo htmlspecialchars($patient['personne_urgence_nom']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="personne_urgence_tel" class="form-label">Téléphone</label>
                                <input type="tel" id="personne_urgence_tel" name="personne_urgence_tel" class="form-control" value="<?php echo htmlspecialchars($patient['personne_urgence_tel']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-warning">Patient non trouvé</div>
                    <a href="list.php" class="btn btn-secondary">Retour à la liste</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
