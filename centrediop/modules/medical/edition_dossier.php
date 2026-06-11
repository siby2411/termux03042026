<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Accessible à tous les utilisateurs connectés
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$message = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_patient = $_POST['code_patient'] ?? '';
    
    if (!empty($code_patient)) {
        // Rechercher le patient par son code
        $stmt = $db->prepare("SELECT * FROM patients WHERE code_patient_unique = ? OR numero_patient = ?");
        $stmt->execute([$code_patient, $code_patient]);
        $patient = $stmt->fetch();
        
        if ($patient) {
            // Mettre à jour le token avec ce patient (si la fonction existe)
            if (function_exists('updateTokenPatient')) {
                updateTokenPatient($patient['id']);
            }
            
            // Rediriger vers le dossier du patient
            header('Location: /modules/medecin/dossier.php?patient_id=' . $patient['id']);
            exit();
        } else {
            $error = "Aucun patient trouvé avec ce code";
        }
    } else {
        $error = "Veuillez saisir un code patient";
    }
}

// Statistiques pour l'affichage
$stats = [
    'total_patients' => $db->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    'total_consultations' => $db->query("SELECT COUNT(*) FROM consultations")->fetchColumn(),
    'total_rdv' => $db->query("SELECT COUNT(*) FROM rendez_vous WHERE date_rdv >= CURDATE()")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Édition du Dossier Médical - Omega Informatique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header-banner {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .banner-title {
            color: #667eea;
            font-weight: bold;
            font-size: 1.8em;
        }
        
        .banner-subtitle {
            color: #764ba2;
            font-size: 1.2em;
        }
        
        .main-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 30px;
        }
        
        .stat-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #666;
        }
        
        .code-input {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            font-size: 1.2em;
            text-align: center;
            letter-spacing: 2px;
            transition: all 0.3s;
        }
        
        .code-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .user-info {
            background: #e8f4fd;
            border-radius: 50px;
            padding: 10px 20px;
            display: inline-block;
        }
        
        .recent-patients {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- Bannière Omega Informatique -->
    <div class="header-banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="banner-title">
                        <i class="fas fa-microchip me-2"></i>OMEGA INFORMATIQUE
                    </div>
                    <div class="banner-subtitle">
                        JUMTO WER SOLUTION DESIGN
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="user-info">
                        <i class="fas fa-user-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?>
                        (<?= $_SESSION['user_role'] ?>)
                        <a href="/logout.php" class="ms-3 text-danger">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="main-card">
            <h2 class="text-center mb-4">
                <i class="fas fa-edit text-primary me-2"></i>
                Édition du Dossier Médical
            </h2>
            
            <p class="text-center text-muted mb-4">
                Entrez le code patient pour accéder à son dossier complet
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label fw-bold">Code Patient</label>
                    <input type="text" 
                           name="code_patient" 
                           class="form-control code-input" 
                           placeholder="PAT-000001" 
                           required 
                           autofocus>
                    <div class="form-text text-center mt-2">
                        Exemple: PAT-000001, PAT-000002
                    </div>
                </div>
                
                <button type="submit" class="btn-edit">
                    <i class="fas fa-folder-open me-2"></i>
                    Accéder au dossier
                </button>
            </form>
            
            <!-- Derniers patients consultés -->
            <?php
            // Récupérer les 5 derniers patients consultés par l'utilisateur
            if ($_SESSION['user_role'] == 'medecin') {
                $stmt = $db->prepare("
                    SELECT DISTINCT p.*, MAX(c.date_consultation) as derniere_consult
                    FROM patients p
                    JOIN consultations c ON p.id = c.patient_id
                    WHERE c.medecin_id = ?
                    GROUP BY p.id
                    ORDER BY derniere_consult DESC
                    LIMIT 5
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $recent = $stmt->fetchAll();
                
                if (!empty($recent)): ?>
                <div class="recent-patients">
                    <h6 class="mb-3">Vos derniers patients</h6>
                    <?php foreach ($recent as $p): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?= $p['prenom'] ?> <?= $p['nom'] ?></strong>
                                <br>
                                <small class="text-muted"><?= $p['code_patient_unique'] ?></small>
                            </div>
                            <a href="dossier.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; 
            } ?>
            
            <!-- Statistiques globales -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['total_patients'] ?></div>
                    <div class="stat-label">Patients</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['total_consultations'] ?></div>
                    <div class="stat-label">Consultations</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['total_rdv'] ?></div>
                    <div class="stat-label">RDV à venir</div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="/modules/<?= $_SESSION['user_role'] ?>/dashboard.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Retour au dashboard
                </a>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="text-center text-white mt-4">
            <small>
                <i class="fas fa-copyright me-1"></i> 2026 Omega Informatique - Jumto Wer Solution Design
                <br>
                Version 2.0 - Tous droits réservés
            </small>
        </div>
    </div>
</body>
</html>
