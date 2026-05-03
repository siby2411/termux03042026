<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

$generated_html = '';
$description = '';
$saved_path = '';

// Créer le dossier pour les images générées
$save_dir = __DIR__ . '/uploads/generated/';
if (!is_dir($save_dir)) {
    mkdir($save_dir, 0777, true);
}
chmod($save_dir, 0777);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description = trim($_POST['description']);
    
    $filename = 'generated_' . date('Ymd_His') . '.html';
    $saved_path = 'uploads/generated/' . $filename;
    $full_path = __DIR__ . '/' . $saved_path;
    
    // Générer le HTML complet avec design amélioré
    $full_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dieynaba GP Holding - Promotion</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: #f0f0f0; 
            font-family: "Poppins", "Segoe UI", Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px;
        }
        .poster {
            width: 800px;
            max-width: 100%;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            margin: 20px auto;
        }
        /* Espace pour capture d\'écran */
        .screenshot-space {
            padding: 30px;
        }
        /* En-tête */
        .header {
            text-align: center;
            padding: 30px 30px 20px 30px;
            border-bottom: 2px solid #ff8c00;
            background: rgba(0,0,0,0.2);
        }
        .header img {
            max-height: 80px;
            margin-bottom: 15px;
            border-radius: 15px;
        }
        .header h1 {
            color: #ff8c00;
            font-size: 28px;
            letter-spacing: 2px;
            margin: 10px 0 5px;
            font-weight: 800;
        }
        .header .slogan {
            color: #aaaaaa;
            font-size: 12px;
            letter-spacing: 1px;
        }
        /* Corps */
        .body {
            padding: 30px;
            text-align: center;
        }
        .promo-badge {
            display: inline-block;
            background: #ff8c00;
            color: #1a1a2e;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .product-title {
            background: linear-gradient(135deg, #0f3460, #1a1a2e);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #ff8c00;
        }
        .product-title .label {
            color: #ff8c00;
            font-size: 14px;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }
        .product-title .description {
            color: #ffffff;
            font-size: 26px;
            font-weight: bold;
            line-height: 1.4;
        }
        /* Avantages */
        .features {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin: 30px 0;
        }
        .feature {
            background: rgba(255,140,0,0.1);
            border: 1px solid #ff8c00;
            border-radius: 15px;
            padding: 12px 20px;
            min-width: 120px;
        }
        .feature p {
            color: #ff8c00;
            font-size: 13px;
            font-weight: 500;
            margin: 0;
        }
        /* Prix / Offre */
        .offer-box {
            background: #ff8c00;
            border-radius: 15px;
            padding: 20px;
            margin: 25px 0;
        }
        .offer-box p {
            color: #1a1a2e;
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .offer-box .price {
            font-size: 32px;
            margin-top: 10px;
        }
        /* Call to action */
        .cta {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 20px;
            margin: 25px 0;
        }
        .cta .phone {
            color: #ff8c00;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .cta .sub {
            color: #aaa;
            font-size: 12px;
            margin-top: 8px;
        }
        /* Pied de page */
        .footer {
            background: #0a1a2a;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #ff8c00;
        }
        .footer .contacts {
            margin-bottom: 15px;
        }
        .footer .contacts p {
            color: #888;
            font-size: 12px;
            margin: 5px 0;
        }
        .footer .contacts i {
            color: #ff8c00;
            margin-right: 8px;
        }
        .footer .copyright {
            color: #555;
            font-size: 10px;
            margin-top: 15px;
        }
        /* Espace supplémentaire pour capture d\'écran */
        .screenshot-padding {
            height: 40px;
        }
    </style>
</head>
<body>
    <div class="poster">
        <div class="screenshot-space">
            <!-- En-tête -->
            <div class="header">
                <img src="http://127.0.0.1:8000/logo.jpg" alt="Dieynaba GP Holding">
                <h1>DIEYNABA GP HOLDING</h1>
                <div class="slogan">Transport international • E-commerce • Logistique</div>
            </div>
            
            <!-- Corps -->
            <div class="body">
                <div class="promo-badge">✨ OFFRE PROMOTIONNELLE ✨</div>
                
                <div class="product-title">
                    <div class="label">PRODUIT EXCEPTIONNEL</div>
                    <div class="description">' . htmlspecialchars($description) . '</div>
                </div>
                
                <div class="features">
                    <div class="feature"><p>✈️ Livraison express</p></div>
                    <div class="feature"><p>🔒 Paiement sécurisé</p></div>
                    <div class="feature"><p>✅ Garantie 12 mois</p></div>
                    <div class="feature"><p>📦 Frais de douane offerts</p></div>
                </div>
                
                <div class="offer-box">
                    <p>PROMO SPÉCIALE</p>
                    <p class="price">-20%</p>
                    <p>sur votre première commande</p>
                </div>
                
                <div class="cta">
                    <div class="phone">📞 +33 7 58 68 63 48</div>
                    <div class="sub">Appelez-nous ou envoyez un message WhatsApp</div>
                </div>
            </div>
            
            <!-- Pied de page -->
            <div class="footer">
                <div class="contacts">
                    <p><i class="fas fa-envelope"></i> contact@dieynaba.com</p>
                    <p><i class="fab fa-whatsapp"></i> WhatsApp: +221 77 654 28 03</p>
                    <p><i class="fas fa-globe"></i> www.dieynaba.com</p>
                </div>
                <div class="copyright">
                    &copy; 2026 Dieynaba GP Holding - Tous droits réservés<br>
                    France: Saint-Denis | Dakar: Hann Maristes
                </div>
            </div>
            <div class="screenshot-padding"></div>
        </div>
    </div>
</body>
</html>';
    
    file_put_contents($full_path, $full_html);
    $generated_html = $full_html;
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .studio-card { border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 30px; }
    .btn-generate { background: linear-gradient(135deg, #ff8c00, #ffaa33); border: none; padding: 12px 30px; font-weight: bold; }
    .preview-card { background: #f5f5f5; border-radius: 20px; padding: 20px; overflow-x: auto; max-height: 650px; overflow-y: auto; }
    .preview-iframe { width: 100%; height: 650px; border: none; border-radius: 15px; background: #f0f0f0; }
</style>

<h2><i class="fas fa-magic"></i> Studio GP - Générateur de supports marketing</h2>
<p class="text-muted">Créez une affiche promotionnelle personnalisée (format HTML/CSS) - Parfaite pour captures d'écran et partage</p>

<div class="row">
    <div class="col-md-5">
        <div class="card studio-card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-edit"></i> Formulaire de création
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Description du produit / promotion</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Ex: iPhone 15 Pro Max - 256Go - Promotion exceptionnelle -20%" required></textarea>
                        <small class="text-muted">Saisissez le nom et les caractéristiques de votre produit</small>
                    </div>
                    <button type="submit" class="btn btn-generate w-100">
                        <i class="fas fa-image"></i> Générer l'affiche promotionnelle
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle"></i> Comment obtenir votre image
            </div>
            <div class="card-body">
                <ol class="small">
                    <li>Remplissez le formulaire ci-dessus</li>
                    <li>L'aperçu s'affichera à droite</li>
                    <li>Cliquez sur "Voir le fichier" pour l'ouvrir dans un nouvel onglet</li>
                    <li>Faites une <strong>capture d'écran</strong> (Ctrl+Shift+S ou impr écran)</li>
                    <li>Partagez l'image sur WhatsApp, réseaux sociaux ou email</li>
                </ol>
                <?php if ($saved_path && file_exists(__DIR__ . '/' . $saved_path)): ?>
                <div class="alert alert-success mt-2">
                    <i class="fas fa-check-circle"></i> Dernier fichier : <strong><?= basename($saved_path) ?></strong><br>
                    <a href="<?= $saved_path ?>" target="_blank" class="btn btn-sm btn-success mt-2">
                        <i class="fas fa-external-link-alt"></i> Voir le fichier généré
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card studio-card">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-eye"></i> Aperçu de l'affiche promotionnelle
            </div>
            <div class="card-body preview-card">
                <?php if ($saved_path && file_exists(__DIR__ . '/' . $saved_path)): ?>
                    <iframe src="<?= $saved_path ?>" class="preview-iframe" title="Aperçu promotion"></iframe>
                <?php else: ?>
                    <div class="text-muted text-center p-5">
                        <i class="fas fa-image fa-4x mb-3"></i>
                        <p>Remplissez le formulaire pour générer votre affiche promotionnelle</p>
                        <p class="small">Un aperçu apparaîtra ici avec votre texte personnalisé</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
