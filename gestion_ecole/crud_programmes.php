<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if (isset($_POST['affecter'])) {
    $stmt = $conn->prepare("INSERT INTO programmes (classe_id, uv_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_POST['classe_id'], $_POST['uv_id']);
    $stmt->execute();
    header("Location: crud_programmes.php"); exit();
}

$classes = $conn->query("SELECT * FROM classes");
$uvs = $conn->query("SELECT * FROM unites_valeur");
$programmes = $conn->query("SELECT p.id, c.nom_class, u.nom_uv FROM programmes p JOIN classes c ON p.classe_id=c.id JOIN unites_valeur u ON p.uv_id=u.id");

include 'header_ecole.php';
?>
<div class="container mt-4">
    <h2 class="text-primary fw-bold mb-4">Affectation UV aux Classes</h2>
    <div class="card p-4 shadow-sm border-0">
        <form method="post" class="row g-3">
            <div class="col-md-5">
                <select name="classe_id" class="form-select"><?php foreach($classes as $c) echo "<option value='{$c['id']}'>{$c['nom_class']}</option>"; ?></select>
            </div>
            <div class="col-md-5">
                <select name="uv_id" class="form-select"><?php foreach($uvs as $u) echo "<option value='{$u['id']}'>{$u['nom_uv']}</option>"; ?></select>
            </div>
            <div class="col-md-2"><button name="affecter" class="btn btn-success w-100">Affecter</button></div>
        </form>
    </div>
    <table class="table mt-4">
        <thead class="table-light"><tr><th>Classe</th><th>Unité de Valeur</th></tr></thead>
        <tbody>
            <?php foreach($programmes as $p): ?>
            <tr><td><?= $p['nom_class'] ?></td><td><?= $p['nom_uv'] ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'footer_ecole.php'; ?>
