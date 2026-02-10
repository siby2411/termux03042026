<?php
session_start();
// Assurez-vous que ces fichiers existent et que la connexion PDO est établie via 'config/database.php'
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

// Fonction pour générer un QR code
function genererQRCode($matricule, $nom, $prenom) {
    $data = "EMP:$matricule|$nom|$prenom";
    $chemin = "qrcodes/".$matricule.".png";
    
    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($data)
        ->encoding(new Encoding('UTF-8'))
        ->size(200)
        ->margin(10)
        ->errorCorrectionLevel(ErrorCorrectionLevel::High)
        ->build();
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists('qrcodes')) {
        mkdir('qrcodes', 0777, true);
    }
    
    $result->saveToFile($chemin);
    return $chemin;
}

// ----------------------------------------------------
// CORRECTION APPLIQUÉE À LA LIGNE 35
// Vérifie si $_POST['action'] existe avant d'y accéder.
// ----------------------------------------------------
// Traitement du formulaire d'ajout d'employé
if (isset($_POST['action']) && $_POST['action'] == 'ajouter_employe') {
    $matricule = $_POST['matricule'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $poste = $_POST['poste'];
    $taux_horaire = $_POST['taux_horaire'];
    $date_embauche = $_POST['date_embauche'];
    
    // Générer le QR code
    $qr_code = genererQRCode($matricule, $nom, $prenom);
    
    $stmt = $pdo->prepare("INSERT INTO employes (matricule, nom, prenom, poste, taux_horaire, date_embauche, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$matricule, $nom, $prenom, $poste, $taux_horaire, $date_embauche, $qr_code]);
    
    $_SESSION['message'] = "Employé ajouté avec succès!";
    header("Location: index.php");
    exit;
}

// ----------------------------------------------------
// CORRECTION APPLIQUÉE À LA LIGNE 55
// Vérifie si $_POST['action'] existe avant d'y accéder.
// ----------------------------------------------------
// Traitement du pointage
if (isset($_POST['action']) && $_POST['action'] == 'pointage') {
    $qr_data = $_POST['qr_data'];
    
    // Décoder les données du QR code
    $parts = explode('|', str_replace('EMP:', '', $qr_data));
    $matricule = $parts[0];
    
    // Récupérer l'employé
    $stmt = $pdo->prepare("SELECT * FROM employes WHERE matricule = ?");
    $stmt->execute([$matricule]);
    $employe = $stmt->fetch();
    
    if ($employe) {
        $aujourdhui = date('Y-m-d');
        $maintenant = date('H:i:s');
        
        // Vérifier le dernier pointage
        $stmt = $pdo->prepare("SELECT * FROM pointages WHERE employe_id = ? AND date_pointage = ? ORDER BY heure_pointage DESC LIMIT 1");
        $stmt->execute([$employe['id'], $aujourdhui]);
        $dernier_pointage = $stmt->fetch();
        
        $type_pointage = 'entree';
        if ($dernier_pointage && $dernier_pointage['type_pointage'] == 'entree') {
            $type_pointage = 'sortie';
        }
        
        // Enregistrer le pointage
        $stmt = $pdo->prepare("INSERT INTO pointages (employe_id, type_pointage, date_pointage, heure_pointage) VALUES (?, ?, ?, ?)");
        $stmt->execute([$employe['id'], $type_pointage, $aujourdhui, $maintenant]);
        
        // Mettre à jour ou créer la session de travail
        if ($type_pointage == 'sortie') {
            // Calculer la durée
            $duree = strtotime($maintenant) - strtotime($dernier_pointage['heure_pointage']);
            $duree_minutes = floor($duree / 60);
            $remuneration = ($duree_minutes / 60) * $employe['taux_horaire'];
            
            $stmt = $pdo->prepare("INSERT INTO sessions_travail (employe_id, date_session, heure_entree, heure_sortie, duree_minutes, remuneration_jour) 
                                   VALUES (?, ?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE heure_sortie = ?, duree_minutes = ?, remuneration_jour = ?");
            $stmt->execute([
                $employe['id'], $aujourdhui, $dernier_pointage['heure_pointage'], $maintenant, $duree_minutes, $remuneration,
                $maintenant, $duree_minutes, $remuneration
            ]);
        }
        
        $_SESSION['message'] = "Pointage $type_pointage enregistré pour " . $employe['prenom'] . " " . $employe['nom'];
    } else {
        $_SESSION['erreur'] = "Employé non trouvé!";
    }
    
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omega Informatique - Gestion des Pointages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-header-custom {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
        }
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .qrcode-scanner {
            border: 2px dashed #dee2e6;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-fingerprint"></i> Omega Informatique
            </a>
            <span class="navbar-text">Gestion des Pointages</span>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['erreur'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['erreur'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['erreur']); ?>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM employes WHERE statut = 'actif'");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </h4>
                                <p>Employés Actifs</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM pointages WHERE date_pointage = CURDATE() AND type_pointage = 'entree'");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </h4>
                                <p>Présents Aujourd'hui</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM pointages WHERE date_pointage = CURDATE() AND type_pointage = 'entree' AND heure_pointage > '08:15:00'");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </h4>
                                <p>Retards Aujourd'hui</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(DISTINCT employe_id) FROM pointages WHERE date_pointage = CURDATE() AND type_pointage = 'entree'");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </h4>
                                <p>Pointages Entrée</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-sign-in-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h4 class="mb-0"><i class="fas fa-qrcode"></i> Pointage QR Code</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="formPointage">
                            <input type="hidden" name="action" value="pointage">
                            <div class="mb-3">
                                <label class="form-label">Scanner QR Code</label>
                                <div class="qrcode-scanner bg-light rounded p-3 text-center" id="scannerArea">
                                    <div>
                                        <i class="fas fa-camera fa-3x text-muted mb-2"></i>
                                        <p class="text-muted">Cliquez pour scanner le QR Code</p>
                                    </div>
                                </div>
                                <input type="hidden" name="qr_data" id="qrData">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary-custom btn-lg">
                                    <i class="fas fa-check-circle"></i> Enregistrer le Pointage
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Pointages Aujourd'hui</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Employé</th>
                                        <th>Heure Entrée</th>
                                        <th>Heure Sortie</th>
                                        <th>Durée</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("
                                        SELECT e.nom, e.prenom, 
                                               MIN(CASE WHEN p.type_pointage = 'entree' THEN p.heure_pointage END) as entree,
                                               MAX(CASE WHEN p.type_pointage = 'sortie' THEN p.heure_pointage END) as sortie,
                                               s.duree_minutes
                                        FROM pointages p
                                        JOIN employes e ON p.employe_id = e.id
                                        LEFT JOIN sessions_travail s ON s.employe_id = e.id AND s.date_session = CURDATE()
                                        WHERE p.date_pointage = CURDATE()
                                        GROUP BY e.id
                                        ORDER BY entree DESC
                                    ");
                                    while ($row = $stmt->fetch()):
                                        $duree = $row['duree_minutes'] ? 
                                            floor($row['duree_minutes'] / 60) . 'h' . ($row['duree_minutes'] % 60) : 
                                            '-';
                                    ?>
                                    <tr>
                                        <td><?= $row['prenom'] ?> <?= $row['nom'] ?></td>
                                        <td><?= $row['entree'] ?></td>
                                        <td><?= $row['sortie'] ?: '-' ?></td>
                                        <td><?= $duree ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h4 class="mb-0"><i class="fas fa-user-plus"></i> Ajouter un Employé</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="ajouter_employe">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Matricule</label>
                                        <input type="text" class="form-control" name="matricule" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Date d'Embauche</label>
                                        <input type="date" class="form-control" name="date_embauche" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nom</label>
                                        <input type="text" class="form-control" name="nom" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Prénom</label>
                                        <input type="text" class="form-control" name="prenom" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Poste</label>
                                        <input type="text" class="form-control" name="poste" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Taux Horaire (€)</label>
                                        <input type="number" step="0.01" class="form-control" name="taux_horaire" required>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="fas fa-plus-circle"></i> Ajouter l'Employé
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Liste des Employés</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Nom</th>
                                        <th>Poste</th>
                                        <th>QR Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM employes WHERE statut = 'actif' ORDER BY nom, prenom");
                                    while ($employe = $stmt->fetch()):
                                    ?>
                                    <tr>
                                        <td><?= $employe['matricule'] ?></td>
                                        <td><?= $employe['prenom'] ?> <?= $employe['nom'] ?></td>
                                        <td><?= $employe['poste'] ?></td>
                                        <td>
                                            <?php if ($employe['qr_code']): ?>
                                                <a href="<?= $employe['qr_code'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header card-header-custom">
                <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Rapports Mensuels</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Mois</th>
                                <th>Jours Travaillés</th>
                                <th>Heures Total</th>
                                <th>Rémunération</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT e.nom, e.prenom, 
                                       DATE_FORMAT(s.date_session, '%Y-%m') as mois,
                                       COUNT(DISTINCT s.date_session) as jours_travailles,
                                       SUM(s.duree_minutes) as total_minutes,
                                       SUM(s.remuneration_jour) as total_remuneration
                                FROM sessions_travail s
                                JOIN employes e ON s.employe_id = e.id
                                WHERE s.heure_sortie IS NOT NULL
                                GROUP BY e.id, mois
                                ORDER BY mois DESC, e.nom, e.prenom
                            ");
                            while ($row = $stmt->fetch()):
                                $heures = floor($row['total_minutes'] / 60);
                                $minutes = $row['total_minutes'] % 60;
                            ?>
                            <tr>
                                <td><?= $row['prenom'] ?> <?= $row['nom'] ?></td>
                                <td><?= $row['mois'] ?></td>
                                <td><?= $row['jours_travailles'] ?></td>
                                <td><?= $heures ?>h<?= sprintf('%02d', $minutes) ?></td>
                                <td><?= number_format($row['total_remuneration'], 2) ?> €</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="scannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Scanner QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Simulation de scan de QR Code. Sélectionnez un employé :</p>
                    <select class="form-select" id="employeSelect">
                        <option value="">Sélectionnez un employé</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM employes WHERE statut = 'actif' ORDER BY nom, prenom");
                        while ($employe = $stmt->fetch()):
                        ?>
                        <option value="EMP:<?= $employe['matricule'] ?>|<?= $employe['nom'] ?>|<?= $employe['prenom'] ?>">
                            <?= $employe['prenom'] ?> <?= $employe['nom'] ?> (<?= $employe['matricule'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="validerScan">Valider</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simulation du scanner QR Code
        document.getElementById('scannerArea').addEventListener('click', function() {
            var scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));
            scannerModal.show();
        });

        document.getElementById('validerScan').addEventListener('click', function() {
            var employeSelect = document.getElementById('employeSelect');
            if (employeSelect.value) {
                document.getElementById('qrData').value = employeSelect.value;
                var scannerModal = bootstrap.Modal.getInstance(document.getElementById('scannerModal'));
                scannerModal.hide();
                
                // Soumettre automatiquement le formulaire
                document.getElementById('formPointage').submit();
            }
        });
    </script>
</body>
</html>
