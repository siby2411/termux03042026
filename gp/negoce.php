<?php
require_once 'db_connect.php';
include('header.php');

$produits = $pdo->query("SELECT * FROM negoce WHERE stock > 0 ORDER BY date_ajout DESC")->fetchAll();
?>
<style>
    .hero-negoce { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); color: white; padding: 40px; text-align: center; border-radius: 20px; margin-bottom: 30px; }
    .btn-negoce { background: #ff8c00; color: white; border: none; padding: 8px 20px; border-radius: 30px; }
    .showroom-info { background: #e9ecef; padding: 15px; border-radius: 15px; margin: 20px 0; text-align: center; }
    .product-img-card { height: 200px; object-fit: cover; width: 100%; border-radius: 15px 15px 0 0; background: #f5f5f5; }
    .card-produit { transition: transform 0.3s; border-radius: 15px; overflow: hidden; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .card-produit:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
    .categorie-badge { background: #ff8c00; color: white; padding: 3px 10px; border-radius: 20px; font-size: 11px; }
</style>

<div class="hero-negoce">
    <i class="fas fa-store fa-4x"></i>
    <h1>Dieynaba GP Holding - Departement Negoce</h1>
    <p>✨ Visitez notre showroom a Hann Maristes ✨</p>
    <p class="mt-2"><strong>📍 A cote de l'Ecole Franco-Japonaise - Hann Maristes, Dakar</strong></p>
    <p class="lead">Des produits haut de gamme a des prix qui defient toute concurrence !</p>
</div>

<div class="showroom-info">
    <i class="fas fa-map-marker-alt fa-2x"></i>
    <h3>Ouvert du Lundi au Samedi - 9h a 19h</h3>
    <p>📞 Contact: +221 77 654 28 03 | +33 7 58 68 63 48</p>
</div>

<div class="row">
    <?php foreach ($produits as $p): 
        // Déterminer l'image à afficher
        $image = !empty($p['image']) ? $p['image'] : '';
        
        // Si l'image n'existe pas physiquement, utiliser une image par défaut
        if (empty($image) || !file_exists($image)) {
            // Image par catégorie
            $default_images = [
                'telephone' => 'uploads/negoce/iphone.jpg',
                'informatique' => 'uploads/negoce/pc_gamer.jpg',
                'electromenager' => 'uploads/negoce/frigo.jpg',
                'mobilier' => 'uploads/negoce/canape.jpg',
                'vehicule' => 'uploads/negoce/moto.jpg'
            ];
            $image = isset($default_images[$p['categorie']]) ? $default_images[$p['categorie']] : 'assets/images/default_image.svg';
        }
    ?>
    <div class="col-md-3 mb-4">
        <div class="card-produit card h-100">
            <img src="<?= htmlspecialchars($image) ?>" class="product-img-card" alt="<?= htmlspecialchars($p['nom']) ?>">
            <div class="card-body">
                <h5><?= htmlspecialchars($p['nom']) ?></h5>
                <span class="categorie-badge"><?= $p['categorie'] ?></span>
                <span class="badge bg-secondary ms-1"><?= $p['etat'] ?></span>
                <p class="mt-2 small text-muted"><?= substr($p['description'], 0, 60) ?>...</p>
                <h4 class="text-primary mt-2"><?= number_format($p['prix_vente'], 2) ?> €</h4>
                <small class="text-muted">Garantie: <?= $p['garantie_mois'] ?> mois</small><br>
                <button class="btn-negoce w-100 mt-2" onclick="window.open('https://wa.me/33758686348?text=Je souhaite commander <?= urlencode($p['nom']) ?> - <?= $p['prix_vente'] ?>€','_blank')">
                    <i class="fab fa-whatsapp"></i> Commander
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include('footer.php'); ?>
