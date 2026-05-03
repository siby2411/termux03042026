<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$message = '';
$error = '';

// Création d'un rendez-vous
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rdv'])) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO rendez_vous (
                patient_id, service_id, medecin_id, date_rdv, heure_rdv, motif, notes, statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'programme')
        ");
        $stmt->execute([
            $_POST['patient_id'],
            $_POST['service_id'],
            $_POST['medecin_id'] ?: null,
            $_POST['date_rdv'],
            $_POST['heure_rdv'],
            $_POST['motif'],
            $_POST['notes']
        ]);
        
        $message = "Rendez-vous enregistré avec succès";
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Mise à jour du statut
if (isset($_GET['honorer'])) {
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'honore' WHERE id = ?");
    $stmt->execute([$_GET['honorer']]);
    $message = "Rendez-vous marqué comme honoré";
}

if (isset($_GET['annuler'])) {
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE id = ?");
    $stmt->execute([$_GET['annuler']]);
    $message = "Rendez-vous annulé";
}

// Récupérer les rendez-vous
$role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

if ($role == 'admin') {
    $rdvs = $pdo->query("
        SELECT r.*, p.prenom as patient_prenom, p.nom as patient_nom, 
               p.code_patient_unique, s.name as service_nom,
               CONCAT(u.prenom, ' ', u.nom) as medecin_nom
        FROM rendez_vous r
        JOIN patients p ON r.patient_id = p.id
        JOIN services s ON r.service_id = s.id
        LEFT JOIN users u ON r.medecin_id = u.id
        ORDER BY r.date_rdv, r.heure_rdv
    ")->fetchAll();
} else {
    $rdvs = $pdo->prepare("
        SELECT r.*, p.prenom as patient_prenom, p.nom as patient_nom,
               p.code_patient_unique, s.name as service_nom
        FROM rendez_vous r
        JOIN patients p ON r.patient_id = p.id
        JOIN services s ON r.service_id = s.id
        WHERE r.medecin_id = ? OR r.service_id = (SELECT service_id FROM users WHERE id = ?)
        ORDER BY r.date_rdv, r.heure_rdv
    ");
    $rdvs->execute([$user_id, $user_id]);
    $rdvs = $rdvs->fetchAll();
}

// Récupérer les patients, services et médecins pour le formulaire
$patients = $pdo->query("SELECT id, prenom, nom, code_patient_unique FROM patients ORDER BY nom")->fetchAll();
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();
$medecins = $pdo->query("
    SELECT id, prenom, nom, service_id 
    FROM users 
    WHERE role = 'medecin' AND actif = 1
    ORDER BY nom
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small><?= ucfirst($role) ?></small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../dashboard/index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="index.php" class="active"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <?php if ($role == 'admin'): ?>
                        <li><a href="../patients/index.php"><i class="fas fa-users"></i> Patients</a></li>
                        <?php endif; ?>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-calendar-alt"></i> Gestion des rendez-vous</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <!-- Bouton pour ouvrir le modal -->
                <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#rdvModal">
                    <i class="fas fa-plus"></i> Nouveau rendez-vous
                </button>
                
                <!-- Liste des rendez-vous -->
                <div class="dashboard-card">
                    <ul class="nav nav-tabs mb-3" id="rdvTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today" type="button">
                                Aujourd'hui
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button">
                                À venir
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button">
                                Passés
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <?php
                        $today = date('Y-m-d');
                        $today_rdv = array_filter($rdvs, fn($r) => $r['date_rdv'] == $today);
                        $upcoming_rdv = array_filter($rdvs, fn($r) => $r['date_rdv'] > $today);
                        $past_rdv = array_filter($rdvs, fn($r) => $r['date_rdv'] < $today);
                        ?>
                        
                        <!-- Aujourd'hui -->
                        <div class="tab-pane fade show active" id="today">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Heure</th>
                                        <th>Patient</th>
                                        <th>Code</th>
                                        <th>Service</th>
                                        <th>Médecin</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_rdv as $r): ?>
                                    <tr>
                                        <td><?= substr($r['heure_rdv'], 0, 5) ?></td>
                                        <td><?= $r['patient_prenom'] ?> <?= $r['patient_nom'] ?></td>
                                        <td><span class="badge bg-secondary"><?= $r['code_patient_unique'] ?></span></td>
                                        <td><?= $r['service_nom'] ?></td>
                                        <td><?= $r['medecin_nom'] ?? '-' ?></td>
                                        <td><?= substr($r['motif'], 0, 30) ?></td>
                                        <td>
                                            <span class="badge <?= match($r['statut']) {
                                                'programme' => 'bg-warning',
                                                'confirme' => 'bg-info',
                                                'honore' => 'bg-success',
                                                'annule' => 'bg-danger',
                                                default => 'bg-secondary'
                                            } ?>">
                                                <?= $r['statut'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($r['statut'] == 'programme'): ?>
                                            <a href="?honorer=<?= $r['id'] ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="?annuler=<?= $r['id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Annuler ce rendez-vous ?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- À venir -->
                        <div class="tab-pane fade" id="upcoming">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Patient</th>
                                        <th>Service</th>
                                        <th>Médecin</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_rdv as $r): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($r['date_rdv'])) ?></td>
                                        <td><?= substr($r['heure_rdv'], 0, 5) ?></td>
                                        <td><?= $r['patient_prenom'] ?> <?= $r['patient_nom'] ?></td>
                                        <td><?= $r['service_nom'] ?></td>
                                        <td><?= $r['medecin_nom'] ?? '-' ?></td>
                                        <td>
                                            <span class="badge <?= $r['statut'] == 'programme' ? 'bg-warning' : 'bg-info' ?>">
                                                <?= $r['statut'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Passés -->
                        <div class="tab-pane fade" id="past">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Service</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($past_rdv as $r): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($r['date_rdv'])) ?></td>
                                        <td><?= $r['patient_prenom'] ?> <?= $r['patient_nom'] ?></td>
                                        <td><?= $r['service_nom'] ?></td>
                                        <td>
                                            <span class="badge <?= $r['statut'] == 'honore' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $r['statut'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Nouveau Rendez-vous -->
    <div class="modal fade" id="rdvModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus"></i> Nouveau rendez-vous</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Patient *</label>
                                <select name="patient_id" class="form-control" required>
                                    <option value="">Sélectionner un patient</option>
                                    <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= $p['prenom'] ?> <?= $p['nom'] ?> (<?= $p['code_patient_unique'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Service *</label>
                                <select name="service_id" class="form-control" required>
                                    <option value="">Sélectionner un service</option>
                                    <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Médecin</label>
                                <select name="medecin_id" class="form-control">
                                    <option value="">Non spécifié</option>
                                    <?php foreach ($medecins as $m): ?>
                                    <option value="<?= $m['id'] ?>">Dr. <?= $m['prenom'] ?> <?= $m['nom'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="fw-bold">Date *</label>
                                <input type="date" name="date_rdv" class="form-control" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="fw-bold">Heure *</label>
                                <input type="time" name="heure_rdv" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">Motif</label>
                            <input type="text" name="motif" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" name="save_rdv" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
