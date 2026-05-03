<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'medecin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les infos du médecin
$stmt = $db->prepare("SELECT u.*, s.name as service_nom 
                      FROM users u
                      JOIN services s ON u.service_id = s.id
                      WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();

// Récupérer la file d'attente du service
$queue = $db->prepare("
    SELECT f.*, p.nom, p.prenom, p.code_patient_unique, p.telephone,
           p.antecedent_medicaux, p.allergie, p.traitement_en_cours
    FROM file_attente f
    JOIN patients p ON f.patient_id = p.id
    WHERE f.service_id = ? AND f.statut = 'en_attente'
    ORDER BY FIELD(f.priorite, 'urgence', 'senior', 'normal'), f.cree_a ASC
");
$queue->execute([$medecin['service_id']]);
$file_attente = $queue->fetchAll();

// Récupérer les actes médicaux pour le traitement
$actes = $db->prepare("
    SELECT a.*, s.name as service_nom
    FROM actes_medicaux a
    JOIN services s ON a.service_id = s.id
    WHERE a.prix_traitement > 0
    ORDER BY a.libelle
");
$actes->execute();
$traitements = $actes->fetchAll();

// Traitement de la consultation
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_consultation'])) {
    try {
        $db->beginTransaction();
        
        // 1. Récupérer le patient
        $patient_id = $_POST['patient_id'];
        
        // 2. Enregistrer la consultation
        $query_consult = "INSERT INTO consultations (
            patient_id, medecin_id, service_id, motif, diagnostic, 
            observations, date_consultation
        ) VALUES (
            :patient_id, :medecin_id, :service_id, :motif, :diagnostic,
            :observations, NOW()
        )";
        
        $stmt_consult = $db->prepare($query_consult);
        $stmt_consult->execute([
            ':patient_id' => $patient_id,
            ':medecin_id' => $_SESSION['user_id'],
            ':service_id' => $medecin['service_id'],
            ':motif' => $_POST['motif'],
            ':diagnostic' => $_POST['diagnostic'],
            ':observations' => $_POST['observations']
        ]);
        
        $consultation_id = $db->lastInsertId();
        
        // 3. Mettre à jour le dossier médical
        $update_dossier = "UPDATE dossiers_medicaux 
                          SET antecedents_familiaux = CONCAT(antecedents_familiaux, '\n', :nouveau),
                              updated_at = NOW()
                          WHERE patient_id = :patient_id";
        $stmt_dossier = $db->prepare($update_dossier);
        $stmt_dossier->execute([
            ':nouveau' => date('d/m/Y') . " - Consultation: " . $_POST['diagnostic'],
            ':patient_id' => $patient_id
        ]);
        
        // 4. Si un prochain rendez-vous est programmé
        if (!empty($_POST['prochain_rdv']) && !empty($_POST['traitement_id'])) {
            $query_rdv = "INSERT INTO rendez_vous (
                patient_id, service_id, medecin_id, date_rdv, heure_rdv,
                motif, statut, notes, cree_le
            ) VALUES (
                :patient_id, :service_id, :medecin_id, :date_rdv, :heure_rdv,
                :motif, 'programme', :notes, NOW()
            )";
            
            $stmt_rdv = $db->prepare($query_rdv);
            $stmt_rdv->execute([
                ':patient_id' => $patient_id,
                ':service_id' => $medecin['service_id'],
                ':medecin_id' => $_SESSION['user_id'],
                ':date_rdv' => $_POST['prochain_rdv'],
                ':heure_rdv' => $_POST['heure_rdv'] ?? '09:00:00',
                ':motif' => 'Traitement: ' . $_POST['traitement_nom'],
                ':notes' => 'Suite de la consultation du ' . date('d/m/Y')
            ]);
            
            $new_rdv_id = $db->lastInsertId();
            
            // 5. Mettre à jour le statut du patient dans la file d'attente
            $update_queue = "UPDATE file_attente SET statut = 'termine' 
                            WHERE patient_id = ? AND statut = 'en_attente'";
            $stmt_queue = $db->prepare($update_queue);
            $stmt_queue->execute([$patient_id]);
            
            $message = "✅ Consultation enregistrée. Prochain rendez-vous programmé (ID: $new_rdv_id)";
        } else {
            // Si pas de prochain RDV, simplement terminer la consultation
            $update_queue = "UPDATE file_attente SET statut = 'termine' 
                            WHERE patient_id = ? AND statut = 'en_attente'";
            $stmt_queue = $db->prepare($update_queue);
            $stmt_queue->execute([$patient_id]);
            
            $message = "✅ Consultation enregistrée. Patient retiré de la file d'attente.";
        }
        
        $db->commit();
        $message_type = "success";
        
        // Rafraîchir la page
        header("Refresh:2");
        
    } catch (Exception $e) {
        $db->rollBack();
        $message = "❌ Erreur: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Récupérer le patient sélectionné
$patient_selectionne = null;
if (isset($_GET['patient_id'])) {
    $stmt = $db->prepare("
        SELECT p.*, f.token, f.priorite
        FROM patients p
        JOIN file_attente f ON p.id = f.patient_id
        WHERE p.id = ? AND f.statut = 'en_attente'
    ");
    $stmt->execute([$_GET['patient_id']]);
    $patient_selectionne = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation - <?= htmlspecialchars($medecin['service_nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px;
            color: white;
        }
        .navbar a { color: white; text-decoration: none; }
        .container-fluid { padding: 20px; }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 20px;
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            margin: -25px -25px 20px -25px;
            padding: 15px 25px;
            border-radius: 15px 15px 0 0;
            font-weight: 600;
        }
        
        .queue-item {
            background: #f8f9fa;
            border-left: 4px solid #1e3c72;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .queue-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .queue-item.urgence { border-left-color: #dc3545; }
        .queue-item.selected {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
        }
        
        .patient-info {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .btn-consult {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
        }
        
        .traitement-select {
            border: 2px solid #1e3c72;
            border-radius: 8px;
            padding: 10px;
        }
        
        .info-rdv {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h3><i class="fas fa-stethoscope"></i> Dr. <?= htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']) ?> - <?= htmlspecialchars($medecin['service_nom']) ?></h3>
                    <small><i class="fas fa-clock"></i> <?= date('d/m/Y H:i') ?></small>
                </div>
                <div>
                    <a href="index.php" class="btn btn-sm btn-light me-2">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                    <a href="../auth/logout.php" class="btn btn-sm btn-light">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Colonne de gauche : File d'attente -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-clock"></i> Patients en attente (<?= count($file_attente) ?>)
                    </div>
                    
                    <?php if (empty($file_attente)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-check-circle"></i> Aucun patient en attente
                        </div>
                    <?php else: ?>
                        <div style="max-height: 500px; overflow-y: auto;">
                            <?php foreach($file_attente as $patient): ?>
                                <div class="queue-item <?= $patient['priorite'] ?> <?= ($patient_selectionne && $patient_selectionne['id'] == $patient['id']) ? 'selected' : '' ?>" 
                                     onclick="window.location.href='?patient_id=<?= $patient['id'] ?>'">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="badge bg-primary"><?= $patient['token'] ?></span>
                                            <strong class="ms-2"><?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-id-card"></i> <?= htmlspecialchars($patient['code_patient_unique']) ?>
                                            </small>
                                        </div>
                                        <span class="badge <?= $patient['priorite'] == 'urgence' ? 'bg-danger' : ($patient['priorite'] == 'senior' ? 'bg-warning' : 'bg-success') ?>">
                                            <?= ucfirst($patient['priorite']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Colonne de droite : Formulaire de consultation -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-notes-medical"></i> Consultation médicale
                    </div>
                    
                    <?php if (!$patient_selectionne): ?>
                        <div class="alert alert-info text-center p-5">
                            <i class="fas fa-hand-pointer fa-3x mb-3"></i>
                            <h5>Sélectionnez un patient dans la file d'attente</h5>
                        </div>
                    <?php else: ?>
                        <!-- Informations patient -->
                        <div class="patient-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><?= htmlspecialchars($patient_selectionne['prenom'] . ' ' . $patient_selectionne['nom']) ?></h5>
                                    <p>
                                        <i class="fas fa-id-card"></i> N° Patient: <?= htmlspecialchars($patient_selectionne['code_patient_unique']) ?><br>
                                        <i class="fas fa-phone"></i> Téléphone: <?= htmlspecialchars($patient_selectionne['telephone'] ?? 'Non renseigné') ?><br>
                                        <i class="fas fa-tag"></i> Token: <?= $patient_selectionne['token'] ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Antécédents:</strong>
                                    <p class="small">
                                        <?= nl2br(htmlspecialchars($patient_selectionne['antecedent_medicaux'] ?? 'Aucun')) ?>
                                    </p>
                                    <strong>Allergies:</strong>
                                    <p class="small">
                                        <?= nl2br(htmlspecialchars($patient_selectionne['allergie'] ?? 'Aucune')) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de consultation -->
                        <form method="POST" action="">
                            <input type="hidden" name="patient_id" value="<?= $patient_selectionne['id'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Motif de la consultation</label>
                                <input type="text" name="motif" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Diagnostic</label>
                                <textarea name="diagnostic" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observations / Prescriptions</label>
                                <textarea name="observations" class="form-control" rows="3"></textarea>
                            </div>

                            <hr>
                            <h5 class="mb-3"><i class="fas fa-calendar-plus"></i> Prochain rendez-vous</h5>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date du prochain rendez-vous</label>
                                    <input type="date" name="prochain_rdv" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Heure</label>
                                    <input type="time" name="heure_rdv" class="form-control" value="09:00">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Type de traitement</label>
                                    <select name="traitement_id" class="form-select traitement-select">
                                        <option value="">Sélectionner un traitement</option>
                                        <?php foreach($traitements as $traitement): ?>
                                            <option value="<?= $traitement['id'] ?>" 
                                                    data-prix="<?= $traitement['prix_traitement'] ?>"
                                                    data-nom="<?= htmlspecialchars($traitement['libelle']) ?>">
                                                <?= htmlspecialchars($traitement['libelle']) ?> - 
                                                <?= htmlspecialchars($traitement['service_nom']) ?> - 
                                                <?= number_format($traitement['prix_traitement'], 0, ',', ' ') ?> FCFA
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <input type="hidden" name="traitement_nom" id="traitement_nom">
                            
                            <div id="infoTraitement" class="info-rdv" style="display: none;">
                                <i class="fas fa-info-circle"></i> 
                                <span id="traitement_details"></span>
                                <br>
                                <small>Le patient devra payer ce traitement à la caisse avant la prochaine consultation</small>
                            </div>

                            <div class="mt-4">
                                <button type="submit" name="save_consultation" class="btn-consult">
                                    <i class="fas fa-check-circle"></i> Enregistrer la consultation
                                </button>
                            </div>
                        </form>

                        <script>
                            document.querySelector('select[name="traitement_id"]').addEventListener('change', function() {
                                const selected = this.options[this.selectedIndex];
                                const infoDiv = document.getElementById('infoTraitement');
                                const detailsSpan = document.getElementById('traitement_details');
                                const traitementNom = document.getElementById('traitement_nom');
                                
                                if (selected && selected.value) {
                                    const prix = selected.getAttribute('data-prix');
                                    const nom = selected.getAttribute('data-nom');
                                    const service = selected.text.split('-')[1].trim();
                                    
                                    detailsSpan.innerHTML = `<strong>${nom}</strong> - ${service} - ${parseInt(prix).toLocaleString('fr-FR')} FCFA`;
                                    infoDiv.style.display = 'block';
                                    traitementNom.value = nom;
                                } else {
                                    infoDiv.style.display = 'none';
                                    traitementNom.value = '';
                                }
                            });
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
