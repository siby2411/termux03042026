<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /index.php');
    exit();
}

$pdo = getPDO();

$mois = $_GET['mois'] ?? date('m');
$annee = $_GET['annee'] ?? date('Y');

// Calculer les paies
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculer'])) {
    $pdo->beginTransaction();
    
    try {
        // Récupérer tous les médecins et sages-femmes
        $personnel = $pdo->query("
            SELECT u.*, s.name as service_nom
            FROM users u
            JOIN services s ON u.service_id = s.id
            WHERE u.role IN ('medecin', 'sagefemme')
        ")->fetchAll();
        
        foreach ($personnel as $p) {
            // Compter les consultations
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as nb_consultations,
                       SUM(CASE WHEN type_consultation = 'urgence' THEN 1 ELSE 0 END) as nb_urgences
                FROM consultations
                WHERE medecin_id = ? AND MONTH(date_consultation) = ? AND YEAR(date_consultation) = ?
            ");
            $stmt->execute([$p['id'], $mois, $annee]);
            $stats = $stmt->fetch();
            
            // Compter les actes
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as nb_actes, SUM(ca.prix_applique) as total_actes
                FROM consultation_actes ca
                JOIN consultations c ON ca.consultation_id = c.id
                WHERE c.medecin_id = ? AND MONTH(c.date_consultation) = ? AND YEAR(c.date_consultation) = ?
            ");
            $stmt->execute([$p['id'], $mois, $annee]);
            $actes = $stmt->fetch();
            
            // Calcul des primes
            $prime_consult = $stats['nb_consultations'] * 1000;
            $prime_actes = $actes['nb_actes'] * 500;
            $prime_urgence = $stats['nb_urgences'] * 2000;
            
            $salaire_base = $p['role'] == 'medecin' ? 500000 : 300000;
            $total_brut = $salaire_base + $prime_consult + $prime_actes + $prime_urgence;
            $cotisations = $total_brut * 0.15;
            $total_net = $total_brut - $cotisations;
            
            // Insérer ou mettre à jour la paie
            $stmt = $pdo->prepare("
                INSERT INTO paies (user_id, mois, annee, salaire_base, nb_consultations, nb_actes,
                                   prime_consultation, prime_acte, total_brut, cotisations, total_net)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    salaire_base = VALUES(salaire_base),
                    nb_consultations = VALUES(nb_consultations),
                    nb_actes = VALUES(nb_actes),
                    prime_consultation = VALUES(prime_consultation),
                    prime_acte = VALUES(prime_acte),
                    total_brut = VALUES(total_brut),
                    cotisations = VALUES(cotisations),
                    total_net = VALUES(total_net)
            ");
            $stmt->execute([
                $p['id'], $mois, $annee, $salaire_base,
                $stats['nb_consultations'], $actes['nb_actes'],
                $prime_consult, $prime_actes,
                $total_brut, $cotisations, $total_net
            ]);
        }
        
        $pdo->commit();
        $success = "Paies calculées avec succès pour $mois/$annee";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupérer les paies existantes
$paies = $pdo->prepare("
    SELECT p.*, u.prenom, u.nom, u.role, s.name as service_nom
    FROM paies p
    JOIN users u ON p.user_id = u.id
    JOIN services s ON u.service_id = s.id
    WHERE p.mois = ? AND p.annee = ?
    ORDER BY s.name, u.nom
");
$paies->execute([$mois, $annee]);
$paies = $paies->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calcul des paies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="mt-3">Calcul des paies</h1>
                
                <form method="GET" class="row mb-3">
                    <div class="col-md-3">
                        <select name="mois" class="form-control">
                            <?php for ($m=1; $m<=12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $mois ? 'selected' : '' ?>>
                                <?= date('F', mktime(0,0,0,$m,1)) ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="annee" class="form-control" value="<?= $annee ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-info">Voir</button>
                    </div>
                </form>
                
                <form method="POST">
                    <button type="submit" name="calculer" class="btn btn-success mb-3">
                        <i class="fas fa-calculator"></i> Calculer les paies
                    </button>
                </form>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Personnel</th>
                            <th>Rôle</th>
                            <th>Consultations</th>
                            <th>Actes</th>
                            <th>Salaire base</th>
                            <th>Primes</th>
                            <th>Brut</th>
                            <th>Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paies as $p): ?>
                        <tr>
                            <td><?= $p['service_nom'] ?></td>
                            <td><?= $p['prenom'] ?> <?= $p['nom'] ?></td>
                            <td><?= $p['role'] ?></td>
                            <td><?= $p['nb_consultations'] ?></td>
                            <td><?= $p['nb_actes'] ?></td>
                            <td><?= number_format($p['salaire_base'], 0, ',', ' ') ?></td>
                            <td><?= number_format($p['prime_consultation'] + $p['prime_acte'], 0, ',', ' ') ?></td>
                            <td><?= number_format($p['total_brut'], 0, ',', ' ') ?></td>
                            <td><strong><?= number_format($p['total_net'], 0, ',', ' ') ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
</body>
</html>
