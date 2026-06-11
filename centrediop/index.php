<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Centre Diop - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="d-flex">
    <div class="sidebar-wrapper" style="width: 250px;"><?php include 'includes/sidebar.php'; ?></div>
    <div class="flex-grow-1 p-4">
        <h1>Tableau de Bord</h1>
        <div class="row mt-4 g-3">
            <div class="col-md-3"><a href="/modules/admin/batiments_form.php" class="btn btn-outline-secondary w-100 p-3">Bâtiments</a></div>
            <div class="col-md-3"><a href="/modules/stock/index.php" class="btn btn-outline-primary w-100 p-3">Stock</a></div>
            <div class="col-md-3"><a href="/modules/facturation/index.php" class="btn btn-outline-success w-100 p-3">Facturation</a></div>
            <div class="col-md-3"><a href="/modules/pharmacie/index.php" class="btn btn-outline-warning w-100 p-3">Pharmacie</a></div>
        </div>
    </div>
</div>
</body>
</html>
