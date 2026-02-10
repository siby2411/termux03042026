<?php
function render(\$title, \$content){
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars(\$title) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body{background:#f6f8fb;color:#1f2937}
.card-hero{border-radius:10px;box-shadow:0 6px 18px rgba(15,23,42,0.06)}
.small-muted{color:#6b7280}
.table-compact td, .table-compact th{padding:.45rem .6rem}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background:linear-gradient(90deg,#0d6efd,#6f42c1);">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php"><i class="fa fa-balance-scale-left"></i> SynthesePro</a>
    <div class="d-flex">
      <a class="btn btn-outline-light me-2" href="balance_view.php">Balance</a>
      <a class="btn btn-outline-light me-2" href="ventilation.php">Ventilation</a>
      <a class="btn btn-light" href="export_excel.php">Export Excel</a>
    </div>
  </div>
</nav>
<main class="container my-4">
<div class="card card-hero p-3 mb-4">
  <h3 class="mb-0"><?= htmlspecialchars(\$title) ?></h3>
  <div class="small-muted">Reporting SYSCOHADA — Synthèse & Ventilation</div>
</div>
<?= \$content ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>
