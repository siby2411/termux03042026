<?php include('../../includes/header.php'); require_once('../../config.php');
$infras = $pdo->query("SELECT * FROM infrastructures")->fetchAll();
?>
<div class="container py-5"><h1>🏗️ Infrastructures Santé & Sport</h1><div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>Infrastructure</th><th>Type</th><th>Montant</th><th>Progression</th></tr></thead><tbody><?php foreach($infras as $i): ?><tr><td><?php echo $i['nom']; ?></td><td><?php echo $i['type']; ?></td><td><?php echo number_format($i['montant_investi'],0,',',' '); ?> F</td><td><div class="progress"><div class="progress-bar bg-success" style="width:<?php echo $i['pourcentage_realise']; ?>%"><?php echo $i['pourcentage_realise']; ?>%</div></div></td></tr><?php endforeach; ?></tbody></table></div><div class="alert alert-info">🏆 Initiative: Académie de Football de Velingara</div></div>
<?php include('../../includes/footer.php'); ?>
