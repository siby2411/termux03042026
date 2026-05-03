<?php require('config/db.php'); 
require('includes/navbar.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA - Générateur d'Offres</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:wght@400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
            background: linear-gradient(135deg, #f0f2f5 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding-top: 56px;
        }
        .main-container {
            max-width: 800px; 
            margin: 0 auto; 
            padding: 40px 20px;
        }
        .hero-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,40,85,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            position: relative;
            overflow: hidden;
        }
        .hero-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #002855, #5bb3ff, #34c97a);
        }
        .hero-title {
            font-family: 'DM Serif Display', serif;
            font-size: 2.2rem;
            background: linear-gradient(135deg, #002855, #5bb3ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }
        .hero-subtitle {
            color: #64748b;
            font-size: 1.1rem;
            text-align: center;
            margin-bottom: 35px;
            font-weight: 500;
        }
        .search-container {
            position: relative;
            margin-bottom: 25px;
        }
        .search-input {
            width: 100%;
            padding: 20px 24px 20px 60px;
            font-size: 1.1rem;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            background: rgba(255,255,255,0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            backdrop-filter: blur(10px);
        }
        .search-input:focus {
            outline: none;
            border-color: #5bb3ff;
            box-shadow: 0 0 0 4px rgba(91,179,255,0.1);
            transform: translateY(-2px);
        }
        .search-icon {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1.3rem;
        }
        .guide-section {
            background: linear-gradient(135deg, #f8fafc, #e8f4fd);
            padding: 24px;
            border-radius: 16px;
            border-left: 5px solid #5bb3ff;
            margin-bottom: 30px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }
        .guide-icon {
            font-size: 1.8rem;
            background: rgba(91,179,255,0.2);
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #5bb3ff;
        }
        .generate-btn {
            width: 100%;
            padding: 20px;
            background: linear-gradient(135deg, #002855 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.15rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .generate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(0,40,85,0.3);
        }
        .generate-btn:active {
            transform: translateY(-1px);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        .stat-card {
            background: rgba(255,255,255,0.7);
            padding: 24px;
            border-radius: 16px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #002855, #5bb3ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @media (max-width: 768px) {
            .hero-card { padding: 30px 20px; }
            .hero-title { font-size: 1.8rem; }
            body { padding-top: 56px; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="hero-card">
            <h1 class="hero-title">Générateur d'Offres</h1>
            <p class="hero-subtitle">Trouvez rapidement vos partenaires médicaux et générez des offres professionnelles en 1 clic</p>
            
            <div class="guide-section">
                <div class="guide-icon">🔍</div>
                <div>
                    <h3 style="color: #002855; margin-bottom: 8px; font-size: 1.1rem;">Comment ça marche ?</h3>
                    <p style="color: #64748b; line-height: 1.6; margin: 0;">
                        Tapez un nom, numéro ou spécialité. L'autocomplétion affiche instantanément tous vos partenaires enregistrés.
                    </p>
                </div>
            </div>

            <form action="generer_offre.php" method="POST" id="searchForm">
                <div class="search-container">
                    <span class="search-icon">🔎</span>
                    <input 
                        list="partenaires" 
                        name="telephone" 
                        id="tel_input" 
                        class="search-input"
                        placeholder="Rechercher par nom, numéro ou spécialité..."
                        required 
                        autocomplete="off"
                    >
                </div>
                <datalist id="partenaires">
                    <?php
                    $list = $pdo->query("SELECT nom, telephone, specialite FROM annuaire_medical ORDER BY nom ASC");
                    foreach ($list as $row) {
                        $display = $row['nom'] . ' (' . $row['telephone'] . ')';
                        if (!empty($row['specialite'])) $display .= ' - ' . $row['specialite'];
                        echo '<option value="' . htmlspecialchars($row['telephone']) . '">' . $display . '</option>';
                    }
                    ?>
                </datalist>
                <button type="submit" class="generate-btn">
                    🚀 GÉNÉRER L'OFFRE PDF
                </button>
            </form>

            <?php
            $total = $pdo->query("SELECT COUNT(*) as total FROM annuaire_medical")->fetch()['total'];
            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($total) ?></div>
                    <div style="color: #64748b; font-weight: 500;">Partenaires</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">100%</div>
                    <div style="color: #64748b; font-weight: 500;">Automatisé</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Amélioration de la recherche en temps réel
        const input = document.getElementById('tel_input');
        input.addEventListener('input', function() {
            this.style.borderColor = this.value ? '#5bb3ff' : '#e2e8f0';
        });
        
        // Animation au focus
        input.addEventListener('focus', function() {
            document.querySelector('.hero-card').style.transform = 'scale(1.02)';
        });
        input.addEventListener('blur', function() {
            document.querySelector('.hero-card').style.transform = 'scale(1)';
        });
    </script>
</body>
</html>
