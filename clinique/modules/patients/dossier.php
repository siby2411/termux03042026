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

// Récupérer les données du patient
$query = "SELECT * FROM patients WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("Location: list.php");
    exit();
}

// Récupérer l'historique des consultations
$query = "SELECT c.*, pers.nom as medecin_nom, pers.prenom as medecin_prenom
          FROM consultations c
          JOIN personnel pers ON c.id_medecin = pers.id
          WHERE c.id_patient = :id_patient
          ORDER BY c.date_consultation DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_patient', $id);
$stmt->execute();
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Dossier Médical - <?php echo htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']); ?></h2>
            <div>
                <a href="edit.php?id=<?php echo $patient['id']; ?>" class="btn btn-warning">Modifier</a>
                <a href="../rendezvous/add.php?id_patient=<?php echo $patient['id']; ?>" class="btn btn-primary">Nouveau RDV</a>
            </div>
        </div>
        <div class="card-body">
            <!-- Informations du patient -->
            <div class="patient-info" style="background: #f0f9ff; padding: 1rem; border-radius: 6px; margin-bottom: 2rem;">
                <h3>Informations Patient</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <p><strong>Code:</strong> <?php echo htmlspecialchars($patient['code_patient']); ?></p>
                        <p><strong>Nom Complet:</strong> <?php echo htmlspecialchars($patient['civilite'] . ' ' . $patient['prenom'] . ' ' . $patient['nom']); ?></p>
                        <p><strong>Date de Naissance:</strong> <?php echo htmlspecialchars($patient['date_naissance']); ?></p>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($patient['genre']); ?></p>
                    </div>
                    <div>
                        <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($patient['telephone']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                        <p><strong>Contact Urgence:</strong> <?php echo htmlspecialchars($patient['personne_urgence_nom'] . ' - ' . $patient['personne_urgence_tel']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Historique des consultations -->
            <h3>Historique des Consultations</h3>
            <?php if (count($consultations) > 0): ?>
                <?php foreach ($consultations as $consult): ?>
                <div class="consultation-item" style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 0.5rem;">
                        <div>
                            <strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($consult['date_consultation'])); ?>
                            <strong>Médecin:</strong> Dr. <?php echo htmlspecialchars($consult['medecin_prenom'] . ' ' . $consult['medecin_nom']); ?>
                        </div>
                        <a href="../consultations/view.php?id=<?php echo $consult['id']; ?>" class="btn btn-primary btn-sm">Voir Détails</a>
                    </div>
                    
                    <?php if (!empty($consult['diagnostics'])): ?>
                        <p><strong>Diagnostic:</strong> <?php echo htmlspecialchars(substr($consult['diagnostics'], 0, 100)); ?>...</p>
                    <?php endif; ?>
                    
                    <?php if (!empty($consult['prescriptions'])): ?>
                        <p><strong>Prescriptions:</strong> <?php echo htmlspecialchars(substr($consult['prescriptions'], 0, 100)); ?>...</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune consultation enregistrée pour ce patient.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
