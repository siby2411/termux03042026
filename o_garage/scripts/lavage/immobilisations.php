<?php include '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tools text-secondary"></i> Immobilisations & Matériel</h2>
    <button class="btn btn-dark"><i class="fas fa-plus"></i> Investir</button>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm p-3 border-start border-primary border-4">
            <small class="text-muted">Investissement Total</small>
            <h4 class="fw-bold">2.450.000 FCFA</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm p-3 border-start border-warning border-4">
            <small class="text-muted">Amortissement Mensuel</small>
            <h4 class="fw-bold">45.000 FCFA</h4>
        </div>
    </div>
</div>

<table class="table bg-white shadow-sm mt-4">
    <thead class="table-light">
        <tr>
            <th>Matériel</th>
            <th>Prix Achat</th>
            <th>État</th>
            <th>Rendement</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Karcher Haute Pression K7</td>
            <td>450.000 FCFA</td>
            <td><span class="badge bg-success">Opérationnel</span></td>
            <td>85%</td>
        </tr>
    </tbody>
</table>
<?php include '../../includes/footer.php'; ?>
