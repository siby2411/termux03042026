<?php
require_once 'db_connect_ecole.php';
$page_title = "Gestion des Cycles - OMEGA";
include 'layout_ecole.php';
$conn = db_connect_ecole();

if (isset($_POST['save'])) {
    $nom = $_POST['nom_cycle']; $annee = $_POST['annee_etude']; $sem = $_POST['duree_semestres'];
    $stmt = $conn->prepare("INSERT INTO cycles (nom_cycle, annee_etude, duree_semestres) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $nom, $annee, $sem);
    $stmt->execute();
}

$res = $conn->query("SELECT * FROM cycles ORDER BY annee_etude");
?>

<div class="form-centered">
    <div class="row g-4">
        <div class="col-md-5">
            <div class="card omega-card p-4">
                <h5 class="fw-bold text-primary mb-4"><i class="bi bi-calendar-event"></i> Paramétrage Cycle</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nom du Cycle</label>
                        <input type="text" name="nom_cycle" class="form-control" placeholder="Licence 1" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Année (1-5)</label>
                            <input type="number" name="annee_etude" class="form-control" value="1">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Semestres</label>
                            <input type="number" name="duree_semestres" class="form-control" value="2">
                        </div>
                    </div>
                    <button type="submit" name="save" class="btn btn-omega w-100 shadow mt-3">AJOUTER LE NIVEAU</button>
                </form>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card omega-card border-0 shadow">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="p-3">Niveau</th>
                            <th class="p-3">Année</th>
                            <th class="p-3">Semestres</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td class="p-3 fw-bold"><?= $row['nom_cycle'] ?></td>
                            <td class="p-3">Année <?= $row['annee_etude'] ?></td>
                            <td class="p-3"><?= $row['duree_semestres'] ?></td>
                            <td class="p-3"><button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
