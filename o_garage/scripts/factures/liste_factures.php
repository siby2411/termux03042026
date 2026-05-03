<?php include '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice-dollar text-success"></i> Gestion de la Facturation</h2>
</div>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> Ici seront listées les factures générées à partir des diagnostics terminés.
</div>
<table class="table table-hover bg-white shadow-sm rounded">
    <thead>
        <tr class="table-success">
            <th>N° Facture</th>
            <th>Client</th>
            <th>Montant</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="5" class="text-center py-3">Aucune facture en attente.</td></tr>
    </tbody>
</table>
<?php include '../../includes/footer.php'; ?>
