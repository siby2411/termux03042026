<?php
$db_name = 'gp_db';
try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db_name;charset=utf8", "root", "");
} catch (Exception $e) { die("Lancer MariaDB d'abord !"); }

$save_dir = __DIR__ . '/images/';
if (!is_dir($save_dir)) mkdir($save_dir, 0777, true);

$boutiques = $pdo->query("SELECT * FROM boutiques")->fetchAll(PDO::FETCH_ASSOC);

foreach ($boutiques as $b) {
    echo "Traitement [{$b['nom']}] sur Samsung A20s...\n";
    
    // On réduit la résolution à 512x512 pour économiser la RAM lors de l'affichage
    $prompt = urlencode("Luxury " . $b['domaine'] . " for Dieynaba GP Holding. High quality, professional lighting.");
    $url = "https://image.pollinations.ai/prompt/{$prompt}?width=512&height=512&nologo=true&seed=" . rand(1, 999);
    
    // Utilisation de file_get_contents avec un timeout court pour éviter de bloquer le CPU
    $ctx = stream_context_create(['http' => ['timeout' => 20]]);
    $imgData = @file_get_contents($url, false, $ctx);
    
    if ($imgData) {
        $filename = 'gp_' . $b['id'] . '.jpg';
        file_put_contents($save_dir . $filename, $imgData);
        $pdo->prepare("REPLACE INTO images_generees (boutique_id, chemin_image) VALUES (?, ?)")
            ->execute([$b['id'], 'images/' . $filename]);
        echo "✅ OK.\n";
    }
    // Petite pause pour laisser le processeur respirer
    sleep(1);
}
