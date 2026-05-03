<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'caissier'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$message = '';
$error = '';
$consultation_id = $_GET['consultation_id'] ?? null;
$patient_id = $_GET['patient_id'] ?? null;
$consultation = null;
$patient = null;

if ($consultation_id) {
    // Récupérer les détails de la consultation avec le montant total des actes
    $stmt = $pdo->prepare("
        SELECT c.*, p.prenom, p.nom, p.code_patient_unique,
               s.name as service_nom,
               COALESCE(SUM(ca.prix_applique), 0) as total_actes
        FROM consultations c
        JOIN patients p ON c.patient_id = p.id
        JOIN services s ON c.service_id = s.id
        LEFT JOIN consultation_actes ca ON c.id = ca.consultation_id
        WHERE c.id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$consultation_id]);
    $consultation = $stmt->fetch();
}

if ($patient_id) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_paiement'])) {
    try {
        $pdo->beginTransaction();
        
        $numero_facture = 'FACT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Vérifier si un paiement existe déjà pour cette consultation
        if ($_POST['consultation_id']) {
            $check = $pdo->prepare("SELECT id FROM paiements WHERE consultation_id = ?");
            $check->execute([$_POST['consultation_id']]);
            if ($check->fetch()) {
                throw new Exception("Un paiement existe déjà pour cette consultation");
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO paiements (
                numero_facture, patient_id, consultation_id, caissier_id,
                montant_total, montant_paye, mode_paiement, statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'paye')
        ");
        $stmt->execute([
            $numero_facture,
            $_POST['patient_id'],
            $_POST['consultation_id'] ?: null,
            $_SESSION['user_id'],
            $_POST['montant_total'],
            $_POST['montant_paye'],
            $_POST['mode_paiement']
        ]);
        
        $pdo->commit();
        $message = "Paiement enregistré avec succès. Facture: $numero_facture";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur: " . $e->getMessage();
    }
}

// Liste des impayés avec montant calculé
$impayes = $pdo->query("
    SELECT c.id, c.date_consultation, 
           p.id as patient_id, p.prenom, p.nom, p.code_patient_unique,
           COALESCE(SUM(ca.prix_applique), 0) as total
    FROM consultations c
    JOIN patients p ON c.patient_id = p.id
    LEFT JOIN consultation_actes ca ON c.id = ca.consultation_id
    LEFT JOIN paiements pa ON c.id = pa.consultation_id
    WHERE pa.id IS NULL
    GROUP BY c.id
    ORDER BY c.date_consultation DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau paiement</title>
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
                        <small><?= ucfirst($_SESSION['user_role']) ?></small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../<?= $_SESSION['user_role'] ?>/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="../patients/liste.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="../patients/form.php"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../consultation/form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <li><a href="liste.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="form.php" class="active"><i class="fas fa-plus-circle"></i> Nouveau paiement</a></li>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <?php endif; ?>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-credit-card"></i> Nouveau paiement</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-exclamation-triangle"></i> Impayés récents</h5>
                            <?php if (empty($impayes)): ?>
                                <p class="text-muted">Aucun impayé</p>
                            <?php else: ?>
                                <?php foreach ($impayes as $i): ?>
                                <div class="border p-2 mb-2">
                                    <strong><?= $i['prenom'] ?> <?= $i['nom'] ?></strong><br>
                                    <small><?= $i['code_patient_unique'] ?></small><br>
                                    <span class="badge bg-danger"><?= number_format($i['total'], 0, ',', ' ') ?> FCFA</span><br>
                                    <small>Consultation du <?= date('d/m/Y', strtotime($i['date_consultation'])) ?></small><br>
                                    <a href="?consultation_id=<?= $i['id'] ?>" class="btn btn-sm btn-success mt-2 w-100">Régler</a>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <?php if ($consultation): ?>
                        <div class="dashboard-card">
                            <h5 class="mb-3">Facturation consultation</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Patient:</strong> <?= $consultation['prenom'] ?> <?= $consultation['nom'] ?></p>
                                    <p><strong>Code:</strong> <?= $consultation['code_patient_unique'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($consultation['date_consultation'])) ?></p>
                                    <p><strong>Service:</strong> <?= $consultation['service_nom'] ?></p>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6 class="mb-0">Montant total: <strong><?= number_format($consultation['total_actes'], 0, ',', ' ') ?> FCFA</strong></h6>
                            </div>
                            
                            <form method="POST">
                                <input type="hidden" name="patient_id" value="<?= $consultation['patient_id'] ?>">
                                <input type="hidden" name="consultation_id" value="<?= $consultation['id'] ?>">
                                <input type="hidden" name="montant_total" value="<?= $consultation['total_actes'] ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Montant payé</label>
                                        <input type="number" name="montant_paye" class="form-control" 
                                               value="<?= $consultation['total_actes'] ?>" required step="100">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Mode de paiement</label>
                                        <select name="mode_paiement" class="form-control">
                                            <option value="especes">Espèces</option>
                                            <option value="carte">Carte bancaire</option>
                                            <option value="cheque">Chèque</option>
                                            <option value="mobile_money">Mobile Money (Orange Money, Wave)</option>
                                            <option value="assurance">Assurance</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <button type="submit" name="save_paiement" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-check"></i> Enregistrer le paiement
                                </button>
                            </form>
                        </div>
                        <?php elseif ($patient): ?>
                        <div class="dashboard-card">
                            <h5 class="mb-3">Patient: <?= $patient['prenom'] ?> <?= $patient['nom'] ?></h5>
                            <p class="text-muted">Sélectionnez une consultation impayée dans la liste pour procéder au paiement.</p>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Sélectionnez une consultation impayée dans la liste de gauche.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
