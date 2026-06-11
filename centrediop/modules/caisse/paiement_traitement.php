<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'caissier') {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Récupérer le caissier connecté
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$caissier = $stmt->fetch();

// Liste des traitements avec leurs prix
$traitements = [
    ['code' => 'CONS-GEN', 'nom' => 'Consultation générale', 'service' => 'Médecine générale', 'prix' => 5000],
    ['code' => 'CONS-PED', 'nom' => 'Consultation pédiatrie', 'service' => 'Pédiatrie', 'prix' => 7000],
    ['code' => 'CONS-CARD', 'nom' => 'Consultation cardiologie', 'service' => 'Cardiologie', 'prix' => 10000],
    ['code' => 'CONS-DERM', 'nom' => 'Consultation dermatologie', 'service' => 'Dermatologie', 'prix' => 7000],
    ['code' => 'CONS-OPHT', 'nom' => 'Consultation ophtalmologie', 'service' => 'Ophtalmologie', 'prix' => 8000],
    ['code' => 'ECHO-CARD', 'nom' => 'Échographie cardiaque', 'service' => 'Cardiologie', 'prix' => 35000],
    ['code' => 'ECG', 'nom' => 'Électrocardiogramme', 'service' => 'Cardiologie', 'prix' => 15000],
    ['code' => 'BIOPSIE', 'nom' => 'Biopsie cutanée', 'service' => 'Dermatologie', 'prix' => 20000],
    ['code' => 'FOND-OEIL', 'nom' => "Fond d'œil", 'service' => 'Ophtalmologie', 'prix' => 12000],
    ['code' => 'HBA1C', 'nom' => 'Hémoglobine glyquée', 'service' => 'Diabétologie', 'prix' => 10000],
];

// Recherche de patient
$patient = null;
if (isset($_GET['code']) || isset($_POST['code_patient'])) {
    $code = $_GET['code'] ?? $_POST['code_patient'];
    $stmt = $db->prepare("SELECT * FROM patients WHERE code_patient_unique = ? OR numero_patient = ?");
    $stmt->execute([$code, $code]);
    $patient = $stmt->fetch();
}

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_paiement'])) {
    $patient_id = $_POST['patient_id'] ?? 0;
    $traitement_code = $_POST['traitement'] ?? '';
    $mode_paiement = $_POST['mode_paiement'] ?? 'especes';
    
    // Trouver le traitement sélectionné
    $selected_traitement = null;
    foreach ($traitements as $t) {
        if ($t['code'] == $traitement_code) {
            $selected_traitement = $t;
            break;
        }
    }
    
    if ($selected_traitement && $patient_id) {
        // Générer un numéro de facture unique
        $prefix = 'FACT-' . date('Ymd');
        $stmt = $db->query("SELECT COUNT(*) FROM paiements WHERE numero_facture LIKE '$prefix%'");
        $count = $stmt->fetchColumn() + 1;
        $numero_facture = $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Insertion avec observations
        $insert = $db->prepare("
            INSERT INTO paiements (
                numero_facture, patient_id, caissier_id, 
                montant_total, montant_paye, montant_restant,
                mode_paiement, statut, date_paiement, observations
            ) VALUES (?, ?, ?, ?, ?, 0, ?, 'paye', NOW(), ?)
        ");
        
        $observations = $selected_traitement['nom'] . ' - ' . $selected_traitement['service'];
        
        try {
            $insert->execute([
                $numero_facture,
                $patient_id, 
                $_SESSION['user_id'], 
                $selected_traitement['prix'], 
                $selected_traitement['prix'], 
                $mode_paiement,
                $observations
            ]);
            $message = "✅ Paiement enregistré: " . number_format($selected_traitement['prix'], 0, ',', ' ') . " FCFA - Facture: $numero_facture";
            
            // Recharger le patient
            $stmt = $db->prepare("SELECT * FROM patients WHERE id = ?");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch();
        } catch (Exception $e) {
            $error = "❌ Erreur: " . $e->getMessage();
        }
    }
}

// Statistiques du caissier
$stats = [
    'total_paiements' => $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ?"),
    'total_montant' => $db->prepare("SELECT COALESCE(SUM(montant_paye), 0) FROM paiements WHERE caissier_id = ?"),
    'paiements_ajd' => $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = CURDATE()"),
    'montant_ajd' => $db->prepare("SELECT COALESCE(SUM(montant_paye), 0) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = CURDATE()")
];

$stats['total_paiements']->execute([$_SESSION['user_id']]);
$stats['total_montant']->execute([$_SESSION['user_id']]);
$stats['paiements_ajd']->execute([$_SESSION['user_id']]);
$stats['montant_ajd']->execute([$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Traitement - <?= htmlspecialchars($caissier['prenom'] . ' ' . $caissier['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px;
        }
        .container { padding: 20px; }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #28a745;
        }
        .stats-number {
            font-size: 2em;
            font-weight: bold;
            color: #1e3c72;
        }
        .patient-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .traitement-item {
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .traitement-item:hover {
            transform: translateX(5px);
            border-color: #667eea;
            background: #e8f0fe;
        }
        .traitement-item.selected {
            border-color: #28a745;
            background: #d4edda;
        }
        .prix-badge {
            font-size: 1.2em;
            font-weight: bold;
            color: #28a745;
        }
        .btn-payer {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            width: 100%;
            font-weight: bold;
            font-size: 1.2em;
        }
        .btn-payer:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h4><i class="fas fa-credit-card"></i> Paiement Traitement - <?= htmlspecialchars($caissier['prenom'] . ' ' . $caissier['nom']) ?></h4>
            <a href="dashboard.php" class="btn btn-sm btn-light">Retour</a>
        </div>
    </div>

    <div class="container">
        <!-- Statistiques -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total_paiements']->fetchColumn() ?></div>
                    <div>Total paiements</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($stats['total_montant']->fetchColumn(), 0, ',', ' ') ?> F</div>
                    <div>Total encaissé</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['paiements_ajd']->fetchColumn() ?></div>
                    <div>Aujourd'hui</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($stats['montant_ajd']->fetchColumn(), 0, ',', ' ') ?> F</div>
                    <div>Montant du jour</div>
                </div>
            </div>
        </div>

        <!-- Recherche patient -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-search"></i> Rechercher un patient
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-10">
                        <input type="text" name="code" class="form-control" 
                               placeholder="Code patient (ex: PAT-000001)" 
                               value="<?= htmlspecialchars($_GET['code'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($patient): ?>
            <!-- Informations patient -->
            <div class="patient-card">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h4>
                        <p>
                            <i class="fas fa-id-card"></i> Code: <?= $patient['code_patient_unique'] ?><br>
                            <i class="fas fa-phone"></i> Tél: <?= htmlspecialchars($patient['telephone'] ?? 'N/A') ?><br>
                            <i class="fas fa-venus-mars"></i> Sexe: <?= $patient['sexe'] == 'M' ? 'Masculin' : 'Féminin' ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <i class="fas fa-user-injured fa-4x" style="opacity: 0.3;"></i>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <!-- Liste des traitements -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-list"></i> Sélectionner un traitement
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($traitements as $t): ?>
                        <div class="col-md-6 traitement-item" onclick="selectionnerTraitement('<?= $t['code'] ?>', <?= $t['prix'] ?>, '<?= addslashes($t['nom']) ?>')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= $t['nom'] ?></strong><br>
                                    <small class="text-muted"><?= $t['service'] ?></small>
                                </div>
                                <div class="prix-badge"><?= number_format($t['prix'], 0, ',', ' ') ?> F</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Formulaire de paiement -->
                    <form method="POST" id="paiementForm" class="mt-4">
                        <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                        <input type="hidden" name="code_patient" value="<?= $patient['code_patient_unique'] ?>">
                        <input type="hidden" name="traitement" id="selected_traitement">
                        <input type="hidden" name="montant" id="selected_montant">

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Traitement sélectionné</label>
                                <input type="text" id="traitement_display" class="form-control" readonly placeholder="Aucun traitement sélectionné">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Montant</label>
                                <input type="text" id="montant_display" class="form-control" readonly placeholder="0 F">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Mode de paiement</label>
                                <select name="mode_paiement" class="form-control">
                                    <option value="especes">💵 Espèces</option>
                                    <option value="carte">💳 Carte bancaire</option>
                                    <option value="mobile_money">📱 Mobile Money</option>
                                    <option value="cheque">📝 Chèque</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="valider_paiement" class="btn-payer mt-4" id="btnValider" disabled>
                            <i class="fas fa-check-circle"></i> Valider le paiement
                        </button>
                    </form>
                </div>
            </div>
        <?php elseif (isset($_GET['code'])): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Aucun patient trouvé avec le code "<?= htmlspecialchars($_GET['code']) ?>"
            </div>
        <?php endif; ?>
    </div>

    <script>
    function selectionnerTraitement(code, montant, nom) {
        document.getElementById('selected_traitement').value = code;
        document.getElementById('selected_montant').value = montant;
        document.getElementById('traitement_display').value = nom;
        document.getElementById('montant_display').value = montant.toLocaleString('fr-FR') + ' F';
        document.getElementById('btnValider').disabled = false;
        
        // Highlight selected
        document.querySelectorAll('.traitement-item').forEach(el => {
            el.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
    }
    </script>
</body>
</html>
