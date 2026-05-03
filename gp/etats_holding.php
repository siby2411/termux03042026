<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<h2><i class="fas fa-chart-pie"></i> États financiers consolidés - Dieynaba GP Holding</h2>

<?php
// Statistiques Fret
$fret_colis = $pdo->query("SELECT SUM(montant_encaisse) as total FROM colis")->fetchColumn();
$fret_colis_nb = $pdo->query("SELECT COUNT(*) FROM colis")->fetchColumn();

// Statistiques Joaillerie
$bijoux_ca = $pdo->query("SELECT SUM(prix_vente * stock) as total FROM bijouterie")->fetchColumn();
$bijoux_nb = $pdo->query("SELECT COUNT(*) FROM bijouterie")->fetchColumn();

// Statistiques Négoce
$negoce_ca = $pdo->query("SELECT SUM(prix_vente * stock) as total FROM negoce")->fetchColumn();
$negoce_nb = $pdo->query("SELECT COUNT(*) FROM negoce")->fetchColumn();

// Charges totales
$total_charges = $pdo->query("SELECT SUM(montant) FROM charges")->fetchColumn();

$ca_total = ($fret_colis ?? 0) + ($bijoux_ca ?? 0) + ($negoce_ca ?? 0);
$benefice = $ca_total - ($total_charges ?? 0);
?>

<div class="row mb-4">
    <div class="col-md-3"><div class="card text-white bg-primary"><div class="card-body"><h5>✈️ FRET</h5><h2><?= number_format($fret_colis ?? 0, 0) ?> €</h2><small><?= $fret_colis_nb ?> colis</small></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-warning"><div class="card-body"><h5>💎 BIJOUTERIE</h5><h2><?= number_format($bijoux_ca ?? 0, 0) ?> €</h2><small><?= $bijoux_nb ?> références</small></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-info"><div class="card-body"><h5>📱 NÉGOCE</h5><h2><?= number_format($negoce_ca ?? 0, 0) ?> €</h2><small><?= $negoce_nb ?> produits</small></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-success"><div class="card-body"><h5>💰 CA TOTAL</h5><h2><?= number_format($ca_total, 0) ?> €</h2><small>Bénéfice: <?= number_format($benefice, 0) ?> €</small></div></div></div>
</div>

<canvas id="secteurChart" height="100"></canvas>
<script>
new Chart(document.getElementById("secteurChart"), { type: 'bar', data: { labels: ['Fret', 'Joaillerie', 'Négoce'], datasets: [{ label: 'Chiffre d\'affaires (€)', data: [<?= $fret_colis ?: 0 ?>, <?= $bijoux_ca ?: 0 ?>, <?= $negoce_ca ?: 0 ?>], backgroundColor: ['#007bff', '#ffc107', '#17a2b8'] }] } });
</script>

<div class="row mt-4">
    <div class="col-12 text-center">
        <p><strong>📍 Siège social :</strong> Hann Maristes - À côté École Franco-Japonaise, Dakar, Sénégal</p>
        <p><strong>📞 Contacts :</strong> France: +33 7 58 68 63 48 | Sénégal: +221 33 888 88 88 | WhatsApp: +221 77 654 28 03</p>
    </div>
</div>
<?php include('footer.php'); ?>
<!-- Section Mode & Accessoires -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #8B4513; color: white;">
                <i class="fas fa-shoe-prints"></i> Mode & Accessoires (Luxe)
            </div>
            <div class="card-body">
                <?php
                $mode_ca = $pdo->query("SELECT SUM(prix_vente * stock) as total FROM mode_accessoires")->fetchColumn();
                $mode_nb = $pdo->query("SELECT COUNT(*) FROM mode_accessoires")->fetchColumn();
                $mode_stock = $pdo->query("SELECT SUM(stock) FROM mode_accessoires")->fetchColumn();
                ?>
                <div class="row">
                    <div class="col-md-4"><strong>👠 Produits :</strong> <?= $mode_nb ?></div>
                    <div class="col-md-4"><strong>📦 Stock total :</strong> <?= $mode_stock ?></div>
                    <div class="col-md-4"><strong>💰 CA potentiel :</strong> <?= number_format($mode_ca, 2) ?> €</div>
                </div>
            </div>
        </div>
    </div>
</div>
