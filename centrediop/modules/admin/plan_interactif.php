<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$batiments = $pdo->query("
    SELECT b.*, 
           (SELECT COUNT(*) FROM salles WHERE batiment_id = b.id) as total_salles
    FROM batiments b
    ORDER BY b.nom
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan interactif du centre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px;
            color: white;
        }
        .container-fluid { padding: 20px; }
        .batiment-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .etage-row {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .salle {
            background: #1e3c72;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .salle:hover {
            transform: scale(1.05);
            background: #2a5298;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <h3><i class="fas fa-map-marked-alt"></i> Plan interactif du centre</h3>
            <a href="dashboard.php" class="btn btn-sm btn-light">Retour Dashboard</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <?php foreach($batiments as $b): ?>
                <div class="col-md-6">
                    <div class="batiment-card">
                        <h5><i class="fas fa-building"></i> <?= htmlspecialchars($b['nom']) ?></h5>
                        <hr>
                        
                        <?php
                        $etages = $pdo->prepare("
                            SELECT DISTINCT etage 
                            FROM salles 
                            WHERE batiment_id = ? 
                            ORDER BY 
                                CASE etage 
                                    WHEN 'RDC' THEN 0 
                                    ELSE CAST(SUBSTRING_INDEX(etage, 'er', 1) AS UNSIGNED) 
                                END
                        ");
                        $etages->execute([$b['id']]);
                        ?>
                        
                        <?php foreach($etages as $e): ?>
                            <div class="etage-row">
                                <div class="fw-bold me-3" style="width: 60px;">Ét. <?= $e['etage'] ?></div>
                                <?php
                                $salles = $pdo->prepare("
                                    SELECT s.*, serv.name as service_nom
                                    FROM salles s
                                    JOIN services serv ON s.service_id = serv.id
                                    WHERE s.batiment_id = ? AND s.etage = ?
                                    ORDER BY s.numero_salle
                                ");
                                $salles->execute([$b['id'], $e['etage']]);
                                ?>
                                <?php foreach($salles as $s): ?>
                                    <div class="salle" title="<?= htmlspecialchars($s['service_nom']) ?>">
                                        <?= $s['numero_salle'] ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
