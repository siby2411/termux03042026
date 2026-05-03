<?php require_once '../../includes/header.php'; ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between">
        <h5 class="mb-0">Registre des Immobilisations (Matériel)</h5>
        <button class="btn btn-warning btn-sm">Ajouter un Actif</button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Date Acquisition</th>
                    <th>Valeur Neuf</th>
                    <th>État</th>
                    <th>Amortissement</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pont Élévateur 4T</td>
                    <td>12/01/2025</td>
                    <td>2 500 000 F</td>
                    <td><span class="badge bg-success">Opérationnel</span></td>
                    <td>20% / an</td>
                </tr>
                <tr>
                    <td>Scanner Autel Maxisys</td>
                    <td>05/02/2026</td>
                    <td>1 200 000 F</td>
                    <td><span class="badge bg-success">Bon</span></td>
                    <td>33% / an</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
