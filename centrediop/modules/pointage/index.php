<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Seul l'admin peut accéder au pointage
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qr_code = $_POST['qr_code'] ?? '';
    
    if ($qr_code) {
        $stmt = $pdo->prepare("CALL pointer_personnel(?)");
        $stmt->execute([$qr_code]);
        $result = $stmt->fetch();
        
        if ($result && $result['message'] != 'QR Code invalide') {
            $message = $result['message'] . ' - ' . $result['detail'];
        } else {
            $error = 'QR Code invalide';
        }
    }
}

// Récupérer les pointages du jour
$pointages = $pdo->query("
    SELECT p.*, u.prenom, u.nom, u.role, u.code_personnel
    FROM pointages p
    JOIN users u ON p.user_id = u.id
    WHERE p.date_pointage = CURDATE()
    ORDER BY p.heure_arrivee
")->fetchAll();

$non_pointes = $pdo->query("
    SELECT u.id, u.prenom, u.nom, u.role, u.code_personnel
    FROM users u
    WHERE u.actif = 1 
    AND u.role IN ('medecin', 'sagefemme', 'caissier', 'pharmacien')
    AND NOT EXISTS (
        SELECT 1 FROM pointages p 
        WHERE p.user_id = u.id AND p.date_pointage = CURDATE()
    )
    ORDER BY u.role, u.nom
")->fetchAll();

$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_pointes,
        SUM(CASE WHEN heure_depart IS NOT NULL THEN 1 ELSE 0 END) as deja_parted,
        AVG(TIMESTAMPDIFF(HOUR, heure_arrivee, COALESCE(heure_depart, NOW()))) as heures_moyennes
    FROM pointages
    WHERE date_pointage = CURDATE()
")->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pointage - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Administrateur</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="../patients/liste.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="../patients/form.php"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../consultation/form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <li><a href="../paiements/liste.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../paiements/form.php"><i class="fas fa-plus-circle"></i> Nouveau paiement</a></li>
                        <li><a href="index.php" class="active"><i class="fas fa-clock"></i> Pointage</a></li>
                        <li><a href="../statistiques/index.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-clock"></i> Gestion des pointages</h2>
                
                <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="kpi-card">
                            <div class="kpi-value"><?= $stats['total_pointes'] ?></div>
                            <div class="kpi-label">Personnel pointé</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <div class="kpi-value"><?= $stats['deja_parted'] ?></div>
                            <div class="kpi-label">Déjà partis</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <div class="kpi-value"><?= round($stats['heures_moyennes']) ?>h</div>
                            <div class="kpi-label">Moyenne</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-check-circle"></i> Pointages du jour</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Personnel</th>
                                        <th>Rôle</th>
                                        <th>Arrivée</th>
                                        <th>Départ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pointages as $p): ?>
                                    <tr>
                                        <td><?= $p['prenom'] ?> <?= $p['nom'] ?></td>
                                        <td><?= $p['role'] ?></td>
                                        <td><?= $p['heure_arrivee'] ?></td>
                                        <td><?= $p['heure_depart'] ?? '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-exclamation-triangle"></i> Non pointés</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Personnel</th>
                                        <th>Rôle</th>
                                        <th>Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($non_pointes as $np): ?>
                                    <tr>
                                        <td><?= $np['prenom'] ?> <?= $np['nom'] ?></td>
                                        <td><?= $np['role'] ?></td>
                                        <td><?= $np['code_personnel'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card mt-4">
                    <h5 class="mb-3"><i class="fas fa-qrcode"></i> Scanner QR Code</h5>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" name="qr_code" class="form-control" placeholder="Scanner le QR code" autofocus>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Pointer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
