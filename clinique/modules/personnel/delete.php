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

// Vérifier si le personnel a des consultations, rendez-vous ou résultats d'analyses
$query = "SELECT 
    (SELECT COUNT(*) FROM consultations WHERE id_medecin = :id) as consultations_count,
    (SELECT COUNT(*) FROM rendez_vous WHERE id_medecin = :id) as rendezvous_count,
    (SELECT COUNT(*) FROM resultats_analyses WHERE id_technicien = :id) as analyses_count";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$dependencies = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        if ($_POST['confirmation'] === 'soft') {
            // Suppression logique (désactivation)
            $query = "UPDATE personnel SET is_active = 0, date_suppression = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Désactiver aussi l'utilisateur associé
            $query = "UPDATE users SET is_active = 0 WHERE id = (SELECT utilisateur_id FROM personnel WHERE id = :id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "Personnel désactivé avec succès";
        } elseif ($_POST['confirmation'] === 'hard' && $dependencies['consultations_count'] == 0 && $dependencies['rendezvous_count'] == 0 && $dependencies['analyses_count'] == 0) {
            // Suppression physique (seulement si pas de dépendances)
            $query = "DELETE FROM personnel WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "Personnel supprimé définitivement avec succès";
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

// Récupérer les infos du personnel
$query = "SELECT * FROM personnel WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$personnel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$personnel) {
    header("Location: list.php");
    exit();
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-danger mb-1">
                    <i class="bi bi-exclamation-triangle me-2"></i>Supprimer le Personnel
                </h2>
                <p class="text-muted mb-0">Gestion de la suppression du membre du personnel</p>
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
                    Vous êtes sur le point de supprimer : 
                    <strong><?php echo htmlspecialchars($personnel['civilite'] . ' ' . $personnel['prenom'] . ' ' . $personnel['nom']); ?></strong>
                    (<?php echo htmlspecialchars($personnel['matricule']); ?> - <?php echo htmlspecialchars($personnel['role']); ?>)
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
                                <h3 class="text-success"><?php echo $dependencies['analyses_count']; ?></h3>
                                <small class="text-muted">Analyses</small>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <?php if ($dependencies['consultations_count'] > 0 || $dependencies['rendezvous_count'] > 0 || $dependencies['analyses_count'] > 0): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Ce membre du personnel a des données associées. Seule la désactivation est possible.
                        </div>
                        <input type="hidden" name="confirmation" value="soft">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-person-x me-2"></i>Désactiver le Personnel
                        </button>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Type de suppression :</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="confirmation" id="softDelete" value="soft" checked>
                                <label class="form-check-label" for="softDelete">
                                    <strong>Désactivation</strong> - Le personnel sera masqué mais conservé
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
                                <i class="bi bi-person-x me-2"></i>Désactiver
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
