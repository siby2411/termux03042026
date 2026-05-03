<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['secretaire', 'admin'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$user_id = $_SESSION['user_id'];

// Récupérer les services
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();

$message = '';
$message_type = '';

// Génération de rapport
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generer_rapport'])) {
    $type = $_POST['type_rapport'];
    $periode_debut = $_POST['periode_debut'];
    $periode_fin = $_POST['periode_fin'];
    $service_id = $_POST['service_id'] ?? null;
    
    // Générer le contenu du rapport
    $contenu = "RAPPORT D'ACTIVITÉ\n";
    $contenu .= "==================\n\n";
    $contenu .= "Période du " . date('d/m/Y', strtotime($periode_debut)) . " au " . date('d/m/Y', strtotime($periode_fin)) . "\n";
    $contenu .= "Date de génération: " . date('d/m/Y H:i') . "\n\n";
    
    if ($service_id) {
        $service_nom = $pdo->prepare("SELECT name FROM services WHERE id = ?");
        $service_nom->execute([$service_id]);
        $contenu .= "Service: " . $service_nom->fetchColumn() . "\n\n";
    }
    
    // Statistiques selon le type
    if ($type == 'consultations') {
        $query = "SELECT COUNT(*) as total FROM consultations WHERE date_consultation BETWEEN ? AND ?";
        $params = [$periode_debut, $periode_fin];
        if ($service_id) {
            $query .= " AND service_id = ?";
            $params[] = $service_id;
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        $contenu .= "Total consultations: $total\n\n";
        
        // Détails par médecin
        $query = "SELECT CONCAT(u.prenom, ' ', u.nom) as medecin, COUNT(*) as nb
                  FROM consultations c
                  JOIN users u ON c.medecin_id = u.id
                  WHERE c.date_consultation BETWEEN ? AND ?";
        if ($service_id) {
            $query .= " AND c.service_id = ?";
        }
        $query .= " GROUP BY c.medecin_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $details = $stmt->fetchAll();
        
        $contenu .= "Détails par médecin:\n";
        foreach ($details as $d) {
            $contenu .= "  - Dr. " . $d['medecin'] . ": " . $d['nb'] . " consultation(s)\n";
        }
        
    } elseif ($type == 'rendezvous') {
        $query = "SELECT COUNT(*) as total FROM rendez_vous WHERE date_rdv BETWEEN ? AND ?";
        $params = [$periode_debut, $periode_fin];
        if ($service_id) {
            $query .= " AND service_id = ?";
            $params[] = $service_id;
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        $contenu .= "Total rendez-vous: $total\n\n";
        
        // Par statut
        $stats = $pdo->prepare("
            SELECT statut, COUNT(*) as nb
            FROM rendez_vous
            WHERE date_rdv BETWEEN ? AND ?
            GROUP BY statut
        ");
        $stats->execute([$periode_debut, $periode_fin]);
        
        $contenu .= "Répartition par statut:\n";
        foreach ($stats as $s) {
            $contenu .= "  - " . ucfirst($s['statut']) . ": " . $s['nb'] . "\n";
        }
        
    } elseif ($type == 'paiements') {
        $query = "SELECT COUNT(*) as total, SUM(montant_total) as total_montant 
                  FROM paiements WHERE date_paiement BETWEEN ? AND ?";
        $params = [$periode_debut, $periode_fin];
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        $contenu .= "Total paiements: " . $result['total'] . "\n";
        $contenu .= "Montant total: " . number_format($result['total_montant'], 0, ',', ' ') . " FCFA\n\n";
        
        // Par mode de paiement
        $modes = $pdo->prepare("
            SELECT mode_paiement, COUNT(*) as nb, SUM(montant_total) as montant
            FROM paiements
            WHERE date_paiement BETWEEN ? AND ?
            GROUP BY mode_paiement
        ");
        $modes->execute([$periode_debut, $periode_fin]);
        
        $contenu .= "Répartition par mode de paiement:\n";
        foreach ($modes as $m) {
            $contenu .= "  - " . ucfirst($m['mode_paiement']) . ": " . $m['nb'] . " (" . number_format($m['montant'], 0, ',', ' ') . " FCFA)\n";
        }
    }
    
    // Sauvegarder le rapport
    $nom_fichier = 'rapport_' . $type . '_' . date('Ymd_His') . '.txt';
    $chemin = 'uploads/rapports/' . $nom_fichier;
    file_put_contents('../../' . $chemin, $contenu);
    
    // Enregistrer en BDD
    $stmt = $pdo->prepare("
        INSERT INTO rapports (titre, type_rapport, periode_debut, periode_fin, fichier_nom, fichier_chemin, service_id, genere_par)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        "Rapport $type du " . date('d/m/Y', strtotime($periode_debut)),
        $type,
        $periode_debut,
        $periode_fin,
        $nom_fichier,
        $chemin,
        $service_id,
        $user_id
    ]);
    
    $message = "Rapport généré avec succès !";
    $message_type = "success";
}

// Récupérer les rapports récents
$rapports = $pdo->query("
    SELECT r.*, u.prenom, u.nom, s.name as service_nom
    FROM rapports r
    LEFT JOIN users u ON r.genere_par = u.id
    LEFT JOIN services s ON r.service_id = s.id
    ORDER BY r.date_generation DESC
    LIMIT 20
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Rapports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px; color: white; }
        .container-fluid { padding: 20px; }
        .card {
            background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 25px; margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;
            margin: -25px -25px 20px -25px; padding: 15px 25px; border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <h3><i class="fas fa-chart-bar"></i> Gestion des Rapports</h3>
            <a href="index.php" class="btn btn-sm btn-light">Retour</a>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulaire de génération -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus"></i> Générer un rapport
                    </div>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label>Type de rapport</label>
                            <select name="type_rapport" class="form-select" required>
                                <option value="consultations">Consultations</option>
                                <option value="rendezvous">Rendez-vous</option>
                                <option value="paiements">Paiements</option>
                                <option value="patients">Patients</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Service (optionnel)</label>
                            <select name="service_id" class="form-select">
                                <option value="">Tous les services</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Date début</label>
                                    <input type="date" name="periode_debut" class="form-control" required 
                                           value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Date fin</label>
                                    <input type="date" name="periode_fin" class="form-control" required 
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="generer_rapport" class="btn btn-success w-100">
                            <i class="fas fa-file-export"></i> Générer le rapport
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Liste des rapports -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Rapports récents
                    </div>
                    
                    <div class="list-group">
                        <?php foreach ($rapports as $r): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($r['titre']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($r['date_generation'])) ?> |
                                            Généré par <?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?>
                                            <?php if ($r['service_nom']): ?> | Service: <?= $r['service_nom'] ?><?php endif; ?>
                                        </small>
                                    </div>
                                    <a href="../../<?= $r['fichier_chemin'] ?>" class="btn btn-sm btn-primary" download>
                                        <i class="fas fa-download"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
