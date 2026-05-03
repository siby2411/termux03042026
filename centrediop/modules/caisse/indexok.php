<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'caissier') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$message = '';
$error = '';
$token_info = null;

// Récupérer tous les départements/services avec leurs prix
$departements = $pdo->query("
    SELECT id, name, prix_consultation,
           (SELECT COUNT(*) FROM file_attente WHERE service_id = s.id AND statut = 'en_attente') as en_attente
    FROM services s
    WHERE name NOT IN ('Caisse', 'Accueil/Triage')
    ORDER BY name
")->fetchAll();

// Enregistrement nouveau patient avec département
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_patient'])) {
    $numero = 'PAT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $service_id = $_POST['service_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Récupérer le prix du service
        $stmt = $pdo->prepare("SELECT prix_consultation, name FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
        
        // Insérer le patient
        $stmt = $pdo->prepare("
            INSERT INTO patients (numero_patient, prenom, nom, date_naissance, sexe, telephone, adresse) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $numero,
            $_POST['prenom'],
            $_POST['nom'],
            $_POST['date_naissance'],
            $_POST['sexe'],
            $_POST['telephone'],
            $_POST['adresse'] ?? null
        ]);
        $patient_id = $pdo->lastInsertId();
        
        // Créer le dossier médical
        $pdo->prepare("INSERT INTO dossiers_medicaux (patient_id) VALUES (?)")->execute([$patient_id]);
        
        // Calculer la priorité (senior si âge > 60)
        $age = date('Y') - date('Y', strtotime($_POST['date_naissance']));
        $priorite = ($age >= 60) ? 'senior' : 'normal';
        
        // Générer token
        $token = 'TKN' . date('His') . str_pad($patient_id, 3, '0', STR_PAD_LEFT);
        
        // Insérer dans file_attente
        $stmt = $pdo->prepare("
            INSERT INTO file_attente (token, patient_id, service_id, departement, priorite, statut, cree_a)
            VALUES (?, ?, ?, ?, ?, 'en_attente', NOW())
        ");
        $stmt->execute([$token, $patient_id, $service_id, $service['name'], $priorite]);
        
        // Créer la facture automatiquement
        $file_id = $pdo->lastInsertId();
        $numero_facture = 'FACT-' . date('Ymd') . '-' . str_pad($file_id, 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO paiements (numero_facture, patient_id, caissier_id, montant_total, montant_paye, mode_paiement, statut)
            VALUES (?, ?, ?, ?, ?, ?, 'paye')
        ");
        $stmt->execute([
            $numero_facture,
            $patient_id,
            $_SESSION['user_id'],
            $service['prix_consultation'],
            $service['prix_consultation'],
            $_POST['mode_paiement']
        ]);
        
        $pdo->commit();
        
        $message = "✅ Patient enregistré - Paiement de " . number_format($service['prix_consultation'], 0, ',', ' ') . " FCFA - Token: $token";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "❌ Erreur: " . $e->getMessage();
    }
}

// Récupérer la file d'attente
$queue = $pdo->query("
    SELECT f.*, p.prenom, p.nom
    FROM file_attente f
    JOIN patients p ON f.patient_id = p.id
    WHERE f.statut = 'en_attente'
    ORDER BY FIELD(f.priorite, 'urgence', 'senior', 'normal'), f.cree_a ASC
    LIMIT 20
")->fetchAll();

// Statistiques du jour
$stats = $pdo->query("
    SELECT COUNT(*) as total_paiements, SUM(montant_total) as total_recettes
    FROM paiements WHERE DATE(date_paiement) = CURDATE()
")->fetch();

$recent_payments = $pdo->query("
    SELECT p.*, pat.prenom, pat.nom
    FROM paiements p
    JOIN patients pat ON p.patient_id = pat.id
    ORDER BY p.date_paiement DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caisse - Centre Mamadou Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 bg-dark text-white" style="min-height: 100vh;">
                <div class="text-center py-4">
                    <i class="fas fa-hospital fa-3x mb-2"></i>
                    <h5>Centre Mamadou Diop</h5>
                    <small>Caissier</small>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link text-white active bg-primary">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="etat_caisse.php" class="nav-link text-white">
                            <i class="fas fa-chart-line"></i> État de caisse
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/logout.php" class="nav-link text-white text-danger">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-cash-register"></i> Espace Caisse</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Colonne gauche : Nouveau patient avec département -->
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user-plus"></i> Nouveau patient</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="new_patient" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">👤 Prénom</label>
                                        <input type="text" name="prenom" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">👤 Nom</label>
                                        <input type="text" name="nom" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">📅 Date naissance</label>
                                        <input type="date" name="date_naissance" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">⚥ Sexe</label>
                                        <select name="sexe" class="form-control" required>
                                            <option value="">Sélectionner</option>
                                            <option value="M">Masculin</option>
                                            <option value="F">Féminin</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">📞 Téléphone</label>
                                        <input type="text" name="telephone" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">🏥 DÉPARTEMENT / SERVICE</label>
                                        <select name="service_id" class="form-control" required onchange="updatePrix()">
                                            <option value="">-- Choisir un département --</option>
                                            <?php foreach ($departements as $d): ?>
                                            <option value="<?= $d['id'] ?>" 
                                                    data-prix="<?= $d['prix_consultation'] ?>"
                                                    data-attente="<?= $d['en_attente'] ?>">
                                                <?= htmlspecialchars($d['name']) ?> - 
                                                <?= number_format($d['prix_consultation'], 0, ',', ' ') ?> FCFA
                                                <?php if ($d['en_attente'] > 0): ?>
                                                (<?= $d['en_attente'] ?> en attente)
                                                <?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">💰 Montant consultation</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-info text-white">FCFA</span>
                                            <input type="text" id="prix_consultation" class="form-control" readonly value="0">
                                        </div>
                                        <small class="text-muted">Montant automatique selon le département</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">💳 Mode de paiement</label>
                                        <select name="mode_paiement" class="form-control" required>
                                            <option value="especes">💵 Espèces</option>
                                            <option value="carte">💳 Carte bancaire</option>
                                            <option value="mobile_money">📱 Mobile Money</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">📍 Adresse (optionnel)</label>
                                        <textarea name="adresse" class="form-control" rows="2"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-check-circle"></i> Enregistrer et payer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Colonne milieu : File d'attente -->
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0"><i class="fas fa-clock"></i> File d'attente (<?= count($queue) ?>)</h5>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?php if (empty($queue)): ?>
                                    <p class="text-muted">Aucun patient en attente</p>
                                <?php else: ?>
                                    <?php foreach ($queue as $q): ?>
                                    <div class="border p-2 mb-2">
                                        <strong><?= htmlspecialchars($q['token']) ?></strong> - 
                                        <?= htmlspecialchars($q['prenom'] . ' ' . $q['nom']) ?>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- État de caisse -->
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-line"></i> État de caisse du jour</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h3><?= $stats['total_paiements'] ?? 0 ?></h3>
                                        <small>Paiements</small>
                                    </div>
                                    <div class="col-6">
                                        <h3><?= number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') ?> FCFA</h3>
                                        <small>Recettes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Colonne droite : Derniers paiements -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-history"></i> Derniers paiements</h5>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($recent_payments as $p): ?>
                                <div class="border p-2 mb-2">
                                    <strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong><br>
                                    <small><?= number_format($p['montant_total'], 0, ',', ' ') ?> FCFA</small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function updatePrix() {
        const select = document.querySelector('select[name="service_id"]');
        const prix = select.options[select.selectedIndex]?.dataset.prix || 0;
        document.getElementById('prix_consultation').value = new Intl.NumberFormat('fr-FR').format(prix);
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
