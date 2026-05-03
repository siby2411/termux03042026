<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
include 'header_ecole.php';

$id = $_GET['id'] ?? 0;
if (isset($_POST['update_etu'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $classe_id = $_POST['classe_id'];
    $stmt = $conn->prepare("UPDATE etudiants SET nom=?, prenom=?, classe_id=? WHERE id=?");
    $stmt->bind_param("ssii", $nom, $prenom, $classe_id, $id);
    $stmt->execute();
    header("Location: crud_etudiants.php");
}

$etudiant = $conn->query("SELECT * FROM etudiants WHERE id=$id")->fetch_assoc();
$classes = $conn->query("SELECT * FROM classes");
?>
<div class="container mt-5">
    <div class="card shadow-sm col-md-6 mx-auto">
        <div class="card-header bg-dark text-white">Modifier l'Étudiant : <?= $etudiant['code_etudiant'] ?></div>
        <form method="POST" class="card-body">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control mb-3" value="<?= $etudiant['nom'] ?>">
            <label>Prénom</label>
            <input type="text" name="prenom" class="form-control mb-3" value="<?= $etudiant['prenom'] ?>">
            <label>Classe</label>
            <select name="classe_id" class="form-select mb-3">
                <?php while($c = $classes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= ($c['id'] == $etudiant['classe_id']) ? 'selected' : '' ?>><?= $c['nom_class'] ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="update_etu" class="btn btn-success w-100">Mettre à jour</button>
        </form>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
