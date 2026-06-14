<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
include 'header_ecole.php';

$classe_id = isset($_GET['classe_id']) ? intval($_GET['classe_id']) : 0;
$matiere_id = isset($_GET['matiere_id']) ? intval($_GET['matiere_id']) : 0;
$semestre = isset($_GET['semestre']) ? intval($_GET['semestre']) : 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $matiere_id > 0) {
    foreach ($_POST['cc1'] as $code_etu => $val_cc1) {
        $val_cc2 = $_POST['cc2'][$code_etu] ?? 0;
        $val_exam = $_POST['exam'][$code_etu] ?? 0;
        
        $stmt = $conn->prepare("INSERT INTO notes (code_etudiant, id_matiere, semestre, note_cc1, note_cc2, note_exam, est_valide) 
                                VALUES (?, ?, ?, ?, ?, ?, 1) 
                                ON DUPLICATE KEY UPDATE note_cc1 = VALUES(note_cc1), note_cc2 = VALUES(note_cc2), note_exam = VALUES(note_exam)");
        $stmt->bind_param("siiddd", $code_etu, $matiere_id, $semestre, $val_cc1, $val_cc2, $val_exam);
        $stmt->execute();
        
        // Automatisation : Calcul des moyennes après chaque enregistrement
        $conn->query("CALL update_bulletins('$code_etu')");
    }
    echo "<div class='alert alert-success container mt-3'>Notes enregistrées et bulletin mis à jour pour la classe.</div>";
}
?>

<div class="container mt-4">
    <div class="card p-4 shadow-sm">
        <h4 class="mb-4"><i class="bi bi-pencil-square"></i> Saisie Notes (CC1:20% | CC2:20% | Examen:60%)</h4>
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <select name="classe_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Sélectionner Classe</option>
                    <?php 
                    $res = $conn->query("SELECT * FROM classes");
                    while($c = $res->fetch_assoc()) echo "<option value='{$c['id']}' ".($classe_id==$c['id']?'selected':'').">{$c['nom_class']}</option>";
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="matiere_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Sélectionner Matière</option>
                    <?php 
                    $res = $conn->query("SELECT * FROM matieres");
                    while($m = $res->fetch_assoc()) echo "<option value='{$m['id']}' ".($matiere_id==$m['id']?'selected':'').">{$m['nom_matiere']}</option>";
                    ?>
                </select>
            </div>
        </form>

        <?php if($classe_id > 0 && $matiere_id > 0): ?>
        <form method="POST">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr><th>Étudiant</th><th>CC1 (20%)</th><th>CC2 (20%)</th><th>Examen (60%)</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $etudiants = $conn->query("SELECT code_etudiant, nom, prenom FROM etudiants WHERE classe_id = $classe_id");
                    while($e = $etudiants->fetch_assoc()): 
                        $code = $e['code_etudiant'];
                        $n = $conn->query("SELECT note_cc1, note_cc2, note_exam FROM notes WHERE code_etudiant='$code' AND id_matiere=$matiere_id AND semestre=$semestre")->fetch_assoc();
                    ?>
                    <tr>
                        <td class="align-middle"><?= $e['nom'].' '.$e['prenom'] ?></td>
                        <td><input type="number" step="0.5" name="cc1[<?= $code ?>]" value="<?= $n['note_cc1'] ?? '' ?>" class="form-control"></td>
                        <td><input type="number" step="0.5" name="cc2[<?= $code ?>]" value="<?= $n['note_cc2'] ?? '' ?>" class="form-control"></td>
                        <td><input type="number" step="0.5" name="exam[<?= $code ?>]" value="<?= $n['note_exam'] ?? '' ?>" class="form-control"></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer les notes</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
