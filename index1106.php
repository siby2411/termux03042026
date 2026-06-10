<?php
$host = "http://localhost";
$apps = [
    "Ingénierie" => ["url" => $host . ":8094/", "logo" => "🔧"],
    "Banque" => ["url" => $host . ":8095/", "logo" => "🏦"],
    "GP" => ["url" => $host . ":8096/", "logo" => "📊"],
    "Assurance" => ["url" => $host . ":8098/", "logo" => "🛡️"],
    "PME" => ["url" => $host . ":8100/", "logo" => "🏢"],
    "Gestion Commerciale" => ["url" => $host . ":8101/", "logo" => "📦"],
    "E-commerce" => ["url" => $host . ":8102/", "logo" => "🛒"],
    "Gestion École" => ["url" => $host . ":8091/", "logo" => "🎓"],
    "Report" => ["url" => $host . ":8130/", "logo" => "📋"],
    "Centre DIOP" => ["url" => $host . ":8140/", "logo" => "🏥"],
    "Pharmacie" => ["url" => $host . ":8144/", "logo" => "💊"],
    "Restauration" => ["url" => $host . ":8104/", "logo" => "🍽️"],
    "Auto" => ["url" => $host . ":8110/", "logo" => "🚘"],
    "Cabinet Radiologie" => ["url" => $host . ":8153/", "logo" => "🩻"],
    "Portail" => ["url" => $host . ":8152/", "logo" => "🚪"],
    "Transit" => ["url" => $host . ":8155/", "logo" => "🚛"],
    "Agence Voyage" => ["url" => $host . ":8156/", "logo" => "✈️"],
    "Annuaire" => ["url" => $host . ":8157/", "logo" => "📇"],
    "Fitness" => ["url" => $host . ":8158/", "logo" => "🏋️"],
    "Pizzeria" => ["url" => $host . ":8159/", "logo" => "🍕"],
    "Scooter" => ["url" => $host . ":8160/", "logo" => "🛵"],
    "Cosmétique" => ["url" => $host . ":8161/", "logo" => "🧴"]
];
?>
<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>Business Suite Pro - 2026</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); min-height: 100vh; color: #2c3e50; }
    .glass-card { background: rgba(255, 255, 255, 0.95); border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); margin-bottom: 20px; }
    .app-card { display: block; padding: 2rem; text-decoration: none; color: inherit; }
</style></head>
<body>
    <div class="container py-5 text-center text-white"><h1>Business Suite Pro</h1></div>
    <div class="container"><div class="row">
        <?php foreach($apps as $name => $app): ?>
            <div class="col-md-4 col-lg-3"><div class="glass-card">
                <a href="<?= $app['url'] ?>" target="_blank" class="app-card">
                    <div style="font-size: 3rem;"><?= $app['logo'] ?></div><h5><?= $name ?></h5>
                </a>
            </div></div>
        <?php endforeach; ?>
    </div></div>
</body></html>
