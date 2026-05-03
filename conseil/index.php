<?php 
include('includes/header.php'); 
require_once('config.php');
$actualites = $pdo->query("SELECT * FROM actualites ORDER BY date_publication DESC LIMIT 4")->fetchAll();
?>
<div class="hero text-white text-center py-5">
    <div class="container">
        <h1 class="display-3">Bienvenue à Velingara</h1>
        <p class="lead fs-3">Carrefour de prospérité entre Sénégal, Guinées et Mali</p>
        <div class="mt-4">
            <a href="/modules/agriculture/barrage.php" class="btn btn-success btn-lg me-3"><i class="fas fa-water"></i> Barrage Anambe</a>
            <a href="/modules/religion/gamou.php" class="btn btn-success btn-lg me-3" style="background:#2E7D32;"><i class="fas fa-mosque"></i> Gamou Dakaar</a>
            <a href="/modules/finance/credit.php" class="btn btn-warning btn-lg"><i class="fas fa-hand-holding-usd"></i> Micro-crédits</a>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <h2 class="mb-4"><i class="fas fa-newspaper"></i> 📢 Actualités</h2>
            <?php foreach($actualites as $actu): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success"><?php echo htmlspecialchars($actu['titre']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($actu['contenu']); ?></p>
                    <small class="text-muted"><i class="fas fa-clock"></i> <?php echo date('d/m/Y', strtotime($actu['date_publication'])); ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white mb-4 shadow">
                <div class="card-body text-center">
                    <i class="fas fa-user-tie fa-4x mb-3"></i>
                    <h3>Président</h3>
                    <h4 class="text-warning">Ibrahima Barry</h4>
                    <p>"Engagé pour l'émancipation financière et le développement territorial"</p>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white"><h5><i class="fas fa-chart-line"></i> 🎯 Chiffres clés</h5></div>
                <div class="card-body"><ul><li>250 emplois créés</li><li>500M FCFA investis</li><li>15 infrastructures</li><li>5000 bénéficiaires</li></ul></div>
            </div>
            <div class="card shadow">
                <div class="card-header bg-warning"><h5><i class="fas fa-phone"></i> 📞 Contact Directeur</h5></div>
                <div class="card-body text-center"><h5>Mohamed Siby</h5><p>📞 77 654 28 03<br>✉️ msiby@conseilvelingara.sn</p></div>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>
