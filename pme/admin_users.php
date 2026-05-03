<?php
include 'includes/db.php';
include 'includes/header.php';

$users = $pdo->query("SELECT u.*, s.nom_service FROM users u LEFT JOIN services s ON u.service_id = s.id")->fetchAll();
?>

<h1 class="h2 border-bottom pb-2">Administration des Utilisateurs</h1>

<table class="table table-striped mt-4">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Service</th>
            <th>Rôle</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['nom_service']) ?></td>
            <td><span class="badge bg-dark"><?= $u['role'] ?></span></td>
            <td>
                <a href="#" class="btn btn-sm btn-danger">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>
