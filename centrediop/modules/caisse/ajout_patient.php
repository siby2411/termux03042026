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

// Générer un code patient unique
function generatePatientCode($db) {
    $prefix = 'PAT-' . date('Y');
    $stmt = $db->query("SELECT COUNT(*) FROM patients WHERE code_patient_unique LIKE '$prefix%'");
    $count = $stmt->fetchColumn() + 1;
    return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? null;
    $adresse = $_POST['adresse'] ?? '';
    $email = $_POST['email'] ?? '';
    $sexe = $_POST['sexe'] ?? 'N'; // Valeur par défaut si non spécifié
    $lieu_naissance = $_POST['lieu_naissance'] ?? '';
    
    $code_patient = generatePatientCode($db);
    
    // Requête avec tous les champs obligatoires
    $insert = $db->prepare("
        INSERT INTO patients (
            code_patient_unique, 
            numero_patient, 
            nom, 
            prenom, 
            telephone,
            date_naissance, 
            adresse, 
            email, 
            sexe,
            lieu_naissance,
            created_by, 
            created_at
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?,
            ?, NOW()
        )
    ");
    
    try {
        $insert->execute([
            $code_patient, 
            $code_patient, 
            $nom, 
            $prenom, 
            $telephone,
            $date_naissance, 
            $adresse, 
            $email, 
            $sexe,
            $lieu_naissance,
            $_SESSION['user_id']
        ]);
        $patient_id = $db->lastInsertId();
        $message = "✅ Patient créé avec succès! Code: $code_patient";
    } catch (Exception $e) {
        $error = "❌ Erreur: " . $e->getMessage();
    }
}

// Récupérer les statistiques du caissier
$stats = [
    'patients_crees' => $db->prepare("SELECT COUNT(*) FROM patients WHERE created_by = ?"),
    'paiements_ajd' => $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = CURDATE()"),
    'montant_ajd' => $db->prepare("SELECT COALESCE(SUM(montant_paye), 0) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = CURDATE()")
];

$stats['patients_crees']->execute([$_SESSION['user_id']]);
$stats['paiements_ajd']->execute([$_SESSION['user_id']]);
$stats['montant_ajd']->execute([$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout Patient - <?= $caissier['prenom'] ?> <?= $caissier['nom'] ?></title>
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
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h4><i class="fas fa-user-plus"></i> Ajout Patient - <?= $caissier['prenom'] ?> <?= $caissier['nom'] ?></h4>
            <a href="dashboard.php" class="btn btn-sm btn-light">Retour</a>
        </div>
    </div>

    <div class="container">
        <!-- Statistiques du caissier -->
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['patients_crees']->fetchColumn() ?></div>
                    <div>Patients créés</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['paiements_ajd']->fetchColumn() ?></div>
                    <div>Paiements aujourd'hui</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($stats['montant_ajd']->fetchColumn(), 0, ',', ' ') ?> F</div>
                    <div>Montant du jour</div>
                </div>
            </div>
        </div>

        <!-- Formulaire d'ajout -->
        <div class="form-card">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="required">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="required">Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="required">Téléphone</label>
                        <input type="tel" name="telephone" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="required">Sexe</label>
                        <select name="sexe" class="form-control" required>
                            <option value="">Sélectionnez le sexe</option>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Lieu de naissance</label>
                        <input type="text" name="lieu_naissance" class="form-control">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label>Adresse</label>
                    <textarea name="adresse" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                
                <div class="text-muted small mb-3">
                    <span class="text-danger">*</span> Champs obligatoires
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-2"></i> Enregistrer le patient
                </button>
            </form>
        </div>

        <!-- Derniers patients créés par ce caissier -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-history"></i> Vos derniers patients
            </div>
            <div class="card-body">
                <?php
                $recent = $db->prepare("
                    SELECT * FROM patients 
                    WHERE created_by = ? 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ");
                $recent->execute([$_SESSION['user_id']]);
                $patients = $recent->fetchAll();
                
                if (empty($patients)): ?>
                    <p class="text-muted">Aucun patient créé pour le moment</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Téléphone</th>
                                    <th>Sexe</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $p): ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= $p['code_patient_unique'] ?></span></td>
                                    <td><?= htmlspecialchars($p['nom']) ?></td>
                                    <td><?= htmlspecialchars($p['prenom']) ?></td>
                                    <td><?= htmlspecialchars($p['telephone'] ?? 'N/A') ?></td>
                                    <td><?= $p['sexe'] == 'M' ? '👨 Masculin' : ($p['sexe'] == 'F' ? '👩 Féminin' : '-') ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
