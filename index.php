<?php
$host = "http://localhost";

$apps = [

    // ===================== CORE FINANCE =====================
    "Ingénierie" => ["url" => $host . ":8094/", "logo" => "🔧"],
    "Banque" => ["url" => $host . ":8095/", "logo" => "🏦"],
    "GP" => ["url" => $host . ":8096/", "logo" => "📊"],
    "Assurance" => ["url" => $host . ":8098/", "logo" => "🛡️"],

    // ===================== BUSINESS =====================
    "PME" => ["url" => $host . ":8100/", "logo" => "🏢"],
    "Gestion Commerciale" => ["url" => $host . ":8101/", "logo" => "📦"],
    "Couture Sénégal" => ["url" => $host . ":8102/", "logo" => "🧵"],
    "E-commerce" => ["url" => $host . ":8103/", "logo" => "🛒"],
    "Gestion École" => ["url" . $host . ":8091/", "logo" => "🎓"],

    // ===================== REPORTING =====================
    "Report" => ["url" => $host . ":8130/", "logo" => "📋"],

    // ===================== SANTÉ =====================
    "Centre DIOP" => ["url" => $host . ":8140/", "logo" => "🏥"],
    "Pharmacie" => ["url" => $host . ":8144/", "logo" => "💊"],

    // ===================== SPORT =====================
    "Foot" => ["url" => $host . ":8142/", "logo" => "⚽"],

    // ===================== EMPLOI =====================
    "Offre Emploi" => ["url" => $host . ":8164/", "logo" => "💼"],

    // ===================== RESTAURATION =====================
    "Restauration" => ["url" => $host . ":8104/", "logo" => "🍽️"],

    // ===================== AUTOMOBILE =====================
    "Auto" => ["url" => $host . ":8110/", "logo" => "🚘"],

    // ===================== MÉDICAL =====================
    "Cabinet Radiologie" => ["url" => $host . ":8153/", "logo" => "🩻"],

    // ===================== PORTAIL =====================
    "Portail" => ["url" => $host . ":8152/", "logo" => "🚪"],

    // ===================== LOGISTIQUE =====================
    "Transit" => ["url" => $host . ":8155/", "logo" => "🚛"],
    "Agence Voyage" => ["url" => $host . ":8156/", "logo" => "✈️"],

    // ===================== INDUSTRIE =====================
    "Génie Civil" => ["url" => $host . ":8165/", "logo" => "🏗️"],

    // ===================== SERVICES =====================
    "Annuaire" => ["url" => $host . ":8157/", "logo" => "📇"],
    "Fitness" => ["url" => $host . ":8158/", "logo" => "🏋️"],
    "Pizzeria" => ["url" => $host . ":8159/", "logo" => "🍕"],
    "Scooter" => ["url" => $host . ":8160/", "logo" => "🛵"],

    // ===================== BEAUTÉ =====================
    "Parfumerie & Cosmétique" => ["url" => $host . ":8161/", "logo" => "💄"],

];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>OMEGA SUITE 2026</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#0f2027,#203a43,#2c5364);
    min-height:100vh;
    color:white;
}

.omega-banner{
    background: linear-gradient(90deg,#ffcc00,#ff6600,#ffcc00);
    color:#000;
    font-weight:bold;
    text-align:center;
    padding:15px;
    font-size:20px;
}

.card-app{
    background: rgba(255,255,255,0.95);
    border-radius:15px;
    padding:20px;
    color:#000;
    box-shadow:0 6px 20px rgba(0,0,0,0.3);
    transition:0.3s;
}

.card-app:hover{
    transform:scale(1.05);
}

a{
    text-decoration:none;
}
</style>
</head>

<body>

<div class="omega-banner">
🚀 OMEGA INFORMATIQUE CONSULTING - SUITE PROFESSIONNELLE 2026 🚀
</div>

<div class="container text-center mt-4">
    <h1>Business Suite Multi-Applications</h1>
</div>

<div class="container mt-4">
<div class="row">

<?php foreach($apps as $name => $app): ?>
<div class="col-md-4 col-lg-3 mb-3">
    <a href="<?= $app['url'] ?>" target="_blank">
        <div class="card-app text-center">
            <div style="font-size:40px"><?= $app['logo'] ?></div>
            <h6 class="mt-2"><?= $name ?></h6>
        </div>
    </a>
</div>
<?php endforeach; ?>

</div>
</div>

</body>
</html>
