<?php
include '../../includes/header.php';
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $query = "INSERT INTO consultations (id_rendez_vous, id_patient, id_medecin, symptomes, diagnostics, prescriptions, notes_medecin) 
                  VALUES (:id_rendez_vous, :id_patient, :id_medecin, :symptomes, :diagnostics, :prescriptions, :notes_medecin)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':id_rendez_vous', $_POST['id_rendez_vous']);
        $stmt->bindParam(':id_patient', $_POST['id_patient']);
        $stmt->bindParam(':id_medecin', $_POST['id_medecin']);
        $stmt->bindParam(':symptomes', $_POST['symptomes']);
        $stmt->bindParam(':diagnostics', $_POST['diagnostics']);
        $stmt->bindParam(':prescriptions', $_POST['prescriptions']);
        $stmt->bindParam(':notes_medecin', $_POST['notes_medecin']);
        
        if ($stmt->execute()) {
            // Mettre à jour le statut du rendez-vous
            $updateQuery = "UPDATE rendez_vous SET statut = 'Terminé' WHERE id = :id_rendez_vous";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':id_rendez_vous', $_POST['id_rendez_vous']);
            $updateStmt->execute();
            
            header("Location: list.php?success=Consultation enregistrée avec succès");
            exit();
        } else {
            $error = "Erreur lors de l'enregistrement de la consultation";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

$rdv_id = $_GET['rdv_id'] ?? null;

if ($rdv_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT rv.*, 
                     p.id as patient_id, p.code_patient, p.nom as patient_nom, p.prenom as patient_prenom, p.date_naissance, p.genre,
                     pers.id as medecin_id, pers.matricule, pers.nom as medecin_nom, pers.prenom as medecin_prenom,
                     d.nom as departement_nom
              FROM rendez_vous rv
              JOIN patients p ON rv.id_patient = p.id
              JOIN personnel pers ON rv.id_medecin = pers.id
              JOIN departements d ON rv.id_departement = d.id
              WHERE rv.id = :rdv_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':rdv_id', $rdv_id);
    $stmt->execute();
    $rdv = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rdv) {
        header("Location: ../rendezvous/list.php");
        exit();
    }
} else {
    header("Location: ../rendezvous/list.php");
    exit();
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-heart-pulse me-2"></i>Nouvelle Consultation
                </h2>
                <p class="text-muted mb-0">Enregistrement de la consultation médicale</p>
            </div>
            <a href="../rendezvous/list.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Retour aux RDV
            </a>
        </div>

        <!-- Informations du rendez-vous -->
        <?php if ($rdv): ?>
        <div class="card border-primary mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Informations du Rendez-vous
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-person me-2"></i>Informations Patient
                        </h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Code Patient:</strong></td>
                                <td><span class="badge bg-success"><?php echo htmlspecialchars($rdv['code_patient']); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Nom Complet:</strong></td>
                                <td><?php echo htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Date de Naissance:</strong></td>
                                <td>
                                    <?php echo $rdv['date_naissance'] ? date('d/m/Y', strtotime($rdv['date_naissance'])) : 'Non spécifié'; ?>
                                    (<?php echo $rdv['date_naissance'] ? floor((time() - strtotime($rdv['date_naissance'])) / 31556926) . ' ans' : ''; ?>)
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Genre:</strong></td>
                                <td><?php echo htmlspecialchars($rdv['genre']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-person-badge me-2"></i>Informations Médecin
                        </h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Matricule:</strong></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($rdv['matricule']); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Médecin:</strong></td>
                                <td>Dr. <?php echo htmlspecialchars($rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Département:</strong></td>
                                <td><?php echo htmlspecialchars($rdv['departement_nom']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Date RDV:</strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($rdv['date_heure'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-chat-dots me-2"></i>Motif de Consultation
                        </h6>
                        <div class="alert alert-light border">
                            <?php echo htmlspecialchars($rdv['motif']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulaire de consultation -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-plus me-2"></i>Compte Rendu Médical
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="id_rendez_vous" value="<?php echo $rdv_id; ?>">
                    <input type="hidden" name="id_patient" value="<?php echo $rdv['patient_id'] ?? ''; ?>">
                    <input type="hidden" name="id_medecin" value="<?php echo $rdv['medecin_id'] ?? ''; ?>">
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="symptomes" class="form-label fw-semibold">
                                    <i class="bi bi-clipboard-pulse me-2"></i>Symptômes et Observations
                                </label>
                                <textarea id="symptomes" name="symptomes" class="form-control" rows="5" 
                                          placeholder="Décrire les symptômes présentés par le patient, les observations cliniques, les antécédents pertinents..."></textarea>
                                <div class="form-text">
                                    Décrivez précisément les symptômes, leur durée, intensité et contexte d'apparition.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="diagnostics" class="form-label fw-semibold">
                                    <i class="bi bi-clipboard-check me-2"></i>Diagnostic(s)
                                </label>
                                <textarea id="diagnostics" name="diagnostics" class="form-control" rows="4" 
                                          placeholder="Diagnostic(s) posé(s), hypothèses diagnostiques..."></textarea>
                                <div class="form-text">
                                    Précisez le diagnostic principal et les diagnostics secondaires éventuels.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="prescriptions" class="form-label fw-semibold">
                                    <i class="bi bi-capsule me-2"></i>Prescriptions et Traitements
                                </label>
                                <textarea id="prescriptions" name="prescriptions" class="form-control" rows="5" 
                                          placeholder="Médicaments prescrits, posologie, durée du traitement, recommandations..."></textarea>
                                <div class="form-text">
                                    Indiquez clairement les médicaments, dosages, fréquence et durée des traitements.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="notes_medecin" class="form-label fw-semibold">
                                    <i class="bi bi-sticky me-2"></i>Notes du Médecin
                                </label>
                                <textarea id="notes_medecin" name="notes_medecin" class="form-control" rows="3" 
                                          placeholder="Notes complémentaires, suivi recommandé, conseils au patient..."></textarea>
                                <div class="form-text">
                                    Informations supplémentaires pour le suivi du patient.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="../rendezvous/list.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Enregistrer la Consultation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.table-borderless td {
    border: none !important;
    padding: 0.25rem 0;
}

.form-text {
    font-size: 0.875rem;
    color: #6b7280;
}

.card {
    border-radius: 10px;
}
</style>

<script>
// Auto-resize des textareas
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    // Focus sur le premier champ
    document.getElementById('symptomes').focus();
});
</script>

<?php include '../../includes/footer.php'; ?>
