<?php
session_start();
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['COMPTABLE','ADMIN'])){ header("Location: index.php"); exit(); }
require_once __DIR__ . '/../includes/db.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Comptable - Reporting</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-white">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Comptable</h1>
    <div>
      <a class="btn btn-outline-secondary" href="dashboard.php">Retour</a>
      <a class="btn btn-danger" href="logout.php">Déconnexion</a>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <div class="card p-3">
        <h5>Nouvelle écriture</h5>
        <form method="post" action="comptable_dashboard.php">
          <div class="mb-2">
            <input name="date_operation" type="date" class="form-control" required>
          </div>
          <div class="mb-2">
            <input name="libelle" placeholder="Libellé" class="form-control">
          </div>
          <div class="row g-2">
            <div class="col"><input name="compte_debite_id" placeholder="Compte débité" class="form-control" required></div>
            <div class="col"><input name="compte_credite_id" placeholder="Compte crédité" class="form-control" required></div>
            <div class="col"><input name="montant" placeholder="Montant" class="form-control" required></div>
          </div>
          <div class="mt-2"><button class="btn btn-success" type="submit">Enregistrer</button></div>
        </form>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-3">
        <h5>Actions rapides</h5>
        <a class="btn btn-primary mb-2" href="upload_form.php">Importer CSV</a>
        <a class="btn btn-primary mb-2" href="balance_view.php">Voir Balance</a>
        <a class="btn btn-primary" href="etats_financiers_view.php">États financiers</a>
      </div>
    </div>
  </div>




<?php
// Traitement POST simple (saisie manuelle)
if($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['compte_debite_id'])){
    $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_operation, libelle, compte_debite_id, compte_credite_id, montant, societe_id)
                          VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->execute([$_POST['date_operation'], $_POST['libelle'], intval($_POST['compte_debite_id']), intval($_POST['compte_credite_id']), floatval(str_replace(',','.',$_POST['montant']))]);
    echo "<div class='alert alert-success'>Écriture enregistrée</div>";
}
?>

  <h5>Dernières écritures</h5>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>Date</th><th>Libellé</th><th>Débit</th><th>Crédit</th><th class="text-end">Montant</th></tr></thead>
      <tbody>
<?php
$stmt = $pdo->query("SELECT date_operation, libelle, compte_debite_id, compte_credite_id, montant FROM ECRITURES_COMPTABLES ORDER BY date_operation DESC LIMIT 30");
while($r=$stmt->fetch(PDO::FETCH_ASSOC)){
    echo "<tr><td>{$r['date_operation']}</td><td>".htmlspecialchars($r['libelle'])."</td>
          <td>{$r['compte_debite_id']}</td><td>{$r['compte_credite_id']}</td><td class='text-end'>".number_format($r['montant'],2,","," ")."</td></tr>";
}
?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
