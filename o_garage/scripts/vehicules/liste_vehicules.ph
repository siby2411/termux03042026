<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-car text-primary"></i> Gestion du Parc Automobile</h2>
    <a href="formulaire_vehicule.php" class="btn btn-success"><i class="fas fa-plus"></i> Nouveau Véhicule</a>
</div>

<div class="card shadow border-0">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Immatriculation</th>
                    <th>Marque/Modèle</th>
                    <th>Propriétaire</th>
                    <th>Dernière Visite</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center text-muted">Aucun véhicule enregistré pour le moment.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
