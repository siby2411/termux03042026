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
        $query = "UPDATE personnel SET civilite = :civilite, nom = :nom, prenom = :prenom, role = :role, 
                  id_specialite = :id_specialite, id_departement = :id_departement, email = :email, telephone = :telephone 
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        
        $id_specialite = !empty($_POST['id_specialite']) ? $_POST['id_specialite'] : null;
        
        $stmt->bindParam(':civilite', $_POST['civilite']);
        $stmt->bindParam(':nom', $_POST['nom']);
        $stmt->bindParam(':prenom', $_POST['prenom']);
        $stmt->bindParam(':role', $_POST['role']);
        $stmt->bindParam(':id_specialite', $id_specialite);
        $stmt->bindParam(':id_departement', $_POST['id_departement']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':telephone', $_POST['telephone']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            header("Location: list.php?success=Personnel modifié avec succès");
            exit();
        } else {
            $error = "Erreur lors de la modification du personnel";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupérer les données actuelles
try {
    $query = "SELECT * FROM personnel WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $personnel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$personnel) {
        header("Location: list.php");
        exit();
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement du personnel: " . $e->getMessage();
    $personnel = [];
}

// Récupérer départements et spécialités
try {
    $query = "SELECT * FROM departements WHERE is_active = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $departements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT * FROM specialites";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $specialites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Erreur lors du chargement des données: " . $e->getMessage();
    $departements = [];
    $specialites = [];
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-person-gear me-2"></i>Modifier le Personnel
                </h2>
                <p class="text-muted mb-0">Modifier les informations du membre du personnel</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($personnel)): ?>
                <form method="POST">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="civilite" class="form-label">Civilité *</label>
                                <select id="civilite" name="civilite" class="form-select" required>
                                    <option value="M" <?php echo $personnel['civilite'] == 'M' ? 'selected' : ''; ?>>Monsieur</option>
                                    <option value="Mme" <?php echo $personnel['civilite'] == 'Mme' ? 'selected' : ''; ?>>Madame</option>
                                    <option value="Dr" <?php echo $personnel['civilite'] == 'Dr' ? 'selected' : ''; ?>>Docteur</option>
                                    <option value="Pr" <?php echo $personnel['civilite'] == 'Pr' ? 'selected' : ''; ?>>Professeur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="role" class="form-label">Rôle *</label>
                                <select id="role" name="role" class="form-select" required>
                                    <option value="Medecin" <?php echo $personnel['role'] == 'Medecin' ? 'selected' : ''; ?>>Médecin</option>
                                    <option value="Infirmier" <?php echo $personnel['role'] == 'Infirmier' ? 'selected' : ''; ?>>Infirmier</option>
                                    <option value="Secretaire" <?php echo $personnel['role'] == 'Secretaire' ? 'selected' : ''; ?>>Secrétaire</option>
                                    <option value="Comptable" <?php echo $personnel['role'] == 'Comptable' ? 'selected' : ''; ?>>Comptable</option>
                                    <option value="Admin" <?php echo $personnel['role'] == 'Admin' ? 'selected' : ''; ?>>Administrateur</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" id="nom" name="nom" class="form-control" value="<?php echo htmlspecialchars($personnel['nom']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo htmlspecialchars($personnel['prenom']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="id_departement" class="form-label">Département *</label>
                                <select id="id_departement" name="id_departement" class="form-select" required>
                                    <?php foreach ($departements as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>" <?php echo $personnel['id_departement'] == $dept['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['nom']); ?>
                                        </option>
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
                                        <option value="<?php echo $spec['id']; ?>" <?php echo $personnel['id_specialite'] == $spec['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($spec['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="telephone" class="form-label">Téléphone *</label>
                                <input type="tel" id="telephone" name="telephone" class="form-control" value="<?php echo htmlspecialchars($personnel['telephone']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($personnel['email']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-warning">Personnel non trouvé</div>
                    <a href="list.php" class="btn btn-secondary">Retour à la liste</a>
                <?php endif; ?>
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
