<?php
include '../../includes/header.php';
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Génération du code patient
        $query = "SELECT COUNT(*) as count FROM patients WHERE YEAR(date_creation) = YEAR(CURDATE())";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;
        $code_patient = 'SN-CLN-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO patients 
                  (code_patient, civilite, nom, prenom, date_naissance, genre, adresse, telephone, email, personne_urgence_nom, personne_urgence_tel) 
                  VALUES 
                  (:code_patient, :civilite, :nom, :prenom, :date_naissance, :genre, :adresse, :telephone, :email, :personne_urgence_nom, :personne_urgence_tel)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':code_patient', $code_patient);
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
        
        if ($stmt->execute()) {
            header("Location: list.php?success=Patient ajouté avec succès");
            exit();
        } else {
            $error = "Erreur lors de l'ajout du patient";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-person-plus me-2"></i>Nouveau Patient
                </h2>
                <p class="text-muted mb-0">Ajouter un nouveau patient à la base de données</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="civilite" class="form-label">Civilité *</label>
                                <select id="civilite" name="civilite" class="form-select" required>
                                    <option value="M">Monsieur</option>
                                    <option value="Mme">Madame</option>
                                    <option value="Mlle">Mademoiselle</option>
                                    <option value="Enfant">Enfant</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="genre" class="form-label">Genre *</label>
                                <select id="genre" name="genre" class="form-select" required>
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" id="nom" name="nom" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" id="prenom" name="prenom" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="date_naissance" class="form-label">Date de Naissance *</label>
                                <input type="date" id="date_naissance" name="date_naissance" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="telephone" class="form-label">Téléphone *</label>
                                <input type="tel" id="telephone" name="telephone" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control">
                    </div>

                    <div class="form-group mb-4">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea id="adresse" name="adresse" class="form-control" rows="3"></textarea>
                    </div>

                    <h5 class="mb-3">Personne à Contacter en Cas d'Urgence</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="personne_urgence_nom" class="form-label">Nom</label>
                                <input type="text" id="personne_urgence_nom" name="personne_urgence_nom" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="personne_urgence_tel" class="form-label">Téléphone</label>
                                <input type="tel" id="personne_urgence_tel" name="personne_urgence_tel" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer le Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
