<?php
require_once 'db_connect_ecole.php';
$page_title = "Gestion des Filières - OMEGA";
include 'layout_ecole.php';

$conn = db_connect_ecole();

// --- LOGIQUE SAUVEGARDE ---
if (isset($_POST['save_filiere'])) {
    $nom = $_POST['nom_filiere'];
    $cycle = $_POST['cycle_id'];
    $id = intval($_POST['id_filiere']);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE filieres SET nom_filiere=?, cycle_id=? WHERE id_filiere=?");
        $stmt->bind_param("sii", $nom, $cycle, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO filieres (nom_filiere, cycle_id) VALUES (?, ?)");
        $stmt->bind_param("si", $nom, $cycle);
    }
    $stmt->execute();
    echo "<div class='alert alert-success form-centered mb-4 shadow-sm'>Opération réussie.</div>";
}

// --- LOGIQUE SUPPRESSION ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM filieres WHERE id_filiere = $id");
}

$cycles = $conn->query("SELECT * FROM cycles ORDER BY nom_cycle");
$filieres = $conn->query("SELECT f.*, c.nom_cycle FROM filieres f JOIN cycles c ON f.cycle_id = c.id_cycle");
?>

<div class="form-centered">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card omega-card p-4">
                <h5 class="fw-bold text-primary mb-4"><i class="bi bi-diagram-3"></i> Nouvelle Filière</h5>
                <form method="POST">
                    <input type="hidden" name="id_filiere" value="0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Désignation Filière</label>
                        <input type="text" name="nom_filiere" class="form-control" placeholder="Ex: Informatique de Gestion" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Cycle Rattaché</label>
                        <select name="cycle_id" class="form-select" required>
                            <?php while($c = $cycles->fetch_assoc()): ?>
                                <option value="<?= $c['id_cycle'] ?>"><?= $c['nom_cycle'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="save_filiere" class="btn btn-omega w-100 shadow">ENREGISTRER</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card omega-card">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="p-3">Désignation</th>
                            <th class="p-3">Cycle</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($f = $filieres->fetch_assoc()): ?>
                        <tr>
                            <td class="p-3 fw-bold"><?= $f['nom_filiere'] ?></td>
                            <td class="p-3"><span class="badge bg-light text-dark border"><?= $f['nom_cycle'] ?></span></td>
                            <td class="p-3 text-center">
                                <a href="?delete=<?= $f['id_filiere'] ?>" class="text-danger" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
