<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

$res = $conn->query("SELECT * FROM voitures ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omega Auto | Gestion du Parc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #0f172a; height: 100vh; color: white; padding: 20px; position: fixed; }
        .main { margin-left: 250px; padding: 30px; width: 100%; }
        .card-parc { background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .btn-tool { padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: bold; margin-left: 5px; }
        .bg-qr { background: #000; color: white; }
        .bg-gal { background: #3b82f6; color: white; }
        .bg-edit { background: #10b981; color: white; }
        .nav-item { display: block; color: #94a3b8; padding: 12px; text-decoration: none; border-radius: 8px; margin-bottom: 5px; }
        .nav-item:hover { background: #1e293b; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>OMEGA AUTO</h2>
        <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="ajouter_voiture.php" class="nav-item"><i class="fas fa-plus-circle"></i> Ajouter Véhicule</a>
        <a href="liste_locations.php" class="nav-item"><i class="fas fa-file-invoice"></i> Locations</a>
        <a href="index.php" class="nav-item"><i class="fas fa-eye"></i> Voir le Site</a>
    </div>

    <div class="main">
        <h1>Gestion du Parc Automobile</h1>
        
        <?php while($v = $res->fetch_assoc()): 
            $url_fiche = "http://" . $_SERVER['HTTP_HOST'] . "/detail_voiture.php?id=" . $v['id'];
            $qr_api = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($url_fiche) . "&choe=UTF-8";
        ?>
        <div class="card-parc">
            <div>
                <strong style="font-size:1.1rem;"><?php echo $v['marque']." ".$v['modele']; ?></strong> 
                <span style="color:#64748b; margin-left:10px;">[<?php echo $v['immatriculation']; ?>]</span>
                <div style="font-size:0.8rem; color:#3b82f6; margin-top:5px;">
                    <i class="fas fa-coins"></i> <?php echo number_format($v['prix_journalier'], 0); ?> FCFA | 
                    <i class="fas fa-mouse-pointer"></i> <?php echo $v['clics_whatsapp']; ?> Clics
                </div>
            </div>

            <div class="actions">
                <a href="<?php echo $qr_api; ?>" target="_blank" class="btn-tool bg-qr" title="Imprimer le QR Code">
                    <i class="fas fa-qrcode"></i> QR CODE
                </a>
                
                <a href="ajouter_photos.php?id=<?php echo $v['id']; ?>" class="btn-tool bg-gal">
                    <i class="fas fa-images"></i> PHOTOS
                </a>

                <a href="#" onclick="updateKm(<?php echo $v['id']; ?>)" class="btn-tool bg-edit" style="background:#f59e0b;">
                    <i class="fas fa-tools"></i> KM
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <script>
    function updateKm(id) {
        let km = prompt("Nouveau kilométrage :");
        if(km) window.location.href = "update_km.php?id="+id+"&km="+km;
    }
    </script>
</body>
</html>
