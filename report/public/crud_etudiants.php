<?php
require_once "db_connect_ecole.php";
$conn = db_connect_ecole();
$page_title = "Gestion des Étudiants - OMEGA";
include "layout_ecole.php";

// Logique d'ajout (simplifiée pour l'exemple)
if (isset($_POST['add'])) {
    $nom = $_POST['nom']; $prenom = $_POST['prenom']; $classe_id = $_POST['classe_id'];
    $sql = "INSERT INTO etudiants (nom, prenom, classe_id, code_etudiant) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $code = "ETUD-" . rand(100, 999);
    $stmt->bind_param("ssis", $nom, $prenom, $classe_id, $code);
    $stmt->execute();
    echo "<div class='alert alert-success form-centered mb-4'>Étudiant inscrit avec succès !</div>";
}

$res = $conn->query("SELECT e.*, c.nom_class FROM etudiants e LEFT JOIN classes c ON e.classe_id = c.id ORDER BY e.id DESC");
$etudiants = $res->fetch_all(MYSQLI_ASSOC);
$classes = $conn->query("SELECT id, nom_class FROM classes")->fetch_all(MYSQLI_ASSOC);
?>

<div class="form-centered">
    <div class="card omega-card mb-5">
        <div class="card-header bg-white py-4 border-0">
            <h4 class="fw-bold mb-0 text-center"><i class="bi bi-person-plus-fill text-primary"></i> Inscription d'un Nouvel Étudiant</h4>
        </div>
        <div class="card-body px-5 pb-5">
            <form method="post" class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nom de famille</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Prénom</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Classe / Niveau</label>
                    <select name="classe_id" class="form-select" required>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['nom_class'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" name="add" class="btn btn-omega shadow">
                        <i class="bi bi-check-circle"></i> VALIDER L'INSCRIPTION
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card omega-card">
        <div class="card-body p-0">
            <table class="table table-hover table-elite mb-0">
                <thead>
                    <tr>
                        <th class="p-3">Code ID</th>
                        <th class="p-3">Nom & Prénom</th>
                        <th class="p-3">Classe</th>
                        <th class="p-3 text-center">Actions Académiques</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($etudiants as $e): ?>
                    <tr>
                        <td class="p-3 fw-bold text-primary"><?= $e['code_etudiant'] ?></td>
                        <td class="p-3 text-uppercase"><?= $e['nom'] ?> <?= $e['prenom'] ?></td>
                        <td class="p-3"><span class="badge bg-light text-dark"><?= $e['nom_class'] ?></span></td>
                        <td class="p-3 text-center">
                            <a href="bulletin.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill">Bulletin</a>
                            <a href="saisir_notes.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill ms-2">Notes</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
