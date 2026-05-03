<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
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
            $last_id = $db->lastInsertId(); // Récupérer l'ID de la consultation
            
            // Mettre à jour le statut du rendez-vous
            $updateQuery = "UPDATE rendez_vous SET statut = 'Terminé' WHERE id = :id_rendez_vous";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute(['id_rendez_vous' => $_POST['id_rendez_vous']]);
            
            $db->commit();
            // Rediriger vers l'impression de l'ordonnance
            header("Location: print_ordonnance.php?id=" . $last_id);
            exit();
        }
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}

$rdv_id = $_GET['rdv_id'] ?? null;
$rdv = null;

if ($rdv_id) {
    $query = "SELECT rv.*, p.id as patient_id, p.nom as patient_nom, p.prenom as patient_prenom,
                     pers.id as medecin_id, pers.nom as medecin_nom, pers.prenom as medecin_prenom
              FROM rendez_vous rv
              JOIN patients p ON rv.id_patient = p.id
              JOIN personnel pers ON rv.id_medecin = pers.id
              WHERE rv.id = :rdv_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':rdv_id', $rdv_id);
    $stmt->execute();
    $rdv = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-4">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi- dossiers"></i> Nouvelle Consultation</h4>
            <?php if($rdv): ?>
                <span class="badge bg-light text-primary">RDV #<?= $rdv_id ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($rdv): ?>
            <div class="alert alert-info border-0">
                <h5 class="alert-heading">Patient : <?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']); ?></h5>
                <p class="mb-0"><strong>Motif du RDV :</strong> <?= htmlspecialchars($rdv['motif']); ?></p>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="id_rendez_vous" value="<?= $rdv_id; ?>">
                <input type="hidden" name="id_patient" value="<?= $rdv['patient_id'] ?? ''; ?>">
                <input type="hidden" name="id_medecin" value="<?= $rdv['medecin_id'] ?? ''; ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Symptômes et Observations</label>
                        <textarea name="symptomes" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Diagnostic</label>
                        <textarea name="diagnostics" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold text-success"><i class="bi bi-prescription2"></i> Prescriptions (Ordonnance)</label>
                    <textarea name="prescriptions" class="form-control" rows="5" placeholder="Médicaments, posologie..." required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Notes Internes</label>
                    <textarea name="notes_medecin" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="../rendezvous/list.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-printer me-2"></i> Enregistrer et Imprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
