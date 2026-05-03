<?php include '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-boxes text-primary"></i> Gestion du Stock & Magasin</h2>
    <a href="formulaire_piece.php" class="btn btn-dark"><i class="fas fa-plus"></i> Nouvelle Référence</a>
</div>

<div class="row">
    <?php 
    // Ici vous feriez un SELECT * FROM pieces_detachees
    // Simulation visuelle :
    ?>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-bottom border-primary border-4">
            <h6 class="text-muted">Valeur du Stock</h6>
            <h4 class="fw-bold">1.450.000 FCFA</h4>
        </div>
    </div>
</div>

<table class="table table-hover mt-4 bg-white shadow-sm">
    <thead class="table-dark">
        <tr>
            <th>Réf</th>
            <th>Désignation</th>
            <th>Stock</th>
            <th>Prix Vente</th>
            <th>Alerte</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>FH-TY-01</td>
            <td>Filtre à Huile Toyota</td>
            <td><span class="badge bg-success">50 en stock</span></td>
            <td>7.500 FCFA</td>
            <td><i class="fas fa-check-circle text-success"></i> OK</td>
            <td><button class="btn btn-sm btn-primary">Vendre</button></td>
        </tr>
    </tbody>
</table>
<?php include '../../includes/footer.php'; ?>
