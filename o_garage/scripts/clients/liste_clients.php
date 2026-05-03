<?php require_once '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Répertoire Clients</h2>
    <a href="formulaire_client.php" class="btn btn-warning"><i class="fas fa-plus"></i> Nouveau</a>
</div>
<div class="table-responsive bg-white p-3 rounded shadow-sm">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Véhicules</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $res = $db->query("SELECT * FROM clients");
            while($c = $res->fetch()):
                $count = $db->prepare("SELECT COUNT(*) FROM vehicules WHERE id_client = ?");
                $count->execute([$c['id_client']]);
                $nb = $count->fetchColumn();
            ?>
            <tr>
                <td><?= $c['id_client'] ?></td>
                <td><strong><?= $c['nom'] ?></strong></td>
                <td><?= $c['telephone'] ?></td>
                <td><span class="badge bg-info text-dark"><?= $nb ?> véhicule(s)</span></td>
                <td>
                    <a href="profil_client.php?id=<?= $c['id_client'] ?>" class="btn btn-sm btn-outline-primary">Détails</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require_once '../../includes/footer.php'; ?>
