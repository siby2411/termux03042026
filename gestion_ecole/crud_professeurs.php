<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// 1. Traitement de l'ajout
if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO professeurs (nom, prenom, specialite, email, telephone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $_POST['nom'], $_POST['prenom'], $_POST['specialite'], $_POST['email'], $_POST['telephone']);
    $stmt->execute();
    
    // Génération code auto
    $id = $conn->insert_id;
    $code = "PROF-" . $id . "-" . date("Y");
    $conn->query("UPDATE professeurs SET id_prof_code = '$code' WHERE id_prof = $id");
    
    header("Location: crud_professeurs.php"); exit();
}

$profs = $conn->query("SELECT * FROM professeurs ORDER BY id_prof DESC");
include 'header_ecole.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-person-workspace me-2"></i>Corps Enseignant</h2>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProf">
            <i class="bi bi-plus"></i> Nouveau Professeur
        </button>
    </div>

    <div class="modal fade" id="addProf" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Ajouter Professeur</h5></div>
                <div class="modal-body">
                    <input type="text" name="nom" class="form-control mb-2" placeholder="Nom" required>
                    <input type="text" name="prenom" class="form-control mb-2" placeholder="Prénom" required>
                    <input type="text" name="specialite" class="form-control mb-2" placeholder="Spécialité">
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email">
                    <input type="text" name="telephone" class="form-control mb-2" placeholder="Téléphone">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add" class="btn btn-primary">Valider</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark small">
                <tr><th>Code</th><th>Nom & Prénom</th><th>Spécialité</th><th>Contact</th></tr>
            </thead>
            <tbody>
                <?php while($row = $profs->fetch_assoc()): ?>
                <tr>
                    <td class="fw-bold text-secondary"><?= $row['id_prof_code'] ?></td>
                    <td><?= strtoupper($row['nom']) ?> <?= $row['prenom'] ?></td>
                    <td><span class="badge bg-info"><?= $row['specialite'] ?></span></td>
                    <td><?= $row['email'] ?><br><small><?= $row['telephone'] ?></small></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
