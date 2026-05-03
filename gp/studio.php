<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pdo = new PDO("mysql:host=localhost;dbname=gp_db;charset=utf8", "root", "");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['boutique_id'];
    $extra_items = $_POST['extra_items'];
    $slogan = $_POST['slogan'];
    
    $stmt = $pdo->prepare("SELECT * FROM boutiques WHERE id = ?");
    $stmt->execute([$id]);
    $b = $stmt->fetch();

    // Prompt de sécurité pour les bijoux
    $final_prompt = "Professional luxury photography for Dieynaba GP Holding. " . $extra_items;
    if ($id == 2) {
        $final_prompt = "A luxurious collection of diamond necklaces, gold rings, and bracelets arranged on a dark velvet display. NO PEOPLE, NO WOMEN, only jewelry, high-end lighting.";
    }

    $url = "https://image.pollinations.ai/prompt/" . urlencode($final_prompt) . "?width=1024&height=1024&nologo=true&seed=" . rand(1, 9999);
    
    echo "Tentative de génération... <br>";

    // Utilisation de CURL pour plus de fiabilité sur Termux
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $imgData = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($imgData && $http_code == 200) {
        $filename = 'gp_custom_' . $id . '_' . time() . '.jpg';
        $filepath = __DIR__ . '/images/' . $filename;
        
        if (file_put_contents($filepath, $imgData)) {
            $pdo->prepare("UPDATE boutiques SET slogan_pub = ? WHERE id = ?")->execute([$slogan, $id]);
            $pdo->prepare("INSERT INTO images_generees (boutique_id, chemin_image) VALUES (?, ?)")->execute([$id, 'images/' . $filename]);
            echo "<b style='color:green'>Succès ! Image enregistrée : $filename</b>";
        } else {
            echo "<b style='color:red'>Erreur : Impossible d'écrire dans le dossier /images. Vérifiez les permissions.</b>";
        }
    } else {
        echo "<b style='color:red'>Erreur réseau : Code HTTP $http_code. Vérifiez votre connexion.</b>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Studio Infographie - Dieynaba GP</title>
    <style>
        body { background: #121212; color: #eee; font-family: sans-serif; padding: 20px; }
        .form-box { background: #1e1e1e; padding: 20px; border-radius: 10px; max-width: 500px; margin: auto; border: 1px solid #C5A059; }
        select, textarea, input { width: 100%; padding: 10px; margin: 10px 0; background: #333; border: 1px solid #444; color: white; border-radius: 5px; }
        button { width: 100%; padding: 15px; background: #C5A059; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>🎨 Studio Dieynaba GP</h2>
        <form method="POST">
            <label>Boutique :</label>
            <select name="boutique_id">
                <option value="1">Négoce (Laptops, Frigos...)</option>
                <option value="2">Joaillerie (Bijoux uniquement)</option>
                <option value="3">Épicerie (Mangues, Cajou...)</option>
                <option value="4">Couture Luxe</option>
            </select>
            <textarea name="extra_items" rows="3" placeholder="Description des produits..."></textarea>
            <input type="text" name="slogan" placeholder="Nouveau slogan publicitaire">
            <button type="submit">GÉNÉRER L'INFOGRAPHIE</button>
        </form>
        <br><a href="vitrine.php" style="color:#C5A059">⬅ Voir la Vitrine</a>
    </div>
</body>
</html>
