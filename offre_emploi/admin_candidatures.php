<?php
require_once 'includes/db.php';
include 'includes/header.php';
?>

<div class="container my-5 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Interface Recruteur : Candidatures</h2>
        <a href="admin_offres.php" class="btn btn-outline-primary">Gérer les Offres</a>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover shadow-sm bg-white">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>CV</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM candidatures ORDER BY created_at DESC");
                while ($c = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($c['id']) ?></td>
                    <td><?= htmlspecialchars($c['nom']) ?></td>
                    <td><?= htmlspecialchars($c['prenom']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['telephone']) ?></td>
                    <td>
                        <a href="<?= htmlspecialchars($c['cv_path']) ?>" class="btn btn-sm btn-info" target="_blank">Ouvrir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
