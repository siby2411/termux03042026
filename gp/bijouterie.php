<?php
require_once 'db_connect.php';
include('header.php');
$bijoux = $pdo->query("SELECT * FROM bijouterie WHERE stock > 0 ORDER BY date_ajout DESC")->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .hero-bijoux { background: linear-gradient(135deg, #2c2c2c, #1a1a1a, #ffd700); color: #ffd700; padding: 40px; text-align: center; border-radius: 20px; margin-bottom: 30px; }
    .btn-commander { background: #ffd700; color: #1a1a1a; border: none; padding: 8px 20px; border-radius: 30px; font-weight: bold; }
</style>
<div class="hero-bijoux">
    <i class="fas fa-gem fa-4x"></i>
    <h1>Dieynaba GP Holding - Joaillerie & Bijouterie</h1>
    <p>L'excellence et l'élégance à portée de main - Collection prestige</p>
    <p class="mt-3">📞 Contact France: +33 7 58 68 63 48 | WhatsApp: +221 77 654 28 03</p>
</div>
<div class="row">
    <?php foreach ($bijoux as $b): ?>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <img src="<?= $b['image'] ?? 'https://placehold.co/300x200?text=Bijoux' ?>" class="card-img-top" style="height:220px; object-fit:cover;">
            <div class="card-body">
                <h5><?= htmlspecialchars($b['nom']) ?></h5>
                <span class="badge bg-warning"><?= $b['categorie'] ?></span> <span class="badge bg-info"><?= $b['matiere'] ?></span>
                <p class="mt-2"><?= substr($b['description'],0,80) ?>...</p>
                <h4 class="text-warning"><?= number_format($b['prix_vente'],2) ?> €</h4>
                <button class="btn-commander w-100" onclick="window.open('https://wa.me/33758686348?text=Je souhaite commander <?= urlencode($b['nom']) ?> - <?= $b['prix_vente'] ?>€','_blank')">💎 Commander</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php include('footer.php'); ?>
