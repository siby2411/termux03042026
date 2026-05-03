<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$profs = $conn->query("SELECT * FROM professeurs ORDER BY nom ASC");
include 'header_ecole.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-person-workspace me-2"></i>Corps Enseignant</h2>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProf"><i class="bi bi-plus"></i> Nouveau Professeur</button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark small">
                    <tr>
                        <th>Code</th>
                        <th>Nom & Prénom</th>
                        <th>Diplôme / Spécialité</th>
                        <th>Contact</th>
                        <th class="text-center">CV</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $profs->fetch_assoc()): ?>
                    <tr>
                        <td class="small fw-bold text-secondary"><?= $row['code_professeur'] ?></td>
                        <td><?= strtoupper($row['nom']) ?> <?= $row['prenom'] ?></td>
                        <td><span class="badge bg-info text-dark"><?= $row['diplome'] ?></span></td>
                        <td class="small"><?= $row['email'] ?><br><?= $row['telephone'] ?></td>
                        <td class="text-center">
                            <?php if($row['cv_path']): ?>
                                <a href="<?= $row['cv_path'] ?>" class="text-danger fs-4"><i class="bi bi-file-pdf"></i></a>
                            <?php else: ?> - <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="?edit=<?= $row['id_professeur'] ?>" class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
