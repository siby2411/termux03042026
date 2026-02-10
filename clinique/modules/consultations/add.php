<?php
include '../../includes/header.php';
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
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
}

$rdv_id = $_GET['rdv_id'] ?? null;

if ($rdv_id) {
    $database = new Database();
    $db = $database->getConnection();
    
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

<div class="content">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Nouvelle Consultation</h2>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($rdv): ?>
            <div class="patient-info" style="background: #f0f9ff; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                <h3>Patient: <?php echo htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']); ?></h3>
                <p><strong>Motif:</strong> <?php echo htmlspecialchars($rdv['motif']); ?></p>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="id_rendez_vous" value="<?php echo $rdv_id; ?>">
                <input type="hidden" name="id_patient" value="<?php echo $rdv['patient_id'] ?? ''; ?>">
                <input type="hidden" name="id_medecin" value="<?php echo $rdv['medecin_id'] ?? ''; ?>">
                
                <div class="form-group">
                    <label for="symptomes">Symptômes et Observations</label>
                    <textarea id="symptomes" name="symptomes" class="form-control" rows="4" placeholder="Décrire les symptômes présentés par le patient..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="diagnostics">Diagnostic</label>
                    <textarea id="diagnostics" name="diagnostics" class="form-control" rows="3" placeholder="Diagnostic posé..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="prescriptions">Prescriptions</label>
                    <textarea id="prescriptions" name="prescriptions" class="form-control" rows="4" placeholder="Médicaments prescrits, posologie, durée..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="notes_medecin">Notes du Médecin</label>
                    <textarea id="notes_medecin" name="notes_medecin" class="form-control" rows="3" placeholder="Notes complémentaires..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Enregistrer la Consultation</button>
                <a href="../rendezvous/list.php" class="btn">Annuler</a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
