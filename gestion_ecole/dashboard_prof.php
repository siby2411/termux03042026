<?php
// Fichier : dashboard_prof.php (Inclus dans index.php)

// Vérifiez que la connexion est disponible (conn est supposé être défini dans index.php)
if (!isset($conn)) {
    // Si $conn n'est pas défini, tenter de le définir ou afficher une erreur.
    // Pour cet environnement d'inclusion, on suppose qu'il est défini.
    // require_once 'db_connect_ecole.php'; 
    // $conn = db_connect_ecole();
}

// Exemple : afficher le tableau des professeurs
$sql = "SELECT id, nom, prenom, email FROM professeurs ORDER BY nom";
$result = $conn->query($sql);
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i> Aperçu des Professeurs</h5>
            </div>
            <div class="card-body">

                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 10%;">ID</th>
                                    <th style="width: 30%;">Nom</th>
                                    <th style="width: 30%;">Prénom</th>
                                    <th style="width: 30%;">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['nom']) ?></td>
                                        <td><?= htmlspecialchars($row['prenom']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        <i class="bi bi-info-circle me-2"></i> Aucun professeur trouvé.
                    </div>
                <?php endif; ?>

            </div>
            <div class="card-footer text-end">
                <a href="crud_professeurs.php" class="btn btn-sm btn-outline-success">
                    Gérer les Professeurs <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
// Assurez-vous de libérer la mémoire du résultat de la requête
if ($result) {
    $result->free();
}
?>
