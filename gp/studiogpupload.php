<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Dossiers
$upload_dir = __DIR__ . '/uploads/promos/';
$html_dir = __DIR__ . '/uploads/generated/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
if (!is_dir($html_dir)) mkdir($html_dir, 0777, true);

$generated_html = '';
$saved_path = '';
$image_relative = '';
$description = '';
$prix = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description = trim($_POST['description']);
    $prix = trim($_POST['prix'] ?? '');
    $image_relative = '';

    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = 'promo_' . date('Ymd_His') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename);
        $image_relative = 'uploads/promos/' . $filename;
    }

    // Génération du HTML complet (comme studiogp.php)
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
        .screenshot-space { padding: 30px; }
        .header {
            text-align: center;
            padding: 20px 30px 15px 30px;
            border-bottom: 2px solid #ff8c00;
            background: rgba(0,0,0,0.2);
        }
        .header img { max-height: 70px; margin-bottom: 10px; border-radius: 12px; }
        .header h1 { color: #ff8c00; font-size: 26px; letter-spacing: 2px; margin: 5px 0; }
        .header .slogan { color: #aaaaaa; font-size: 11px; }
        .body { padding: 30px; text-align: center; }
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
        .product-img {
            max-width: 80%;
            border-radius: 20px;
            margin: 15px auto;
            display: block;
            border: 2px solid #ff8c00;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .product-title {
            background: linear-gradient(135deg, #0f3460, #1a1a2e);
            border-radius: 20px;
            padding: 20px;
            margin: 20px 0;
        }
        .product-title .description {
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            line-height: 1.4;
        }
        .price-tag {
            font-size: 36px;
            font-weight: bold;
            color: #ff8c00;
            margin: 15px 0;
        }
        .features {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin: 25px 0;
        }
        .feature {
            background: rgba(255,140,0,0.1);
            border: 1px solid #ff8c00;
            border-radius: 15px;
            padding: 10px 18px;
        }
        .feature p { color: #ff8c00; font-size: 12px; margin: 0; }
        .cta {
            background: #ff8c00;
            border-radius: 15px;
            padding: 15px;
            margin: 25px 0;
        }
        .cta .phone { color: #1a1a2e; font-size: 22px; font-weight: bold; }
        .footer {
            background: #0a1a2a;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #ff8c00;
        }
        .footer p { color: #888; font-size: 11px; margin: 5px 0; }
        .screenshot-padding { height: 30px; }
    </style>
</head>
<body>
    <div class="poster">
        <div class="screenshot-space">
            <div class="header">
                <img src="http://127.0.0.1:8000/logo.jpg" alt="Dieynaba GP Holding">
                <h1>DIEYNABA GP HOLDING</h1>
                <div class="slogan">Transport international • E-commerce • Logistique</div>
            </div>
            <div class="body">
                <div class="promo-badge">🔥 OFFRE PROMOTIONNELLE 🔥</div>';
    if ($image_relative) {
        $full_html .= '<img src="http://127.0.0.1:8000/' . $image_relative . '" class="product-img" alt="Produit">';
    }
    $full_html .= '<div class="product-title">
                        <div class="description">' . htmlspecialchars($description) . '</div>
                    </div>
                    <div class="price-tag">' . number_format(floatval($prix), 2) . ' €</div>
                    <div class="features">
                        <div class="feature"><p>✈️ Livraison express</p></div>
                        <div class="feature"><p>🔒 Paiement sécurisé</p></div>
                        <div class="feature"><p>✅ Garantie 12 mois</p></div>
                    </div>
                    <div class="cta">
                        <div class="phone">📞 +221 77 654 28 03</div>
                        <div style="color:#1a1a2e; font-size:12px;">WhatsApp / Appel</div>
                    </div>
                </div>
                <div class="footer">
                    <p><i class="fab fa-whatsapp"></i> WhatsApp: +221 77 654 28 03 | 📧 contact@dieynaba.com</p>
                    <p>📍 Showroom: Hann Maristes (Dakar) | Antenne: Saint-Denis (France)</p>
                </div>
                <div class="screenshot-padding"></div>
            </div>
        </div>
    </div>
</body>
</html>';

    // Sauvegarde du fichier HTML
    $filename = 'generated_' . date('Ymd_His') . '.html';
    $saved_path = 'uploads/generated/' . $filename;
    file_put_contents(__DIR__ . '/' . $saved_path, $full_html);
    $generated_html = $full_html;
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .upload-area { border: 2px dashed #ff8c00; border-radius: 15px; padding: 20px; text-align: center; cursor: pointer; }
    .preview-iframe { width: 100%; height: 650px; border: none; border-radius: 15px; background: #f0f0f0; }
</style>

<h2><i class="fas fa-upload"></i> Studio GP Upload - Créateur de promotions personnalisées</h2>
<p class="text-muted">Ajoutez une image, une description et un prix. Le support HTML est automatiquement enregistré.</p>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">📤 Formulaire</div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" id="promoForm">
                    <div class="mb-3">
                        <label class="form-label">🖼️ Image du produit</label>
                        <div class="upload-area" onclick="document.getElementById('imageFile').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x text-warning"></i>
                            <p>Cliquez pour sélectionner une image</p>
                        </div>
                        <input type="file" name="image" id="imageFile" class="d-none" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">📝 Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Ex: Ordinateur Dell Core i7, 16Go RAM, 512Go SSD" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">💰 Prix promotionnel (€)</label>
                        <input type="number" step="0.01" name="prix" class="form-control" placeholder="Ex: 210" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-magic"></i> Générer la promotion</button>
                </form>
            </div>
        </div>
        
        <?php if ($saved_path && file_exists(__DIR__ . '/' . $saved_path)): ?>
        <div class="card">
            <div class="card-header bg-success text-white">✅ Support généré</div>
            <div class="card-body">
                <p><strong>Fichier enregistré :</strong> <code><?= $saved_path ?></code></p>
                <a href="<?= $saved_path ?>" target="_blank" class="btn btn-sm btn-outline-primary">📄 Voir le fichier HTML</a>
                <button id="shareWhatsAppBtn" class="btn btn-sm btn-success mt-2 w-100">
                    <i class="fab fa-whatsapp"></i> Partager sur WhatsApp
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-warning text-dark">🎯 Aperçu</div>
            <div class="card-body">
                <?php if ($saved_path && file_exists(__DIR__ . '/' . $saved_path)): ?>
                    <iframe src="<?= $saved_path ?>" class="preview-iframe" title="Aperçu promotion"></iframe>
                <?php else: ?>
                    <div class="text-muted text-center p-5">
                        <i class="fas fa-image fa-4x mb-3"></i>
                        <p>Remplissez le formulaire pour générer votre promotion</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($saved_path): ?>
document.getElementById('shareWhatsAppBtn').addEventListener('click', function() {
    let text = "🔥 PROMOTION EXCEPTIONNELLE 🔥\n\n<?= addslashes($description) ?>\n💰 Prix : <?= number_format(floatval($prix), 2) ?> €\n\n📞 Commandez au +221 77 654 28 03\nDieynaba GP Holding - Livraison France/Sénégal";
    window.open('https://wa.me/221776542803?text=' + encodeURIComponent(text), '_blank');
});
<?php endif; ?>
</script>
<?php include('footer.php'); ?>
