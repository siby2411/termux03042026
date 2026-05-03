<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$matieres = $conn->query("SELECT * FROM matieres ORDER BY semestre, nom_matiere");
include 'header_ecole.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">Ajouter une Matière</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-2"><label class="small">Nom de la Matière</label><input type="text" name="nom_matiere" class="form-control" required></div>
                        <div class="mb-2"><label class="small">Coefficient</label><input type="number" step="0.5" name="coefficient" class="form-control" value="1"></div>
                        <div class="mb-2">
                            <label class="small">Semestre</label>
                            <select name="semestre" class="form-select">
                                <option value="S1">Semestre 1</option>
                                <option value="S2">Semestre 2</option>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100 mt-3 fw-bold">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Matière</th>
                            <th>Coef</th>
                            <th>Semestre</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($m = $matieres->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?= $m['nom_matiere'] ?></td>
                            <td><?= $m['coefficient'] ?></td>
                            <td><span class="badge <?= $m['semestre']=='S1'?'bg-warning text-dark':'bg-info' ?>"><?= $m['semestre'] ?></span></td>
                            <td class="text-end"><a href="#" class="text-danger"><i class="bi bi-trash"></i></a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
