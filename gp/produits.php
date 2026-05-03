<?php require_once 'db_connect.php'; include('header.php'); ?>
<style>
    .produit-card { background: white; border-radius: 16px; overflow: hidden; width: 260px; margin: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .produit-card img { width: 100%; height: 180px; object-fit: cover; }
    .produit-info { padding: 12px; }
    .prix { font-size: 1.4rem; font-weight: bold; color: #ff8c00; }
    .stock { font-size: 0.8rem; color: #666; }
    .produits-grid { display: flex; flex-wrap: wrap; justify-content: center; }
</style>
<h2><i class="fas fa-shopping-basket"></i> Boutique Dieynaba Product Sénégal</h2>
<form method="get" style="text-align: center; margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <button class="btn"><i class="fas fa-search"></i> Chercher</button>
</form>
<div class="produits-grid">
<?php
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE ? OR description LIKE ? AND stock > 0 ORDER BY nom");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM produits WHERE stock > 0 ORDER BY nom");
}
$produits = $stmt->fetchAll();
foreach ($produits as $p):
    $img = (!empty($p['image']) && file_exists($p['image'])) ? $p['image'] : 'https://placehold.co/400x200?text='.urlencode($p['nom']);
?>
<div class="produit-card">
    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['nom']) ?>">
    <div class="produit-info">
        <h3><?= htmlspecialchars($p['nom']) ?></h3>
        <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
        <div class="prix"><?= number_format($p['prix'],2) ?> €</div>
        <div class="stock">Stock disponible : <?= $p['stock'] ?></div>
        <button class="btn-wa" onclick="window.open('https://wa.me/33758686348?text=Je souhaite commander <?= urlencode($p['nom']) ?> (<?= $p['prix'] ?>€)','_blank')"><i class="fab fa-whatsapp"></i> Commander</button>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php include('footer.php'); ?>
