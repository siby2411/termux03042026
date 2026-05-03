<?php include('../../includes/header.php'); require_once('../../config.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO demandes_credit (nom_complet, telephone, montant_demande) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['nom'], $_POST['tel'], $_POST['montant']]);
    $success = true;
}
?>
<div class="container py-5"><h1 class="text-success">💰 Inclusion Financière</h1><div class="row"><div class="col-md-6"><div class="card shadow"><div class="card-header bg-success text-white"><h3>📝 Demande crédit</h3></div><div class="card-body"><?php if(isset($success)) echo '<div class="alert alert-success">✅ Demande envoyée</div>'; ?><form method="POST"><input type="text" name="nom" class="form-control mb-2" placeholder="Nom complet" required><input type="tel" name="tel" class="form-control mb-2" placeholder="Téléphone" required><input type="number" name="montant" class="form-control mb-2" placeholder="Montant FCFA" required><button class="btn btn-success w-100">Envoyer</button></form></div></div></div><div class="col-md-6"><div class="card shadow"><div class="card-header bg-info text-white"><h3>📊 Chiffres</h3></div><div class="card-body"><p>📈 500+ crédits accordés<br>💰 500M FCFA injectés<br>👩 60% de femmes<br>✅ 94% remboursement</p></div></div></div></div></div>
<?php include('../../includes/footer.php'); ?>
