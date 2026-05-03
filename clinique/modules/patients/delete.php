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

// Vérifier si le patient a des consultations, rendez-vous ou factures
$query = "SELECT 
    (SELECT COUNT(*) FROM consultations WHERE id_patient = :id) as consultations_count,
    (SELECT COUNT(*) FROM rendez_vous WHERE id_patient = :id) as rendezvous_count,
    (SELECT COUNT(*) FROM factures WHERE id_patient = :id) as factures_count";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$dependencies = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        if ($_POST['confirmation'] === 'soft') {
            // Suppression logique (archivage)
            $query = "UPDATE patients SET is_active = 0, date_suppression = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "Patient archivé avec succès";
        } elseif ($_POST['confirmation'] === 'hard' && $dependencies['consultations_count'] == 0 && $dependencies['rendezvous_count'] == 0 && $dependencies['factures_count'] == 0) {
            // Suppression physique (seulement si pas de dépendances)
            $query = "DELETE FROM patients WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "Patient supprimé définitivement avec succès";
        } else {
            throw new Exception("Impossible de supprimer - données liées existantes");
        }
        
        $db->commit();
        header("Location: list.php?success=" . urlencode($message));
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Récupérer les infos du patient
$query = "SELECT * FROM patients WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("Location: list.php");
    exit();
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-danger mb-1">
                    <i class="bi bi-exclamation-triangle me-2"></i>Supprimer le Patient
                </h2>
                <p class="text-muted mb-0">Gestion de la suppression du patient</p>
            </div>
        </div>

        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-trash me-2"></i>Confirmation de suppression
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Attention - Action irréversible
                    </h6>
                    Vous êtes sur le point de supprimer le patient : 
                    <strong><?php echo htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']); ?></strong>
                    (<?php echo htmlspecialchars($patient['code_patient']); ?>)
                </div>

                <!-- Statistiques des dépendances -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-primary"><?php echo $dependencies['consultations_count']; ?></h3>
                                <small class="text-muted">Consultations</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-warning"><?php echo $dependencies['rendezvous_count']; ?></h3>
                                <small class="text-muted">Rendez-vous</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-success"><?php echo $dependencies['factures_count']; ?></h3>
                                <small class="text-muted">Factures</small>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <?php if ($dependencies['consultations_count'] > 0 || $dependencies['rendezvous_count'] > 0 || $dependencies['factures_count'] > 0): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Ce patient a des données associées. Seule l'archivage est possible.
                        </div>
                        <input type="hidden" name="confirmation" value="soft">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-archive me-2"></i>Archiver le Patient
                        </button>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Type de suppression :</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="confirmation" id="softDelete" value="soft" checked>
                                <label class="form-check-label" for="softDelete">
                                    <strong>Archivage</strong> - Le patient sera masqué mais conservé dans la base
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="confirmation" id="hardDelete" value="hard">
                                <label class="form-check-label" for="hardDelete">
                                    <strong>Suppression définitive</strong> - Effacement complet des données
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="soft" class="btn btn-warning">
                                <i class="bi bi-archive me-2"></i>Archiver
                            </button>
                            <button type="submit" name="action" value="hard" class="btn btn-danger">
                                <i class="bi bi-trash me-2"></i>Supprimer Définitivement
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <a href="list.php" class="btn btn-secondary mt-3">
                        <i class="bi bi-arrow-left me-2"></i>Annuler
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
