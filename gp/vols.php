<?php require_once 'header.php'; ?>
<style>
    .vol-card { background: white; border-radius: 16px; margin-bottom: 20px; overflow: hidden; display: flex; flex-wrap: wrap; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .vol-img { flex: 0 0 150px; background: #e0e0e0; text-align: center; padding: 20px; }
    .vol-img i { font-size: 4rem; color: #0a2b44; }
    .vol-info { flex: 1; padding: 15px; }
    .vol-status { font-weight: bold; color: #ff8c00; }
    .btn-vol { background: #ff8c00; color: white; padding: 8px 16px; border-radius: 30px; text-decoration: none; display: inline-block; }
</style>
<h2><i class="fas fa-plane"></i> Vols Sénégal ↔ France</h2>
<?php
$vols = $pdo->query("SELECT * FROM vols ORDER BY date_depart ASC")->fetchAll();
foreach ($vols as $v):
    $date_dep = new DateTime($v['date_depart']);
    $date_arr = new DateTime($v['date_arrivee_estimee']);
?>
<div class="vol-card">
    <div class="vol-img">
        <i class="fas fa-plane-departure"></i>
    </div>
    <div class="vol-info">
        <h3>Vol <?= htmlspecialchars($v['numero_vol']) ?> - <?= $v['depart_ville'] ?> → <?= $v['arrivee_ville'] ?></h3>
        <p>Départ : <?= $date_dep->format('d/m/Y H:i') ?> | Arrivée estimée : <?= $date_arr->format('d/m/Y H:i') ?></p>
        <p class="vol-status">Statut : <?= $v['statut'] ?></p>
        <?php if ($v['statut'] === 'planifie'): ?>
            <a href="#" class="btn-vol" onclick="alert('Réservation disponible via WhatsApp +33 7 58 68 63 48')">Réserver un espace</a>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<?php include('footer.php'); ?>
