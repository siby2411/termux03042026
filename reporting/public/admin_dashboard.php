

<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=="ADMIN"){ header("Location: index.php"); exit(); }
require_once __DIR__ . '/../includes/db.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin - Reporting</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Admin Dashboard</h1>
    <div>
      <a class="btn btn-outline-secondary" href="dashboard.php">Retour</a>
      <a class="btn btn-danger" href="logout.php">Déconnexion</a>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Importer écritures</h5>
        <p>Charger les CSV/écritures comptables</p>
        <a class="btn btn-primary" href="upload_form.php">Importer</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Balance (6 colonnes)</h5>
        <p>Consulter la balance synthétique</p>
        <a class="btn btn-primary" href="balance_view.php">Voir la Balance</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h5>États financiers</h5>
        <p>Bilan & Compte de résultat</p>
        <a class="btn btn-primary" href="etats_financiers_view.php">Générer</a>
      </div>
    </div>
  </div>

  <h4>Dernières écritures (20)</h4>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>#</th><th>Date</th><th>Libellé</th><th>Débit (compte)</th><th>Crédit (compte)</th><th>Montant</th></tr></thead>
      <tbody>
<?php
$stmt = $pdo->query("SELECT id, date_operation, libelle, compte_debite_id, compte_credite_id, montant 
                     FROM ECRITURES_COMPTABLES ORDER BY date_operation DESC LIMIT 20");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
    echo "<tr><td>{$r['id']}</td><td>{$r['date_operation']}</td><td>".htmlspecialchars($r['libelle']??'')."</td>
          <td>{$r['compte_debite_id']}</td><td>{$r['compte_credite_id']}</td><td class='text-end'>".number_format($r['montant'],2,","," ")."</td></tr>";
}
?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
