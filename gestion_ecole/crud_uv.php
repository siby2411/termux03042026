<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// Traitement ajout UV
if (isset($_POST['add_uv'])) {
    $stmt = $conn->prepare("INSERT INTO unites_valeur (nom_uv, code_uv, semestre, coefficient) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $_POST['nom_uv'], $_POST['code_uv'], $_POST['semestre'], $_POST['coefficient']);
    $stmt->execute();
    header("Location: crud_uv.php"); exit();
}

// Affectation Matière à UV
if (isset($_POST['link_matiere'])) {
    $conn->query("INSERT INTO matiere_uv (uv_id, matiere_id) VALUES ({$_POST['uv_id']}, {$_POST['matiere_id']})");
    header("Location: crud_uv.php"); exit();
}

$uvs = $conn->query("SELECT * FROM unites_valeur");
$matieres = $conn->query("SELECT * FROM matieres");
include 'header_ecole.php';
?>
<div class="container mt-4">
    <h2 class="text-primary fw-bold mb-4">Gestion des Unités de Valeur (LMD)</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0">
                <h5>Nouvelle UV</h5>
                <form method="post">
                    <input type="text" name="nom_uv" class="form-control mb-2" placeholder="Nom UV" required>
                    <input type="text" name="code_uv" class="form-control mb-2" placeholder="Code UV">
                    <input type="number" name="semestre" class="form-control mb-2" placeholder="Semestre (1/2)">
                    <input type="number" step="0.1" name="coefficient" class="form-control mb-2" placeholder="Coefficient">
                    <button type="submit" name="add_uv" class="btn btn-primary w-100">Créer UV</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-3 shadow-sm border-0">
                <h5>Affecter une Matière à une UV</h5>
                <form method="post" class="row g-2">
                    <div class="col-5">
                        <select name="uv_id" class="form-select"><?php foreach($uvs as $u) echo "<option value='{$u['id']}'>{$u['nom_uv']}</option>"; ?></select>
                    </div>
                    <div class="col-5">
                        <select name="matiere_id" class="form-select"><?php foreach($matieres as $m) echo "<option value='{$m['id']}'>{$m['nom_matiere']}</option>"; ?></select>
                    </div>
                    <div class="col-2"><button name="link_matiere" class="btn btn-success w-100">+</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
