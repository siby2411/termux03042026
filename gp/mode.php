<?php
require_once 'db_connect.php';
include('header.php');
$produits = $pdo->query("SELECT * FROM mode_accessoires WHERE stock > 0 ORDER BY date_ajout DESC")->fetchAll();
?>
<style>
    .hero-mode { background: linear-gradient(135deg, #8B4513, #D2691E, #F4A460); color: white; padding: 40px; text-align: center; border-radius: 20px; margin-bottom: 30px; }
    .btn-mode { background: #8B4513; color: white; border: none; padding: 10px 20px; border-radius: 30px; }
    .mode-card { transition: transform 0.3s; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); height: 100%; }
    .mode-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
    .mode-img { height: 220px; object-fit: cover; width: 100%; }
</style>
<div class="hero-mode">
    <i class="fas fa-shoe-prints fa-4x"></i>
    <i class="fas fa-shopping-bag fa-4x mx-3"></i>
    <i class="fas fa-gem fa-4x"></i>
    <h1>Dieynaba GP Holding - Mode & Accessoires</h1>
    <p>L'élégance et le luxe à portée de main - Chaussures, Sacs, Accessoires de créateurs</p>
</div>
<div class="row">
    <?php foreach ($produits as $p): ?>
    <div class="col-md-3 mb-4">
        <div class="mode-card card h-100">
            <img src="<?= !empty($p['image']) ? $p['image'] : 'https://placehold.co/400x300/8B4513/white?text='.urlencode($p['nom']) ?>" class="mode-img" alt="<?= htmlspecialchars($p['nom']) ?>">
            <div class="card-body">
                <h5><?= htmlspecialchars($p['nom']) ?></h5>
                <span class="badge bg-secondary"><?= $p['categorie'] ?></span>
                <span class="badge" style="background:#8B4513;"><?= $p['genre'] ?></span>
                <?php if (!empty($p['couleurs'])): ?>
                    <div><small>Couleurs: <?= $p['couleurs'] ?></small></div>
                <?php endif; ?>
                <h4 class="text-danger mt-2"><?= number_format($p['prix_vente'], 2) ?> €</h4>
                <button class="btn-mode w-100 mt-2" onclick="window.open('https://wa.me/33758686348?text=Je souhaite commander <?= urlencode($p['nom']) ?> - <?= $p['prix_vente'] ?>€','_blank')">🛒 Commander</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php include('footer.php'); ?>
