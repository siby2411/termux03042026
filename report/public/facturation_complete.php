<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Facturation";
$page_icon = "file-invoice";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';
$clients = $pdo->query("SELECT * FROM TIERS WHERE type = 'CLIENT'")->fetchAll();
$articles = $pdo->query("SELECT * FROM ARTICLES_STOCK ORDER BY code_article")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generer') {
    $client_id = $_POST['client_id'];
    $date = $_POST['date'];
    $total_ht = 0;
    $lignes = [];
    foreach ($_POST['quantite'] as $art_id => $qte) {
        if ($qte > 0) {
            $prix = (float)$_POST['prix'][$art_id];
            $remise = (float)($_POST['remise'][$art_id] ?? 0);
            $montant = $qte * $prix;
            $montant_remise = $montant * $remise / 100;
            $net = $montant - $montant_remise;
            $total_ht += $net;
        }
    }
    $tva = $total_ht * 0.18;
    $ttc = $total_ht + $tva;
    $numero = "FACT-" . date('Ymd') . "-" . rand(100,999);
    // Insertion facture (table FACTURES_VENTE)
    $stmt = $pdo->prepare("INSERT INTO FACTURES_VENTE (numero, date_facture, client_id, montant_ht, tva, montant_ttc) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$numero, $date, $client_id, $total_ht, $tva, $ttc]);
    // Écritures comptables
    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES 
            (?, ?, 411, 701, ?, ?, 'VENTE'),
            (?, ?, 411, 4451, ?, ?, 'VENTE')";
    $stmt2 = $pdo->prepare($sql);
    $stmt2->execute([$date, "Facture $numero (HT)", $total_ht, $numero, $date, "TVA sur $numero", $tva, $numero]);
    $message = "✅ Facture $numero générée - Total TTC : " . number_format($ttc,0,',',' ') . " F";
}
?>
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-primary text-white">Facturation</div>
<div class="card-body">
<?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<form method="POST"><input type="hidden" name="action" value="generer">
<div class="row"><div class="col-md-6"><label>Client</label><select name="client_id" class="form-select" required><?php foreach($clients as $c): ?><option value="<?= $c['id'] ?>"><?= $c['raison_sociale'] ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label>Date</label><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>"></div></div>
<h6 class="mt-3">Articles</h6>
<table class="table table-bordered"><thead><tr><th>Article</th><th>Qté</th><th>Prix unit.</th><th>Remise %</th></tr></thead>
<tbody><?php foreach($articles as $a): ?><tr><td><?= $a['code_article'] ?> - <?= $a['libelle'] ?><input type="hidden" name="prix[<?= $a['id'] ?>]" value="<?= $a['prix_unitaire'] ?>"></td>
<td><input type="number" name="quantite[<?= $a['id'] ?>]" class="form-control" value="0" step="1"></td>
<td class="text-end"><?= number_format($a['prix_unitaire'],0,',',' ') ?> F</td>
<td><input type="number" name="remise[<?= $a['id'] ?>]" class="form-control" value="0" step="1"></td></tr><?php endforeach; ?></tbody></table>
<div class="text-center mt-3"><button type="submit" class="btn-omega">Générer la facture</button></div></form>
</div></div></div></div>
<?php include 'inc_footer.php'; ?>
