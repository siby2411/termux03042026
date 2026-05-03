<?php
$pdo = new PDO("mysql:host=localhost;dbname=gp_db;charset=utf8", "root", "");
$shops = $pdo->query("SELECT b.*, (SELECT chemin_image FROM images_generees WHERE boutique_id = b.id ORDER BY id DESC LIMIT 1) as img FROM boutiques b")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dieynaba GP Holding</title>
    <style>
        body { background: #000; color: white; font-family: serif; margin: 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; padding: 20px; }
        .card { position: relative; border: 1px solid #333; overflow: hidden; height: 500px; border-radius: 15px; }
        .card img { width: 100%; height: 100%; object-fit: cover; }
        
        /* Incrustation forcée du nom et téléphone */
        .brand-overlay {
            position: absolute; top: 15px; left: 15px;
            background: rgba(0,0,0,0.6); padding: 10px; border-left: 3px solid #C5A059;
        }
        .brand-name { font-weight: bold; letter-spacing: 1px; font-size: 1.1em; }
        .brand-tel { color: #C5A059; font-size: 0.9em; display: block; }

        .info-bottom {
            position: absolute; bottom: 0; width: 100%;
            background: linear-gradient(transparent, black); padding: 20px; text-align: center;
        }
        .slogan { font-style: italic; font-size: 1.2em; color: #fff; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1 style="text-align:center; color:#C5A059; padding:20px;">Dieynaba GP Holding</h1>
    <div class="grid">
        <?php foreach($shops as $s): ?>
        <div class="card">
            <img src="<?php echo $s['img']; ?>">
            
            <div class="brand-overlay">
                <div class="brand-name">Dieynaba GP Holding</div>
                <span class="brand-tel">📞 <?php echo $s['telephone']; ?></span>
            </div>

            <div class="info-bottom">
                <h2><?php echo $s['nom']; ?></h2>
                <div class="slogan"><?php echo $s['slogan_pub'] ?? $s['slogan']; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
