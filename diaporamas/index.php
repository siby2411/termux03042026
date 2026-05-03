<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omega Business Suite - Présentation PDF</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Raleway', sans-serif;
            background: #0f2027;
            color: #fff;
            line-height: 1.6;
        }

        /* --- Style des Slides --- */
        .slide {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
            border-bottom: 2px solid rgba(233,69,96,0.2);
            page-break-after: always; /* Force le saut de page pour le PDF */
        }

        .container { width: 100%; max-width: 1100px; margin: 0 auto; }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: #e94560;
            margin-bottom: 20px;
        }

        .app-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
        }

        .app-card {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        /* --- Mise en page Contenu --- */
        .flex-content { display: flex; gap: 40px; margin-top: 30px; text-align: left; }
        .feature-box { flex: 1; background: rgba(255,255,255,0.05); padding: 25px; border-radius: 15px; }
        
        ul { list-style: none; margin-top: 15px; }
        ul li { margin-bottom: 10px; padding-left: 25px; position: relative; }
        ul li::before { content: "\f058"; font-family: "Font Awesome 6 Free"; font-weight: 900; position: absolute; left: 0; color: #e94560; }

        /* --- CONFIGURATION IMPRESSION PDF --- */
        @media print {
            body { background: white !important; color: black !important; }
            .slide { 
                height: 100vh !important; 
                border: none !important;
                margin: 0 !important;
                padding: 40px !important;
                page-break-after: always !important;
                display: block !important; /* Pour éviter les bugs de centrage flex à l'impression */
            }
            .nav-controls, .consultant { display: none !important; }
            h1, .app-name, .subtitle { color: #2c3e50 !important; -webkit-print-color-adjust: exact; }
            .app-card, .feature-box { 
                background: #f9f9f9 !important; 
                border: 1px solid #ddd !important; 
                color: #333 !important;
                -webkit-print-color-adjust: exact;
            }
            i { color: #e94560 !important; }
        }

        /* Barre de consultation fixe */
        .consultant {
            position: fixed; bottom: 10px; right: 20px;
            font-size: 0.8rem; background: rgba(0,0,0,0.7);
            padding: 5px 15px; border-radius: 20px; z-index: 100;
        }
    </style>
</head>
<body>

    <div class="consultant">
        <i class="fas fa-user-tie"></i> Mohamed Siby - Ingénieur Informaticien | © 2026
    </div>

    <div class="slide">
        <div class="container" style="text-align: center;">
            <i class="fas fa-gem" style="font-size: 4rem; color: #e94560;"></i>
            <h1 style="margin-top: 20px;">OMÉGA BUSINESS SUITE</h1>
            <p class="subtitle">Catalogue des Solutions Logicielles 2026</p>
            <div class="app-grid">
                <div class="app-card">🚗<br>Auto</div>
                <div class="app-card">🏥<br>Santé</div>
                <div class="app-card">🏢<br>Immo</div>
                <div class="app-card">🎓<br>École</div>
                <div class="app-card">🔬<br>Labo</div>
                <div class="app-card">🏦<br>Banque</div>
            </div>
        </div>
    </div>

    <?php
    $apps = [
        'auto' => ['name' => 'OMEGA Auto', 'title' => 'Gestion de Location & Parc', 'icon' => '🚗', 'color' => '#e74c3c'],
        'sante' => ['name' => 'OMEGA Santé', 'title' => 'Gestion de Centre Médical', 'icon' => '🏥', 'color' => '#3498db'],
        'immo' => ['name' => 'OMEGA Immobilier', 'title' => 'Gestion de Biens & Copropriété', 'icon' => '🏢', 'color' => '#f1c40f'],
        'labo' => ['name' => 'OMEGA Labo', 'title' => 'Analyses Médicales & Radiologie', 'icon' => '🔬', 'color' => '#1abc9c'],
    ];

    foreach($apps as $app): ?>
    <div class="slide">
        <div class="container" style="text-align: center;">
            <div style="font-size: 3.5rem;"><?= $app['icon'] ?></div>
            <h1><?= $app['name'] ?></h1>
            <p style="font-weight: bold; color: <?= $app['color'] ?>;"><?= $app['title'] ?></p>
            
            <div class="flex-content">
                <div class="feature-box">
                    <h3>Fonctionnalités</h3>
                    <ul>
                        <li>Tableau de bord intelligent</li>
                        <li>Gestion des flux et stocks</li>
                        <li>Exportation des rapports (PDF/Excel)</li>
                    </ul>
                </div>
                <div class="feature-box">
                    <h3>Bénéfices</h3>
                    <ul>
                        <li>Gain de productivité</li>
                        <li>Sécurisation des données</li>
                        <li>Traçabilité totale</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</body>
</html>
