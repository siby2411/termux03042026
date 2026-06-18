<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO professeurs (nom, prenom, specialite, email, telephone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $_POST['nom'], $_POST['prenom'], $_POST['specialite'], $_POST['email'], $_POST['telephone']);
    $stmt->execute();
    $prof_id = $conn->insert_id;
    $conn->query("UPDATE professeurs SET id_prof_code = 'PROF-$prof_id-".date("Y")."' WHERE id_prof = $prof_id");
    
    // Affectation automatique à l'UV choisie
    $conn->query("INSERT INTO affectations (prof_id, uv_id) VALUES ($prof_id, " . intval($_POST['uv_id']) . ")");
    header("Location: crud_professeurs.php"); exit();
}

$profs = $conn->query("SELECT p.*, u.nom_uv FROM professeurs p LEFT JOIN affectations a ON p.id_prof = a.prof_id LEFT JOIN uvs u ON a.uv_id = u.id ORDER BY p.id_prof DESC");
$uvs = $conn->query("SELECT * FROM uvs");
include 'header_ecole.php';
?>

<div class="container-fluid px-4">
    <h2 class="fw-bold text-primary mb-4">Gestion du Corps Enseignant</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProf">+ Nouveau Professeur</button>

    <div class="card shadow-sm">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr><th>Code</th><th>Nom</th><th>Matière Enseignée</th><th>Contact</th></tr>
            </thead>
            <tbody>
                <?php while($row = $profs->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_prof_code'] ?></td>
                    <td><?= $row['nom'] ?> <?= $row['prenom'] ?></td>
                    <td><span class="badge bg-success"><?= $row['nom_uv'] ?? 'Non affecté' ?></span></td>
                    <td><?= $row['telephone'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addProf" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-body">
                <input type="text" name="nom" class="form-control mb-2" placeholder="Nom" required>
                <input type="text" name="prenom" class="form-control mb-2" placeholder="Prénom" required>
                <select name="uv_id" class="form-control mb-2" required>
                    <option value="">Choisir la matière</option>
                    <?php while($u = $uvs->fetch_assoc()): ?>
                        <option value="<?= $u['id'] ?>"><?= $u['nom_uv'] ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="email" name="email" class="form-control mb-2" placeholder="Email">
                <input type="text" name="telephone" class="form-control mb-2" placeholder="Téléphone">
            </div>
            <div class="modal-footer"><button type="submit" name="add" class="btn btn-primary">Enregistrer</button></div>
        </form>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
