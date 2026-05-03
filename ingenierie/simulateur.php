<?php
require_once 'db_connect.php';

// Simuler une variation de prix entre -2% et +2% pour chaque actif
$stmt = $pdo->query("SELECT id, current_price FROM assets");
$assets = $stmt->fetchAll();

foreach ($assets as $asset) {
    $variation = rand(-200, 200) / 10000; // Génère un pourcentage (ex: 0.015 pour 1.5%)
    $new_price = $asset['current_price'] * (1 + $variation);
    
    $update = $pdo->prepare("UPDATE assets SET current_price = ? WHERE id = ?");
    $update->execute([$new_price, $asset['id']]);
}

echo "📈 Marché mis à jour avec succès !";
?>
<script>setTimeout(() => { window.location.href = 'index.php'; }, 1000);</script>
