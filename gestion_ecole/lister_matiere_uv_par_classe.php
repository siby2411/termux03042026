<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$page_title = "Récapitulatif Académique";
include 'layout_ecole.php';

$sql = "SELECT c.nom_class, m.nom_matiere, uv.nom_uv, uv.semestre, uv.coefficient 
        FROM unites_valeur uv
        JOIN classes c ON uv.classe_id = c.id
        JOIN matieres m ON uv.matiere_id = m.id
        ORDER BY c.nom_class, uv.semestre";
$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="card omega-card border-0 shadow">
        <div class="card-header bg-secondary text-white fw-bold">Structure des Enseignements</div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Classe</th>
                        <th>Matière</th>
                        <th>UV</th>
                        <th>Semestre</th>
                        <th>Coef</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge bg-info text-dark"><?= $row['nom_class'] ?></span></td>
                        <td><?= $row['nom_matiere'] ?></td>
                        <td><?= $row['nom_uv'] ?></td>
                        <td>S<?= $row['semestre'] ?></td>
                        <td class="fw-bold"><?= $row['coefficient'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
