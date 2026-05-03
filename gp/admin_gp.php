<?php
$pdo = new PDO("mysql:host=localhost;dbname=gp_db;charset=utf8", "root", "");

// Action : Régénérer une image spécifique
if (isset($_GET['regen'])) {
    $id = (int)$_GET['regen'];
    $b = $pdo->query("SELECT * FROM boutiques WHERE id = $id")->fetch();
    
    $prompt = urlencode("Professional luxury " . $b['domaine'] . " for Dieynaba GP Holding. " . $b['produits'] . ". High-end lighting, 8k resolution.");
    $imgData = file_get_contents("https://image.pollinations.ai/prompt/{$prompt}?width=1024&height=1024&nologo=true&seed=" . rand(1, 10000));
    
    if ($imgData) {
        $filename = 'gp_' . $id . '_' . time() . '.jpg';
        file_put_contents(__DIR__ . '/images/' . $filename, $imgData);
        $pdo->prepare("INSERT INTO images_generees (boutique_id, chemin_image) VALUES (?, ?)")->execute([$id, 'images/' . $filename]);
        header("Location: admin_gp.php?success=1");
        exit;
    }
}

$shops = $pdo->query("SELECT b.*, (SELECT chemin_image FROM images_generees WHERE boutique_id = b.id ORDER BY id DESC LIMIT 1) as last_img FROM boutiques b")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Dieynaba GP</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        img { width: 100%; border-radius: 5px; height: 200px; object-fit: cover; margin-bottom: 10px; border: 1px solid #ddd; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
        .btn-regen { background: #28a745; }
    </style>
</head>
<body>
    <h1>Gestion des Boutiques - Dieynaba GP Holding</h1>
    <div class="grid">
        <?php foreach($shops as $s): ?>
        <div class="card">
            <h3><?php echo $s['nom']; ?></h3>
            <?php if($s['last_img']): ?>
                <img src="<?php echo $s['last_img']; ?>">
            <?php else: ?>
                <div style="height:200px; background:#eee; line-height:200px;">Aucune image</div>
            <?php endif; ?>
            <p><i><?php echo $s['domaine']; ?></i></p>
            <a href="?regen=<?php echo $s['id']; ?>" class="btn btn-regen">🔄 Régénérer l'IA</a>
        </div>
        <?php endforeach; ?>
    </div>
    <p><a href="vitrine.php" target="_blank">👁️ Voir la vitrine publique</a></p>
</body>
</html>
