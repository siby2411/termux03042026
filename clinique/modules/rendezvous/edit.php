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
    $date_heure = $_POST['date'] . ' ' . $_POST['heure'];
    
    $query = "UPDATE rendez_vous SET id_patient = :id_patient, id_medecin = :id_medecin, 
              id_departement = :id_departement, date_heure = :date_heure, motif = :motif, 
              notes_secretaire = :notes_secretaire, statut = :statut 
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id_patient', $_POST['id_patient']);
    $stmt->bindParam(':id_medecin', $_POST['id_medecin']);
    $stmt->bindParam(':id_departement', $_POST['id_departement']);
    $stmt->bindParam(':date_heure', $date_heure);
    $stmt->bindParam(':motif', $_POST['motif']);
    $stmt->bindParam(':notes_secretaire', $_POST['notes_secretaire']);
    $stmt->bindParam(':statut', $_POST['statut']);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        header("Location: list.php?success=Rendez-vous modifié avec succès");
        exit();
    } else {
        $error = "Erreur lors de la modification du rendez-vous";
    }
}

// Récupérer les données actuelles
$query = "SELECT * FROM rendez_vous WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$rdv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rdv) {
    header("Location: list.php");
    exit();
}

// Récupérer les listes
$query = "SELECT id, code_patient, nom, prenom FROM patients ORDER BY nom, prenom";
$stmt = $db->prepare($query);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT id, nom, prenom FROM personnel WHERE role = 'Medecin' ORDER BY nom, prenom";
$stmt = $db->prepare($query);
$stmt->execute();
$medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT id, nom FROM departements WHERE is_active = 1 ORDER BY nom";
$stmt = $db->prepare($query);
$stmt->execute();
$departements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formater la date et l'heure
$date = date('Y-m-d', strtotime($rdv['date_heure']));
$heure = date('H:i', strtotime($rdv['date_heure']));
?>

<div class="content">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Modifier le Rendez-vous</h2>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="id_patient">Patient</label>
                        <select id="id_patient" name="id_patient" class="form-control" required>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['id']; ?>" <?php echo $rdv['id_patient'] == $patient['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($patient['code_patient'] . ' - ' . $patient['prenom'] . ' ' . $patient['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_medecin">Médecin</label>
                        <select id="id_medecin" name="id_medecin" class="form-control" required>
                            <?php foreach ($medecins as $medecin): ?>
                                <option value="<?php echo $medecin['id']; ?>" <?php echo $rdv['id_medecin'] == $medecin['id'] ? 'selected' : ''; ?>>
                                    Dr. <?php echo htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="id_departement">Département</label>
                        <select id="id_departement" name="id_departement" class="form-control" required>
                            <?php foreach ($departements as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $rdv['id_departement'] == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?php echo $date; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="heure">Heure</label>
                        <input type="time" id="heure" name="heure" class="form-control" value="<?php echo $heure; ?>" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut" class="form-control" required>
                            <option value="Planifié" <?php echo $rdv['statut'] == 'Planifié' ? 'selected' : ''; ?>>Planifié</option>
                            <option value="Confirmé" <?php echo $rdv['statut'] == 'Confirmé' ? 'selected' : ''; ?>>Confirmé</option>
                            <option value="En Cours" <?php echo $rdv['statut'] == 'En Cours' ? 'selected' : ''; ?>>En Cours</option>
                            <option value="Terminé" <?php echo $rdv['statut'] == 'Terminé' ? 'selected' : ''; ?>>Terminé</option>
                            <option value="Annulé" <?php echo $rdv['statut'] == 'Annulé' ? 'selected' : ''; ?>>Annulé</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="motif">Motif de consultation</label>
                    <textarea id="motif" name="motif" class="form-control" rows="3" required><?php echo htmlspecialchars($rdv['motif']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="notes_secretaire">Notes secrétariat</label>
                    <textarea id="notes_secretaire" name="notes_secretaire" class="form-control" rows="2"><?php echo htmlspecialchars($rdv['notes_secretaire']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Modifier</button>
                <a href="list.php" class="btn">Annuler</a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
