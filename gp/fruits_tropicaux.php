<?php
require_once 'db_connect.php';
include('header.php');

$produits = $pdo->query("SELECT * FROM fruits_tropicaux ORDER BY date_ajout DESC")->fetchAll();
$sponsors = $pdo->query("SELECT * FROM sponsors WHERE statut = 'actif' ORDER BY date_ajout DESC LIMIT 4")->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .hero-fruits { background: linear-gradient(135deg, #2c5f2d, #97bc62); color: white; padding: 40px; text-align: center; border-radius: 20px; margin-bottom: 30px; }
    .sponsor-slider { overflow: hidden; white-space: nowrap; background: #f8f9fa; padding: 15px; border-radius: 50px; margin: 20px 0; }
    .sponsor-slider span { display: inline-block; margin: 0 20px; font-weight: bold; color: #2c5f2d; }
    .product-card { transition: transform 0.3s; border-radius: 15px; overflow: hidden; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .product-card:hover { transform: translateY(-5px); }
    .product-img { height: 200px; object-fit: cover; width: 100%; }
    .sponsor-card { transition: all 0.3s ease; border-radius: 15px; cursor: pointer; height: 150px; display: flex; align-items: center; justify-content: center; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .sponsor-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); border: 1px solid #ff8c00; }
    .partnership-card { background: #fffbf0; border-left: 4px solid #ff8c00; border-radius: 15px; padding: 25px; margin: 30px 0; }
</style>

<div class="hero-fruits">
    <i class="fas fa-leaf fa-4x"></i>
    <h1>Dieynaba GP Holding - Valorisation des fruits tropicaux</h1>
    <p>Mangue, ditakh, bissap, baobab... La transformation locale pour une insertion mondiale</p>
</div>

<!-- Bannière publicitaire défilante (sponsors) -->
<div class="sponsor-slider">
    <span>🌟 Sponsors :</span>
    <?php foreach ($sponsors as $s): ?>
        <span>🏢 <?= htmlspecialchars($s['nom']) ?></span>
    <?php endforeach; ?>
    <span>🏢 Devenez sponsor</span>
</div>

<!-- Galerie des produits -->
<div class="row">
    <?php foreach ($produits as $p): ?>
    <div class="col-md-3 mb-4">
        <div class="card product-card">
            <img src="<?= !empty($p['image_url']) ? $p['image_url'] : 'https://placehold.co/400x250/2c5f2d/white?text='.urlencode($p['nom']) ?>" class="product-img" alt="<?= htmlspecialchars($p['nom']) ?>">
            <div class="card-body">
                <h5><?= htmlspecialchars($p['nom']) ?></h5>
                <p><?= htmlspecialchars(substr($p['description'], 0, 80)) ?>...</p>
                <?php if ($p['prix'] > 0): ?>
                    <div class="fw-bold text-success"><?= number_format($p['prix'], 2) ?> €</div>
                <?php endif; ?>
                <?php if ($p['badge']): ?>
                    <span class="badge bg-success"><?= htmlspecialchars($p['badge']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Section Partenariat & Sponsoring -->
<div class="partnership-card">
    <h3><i class="fas fa-handshake"></i> Proposition de partenariat promotionnel – Dieynaba GP Holding</h3>
    <p class="mt-3">Dans le cadre de notre engagement pour la valorisation des produits agricoles africains et leur intégration dans les chaînes de valeur mondiales, nous lançons une vitrine dédiée à la transformation des fruits tropicaux sénégalais (mangue, ditakh, bissap, etc.).</p>
    <p>Nous pensons que les pays africains doivent saisir les grappes de convergence de la mondialisation par la transformation locale de leurs propres ressources. C'est dans cet esprit que nous sollicitons votre entreprise pour un partenariat sponsoring.</p>
    <p class="fw-bold">Notre plateforme propose un espace publicitaire dédié avec bannière défilante et galerie de produits. En contrepartie, votre logo et votre lien seront mis en avant auprès de notre audience.</p>
    <div class="text-end">
        <em>L'équipe Dieynaba GP Holding</em>
    </div>
</div>

<!-- Espace sponsors (4 emplacements de logo) -->
<h3 class="mt-5"><i class="fas fa-star"></i> Nos sponsors & partenaires</h3>
<div class="row g-4 mt-2 mb-5">
    <?php foreach ($sponsors as $s): ?>
    <div class="col-md-3">
        <div class="sponsor-card text-center p-4">
            <?php if (!empty($s['logo_url'])): ?>
                <img src="<?= $s['logo_url'] ?>" style="max-height: 80px; max-width: 100%;">
            <?php else: ?>
                <i class="fas fa-building fa-3x text-primary"></i>
            <?php endif; ?>
            <p class="mt-2 fw-bold"><?= htmlspecialchars($s['nom']) ?></p>
            <small><?= htmlspecialchars($s['description']) ?></small>
            <?php if (!empty($s['site_web']) && $s['site_web'] != '#'): ?>
                <div class="mt-2"><a href="<?= $s['site_web'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">Visiter</a></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="alert alert-info mt-4">
    <strong>📢 Soutenez la transformation locale !</strong> Devenez sponsor et associez votre image à une filière durable et solidaire.<br>
    <strong>Contact sponsoring :</strong> <i class="fab fa-whatsapp"></i> +33 7 58 68 63 48 | <i class="fas fa-envelope"></i> contact@dieynaba.com
</div>

<?php include('footer.php'); ?>
