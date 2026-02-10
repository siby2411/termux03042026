<?php
include '../../includes/header.php';
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Générer matricule
        $role_prefix = strtoupper(substr($_POST['role'], 0, 3));
        $query = "SELECT COUNT(*) as count FROM personnel WHERE role = :role AND YEAR(date_creation) = YEAR(CURDATE())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role', $_POST['role']);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;
        $matricule = $role_prefix . '-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO personnel (matricule, civilite, nom, prenom, role, id_specialite, id_departement, email, telephone) 
                  VALUES (:matricule, :civilite, :nom, :prenom, :role, :id_specialite, :id_departement, :email, :telephone)";
        
        $stmt = $db->prepare($query);
        
        $id_specialite = !empty($_POST['id_specialite']) ? $_POST['id_specialite'] : null;
        
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':civilite', $_POST['civilite']);
        $stmt->bindParam(':nom', $_POST['nom']);
        $stmt->bindParam(':prenom', $_POST['prenom']);
        $stmt->bindParam(':role', $_POST['role']);
        $stmt->bindParam(':id_specialite', $id_specialite);
        $stmt->bindParam(':id_departement', $_POST['id_departement']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':telephone', $_POST['telephone']);
        
        if ($stmt->execute()) {
            header("Location: list.php?success=Personnel ajouté avec succès");
            exit();
        } else {
            $error = "Erreur lors de l'ajout du personnel";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupérer départements et spécialités
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM departements WHERE is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$departements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM specialites";
$stmt = $db->prepare($query);
$stmt->execute();
$specialites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-person-plus me-2"></i>Nouveau Personnel
                </h2>
                <p class="text-muted mb-0">Ajouter un nouveau membre du personnel</p>
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
                                    <option value="Dr">Docteur</option>
                                    <option value="Pr">Professeur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="role" class="form-label">Rôle *</label>
                                <select id="role" name="role" class="form-select" required>
                                    <option value="Medecin">Médecin</option>
                                    <option value="Infirmier">Infirmier</option>
                                    <option value="Secretaire">Secrétaire</option>
                                    <option value="Comptable">Comptable</option>
                                    <option value="Admin">Administrateur</option>
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
                                <label for="id_departement" class="form-label">Département *</label>
                                <select id="id_departement" name="id_departement" class="form-select" required>
                                    <?php foreach ($departements as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['nom']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="id_specialite" class="form-label">Spécialité</label>
                                <select id="id_specialite" name="id_specialite" class="form-select">
                                    <option value="">-- Sélectionner --</option>
                                    <?php foreach ($specialites as $spec): ?>
                                        <option value="<?php echo $spec['id']; ?>"><?php echo htmlspecialchars($spec['nom']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="telephone" class="form-label">Téléphone *</label>
                                <input type="tel" id="telephone" name="telephone" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    const specialiteField = document.getElementById('id_specialite');
    if (this.value === 'Medecin') {
        specialiteField.required = true;
    } else {
        specialiteField.required = false;
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
