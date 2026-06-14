<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$code_etu = $_GET['code_etudiant'] ?? '';

// Sauvegarde des notes
if (isset($_POST['save_notes'])) {
    foreach ($_POST['notes'] as $id_note => $val) {
        $stmt = $conn->prepare("UPDATE notes SET note_cc1=?, note_cc2=?, note_exam=? WHERE id=?");
        $stmt->bind_param("dddi", $val['cc1'], $val['cc2'], $val['exam'], $id_note);
        $stmt->execute();
    }
}

// Validation par l'Admin (Trigger déclencheur)
if (isset($_POST['valider_notes'])) {
    $conn->query("UPDATE notes SET est_valide = 1 WHERE code_etudiant = '$code_etu'");
}

$notes = $conn->query("SELECT n.*, m.nom_matiere FROM notes n JOIN matieres m ON n.id_matiere = m.id WHERE n.code_etudiant = '$code_etu'");
include 'header_ecole.php';
?>
<div class="container mt-4">
    <h2 class="text-primary">Validation des Notes : <?= $code_etu ?></h2>
    <form method="post">
        <table class="table">
            <thead><tr><th>Matière</th><th>CC1</th><th>CC2</th><th>Exam</th><th>Validé ?</th></tr></thead>
            <tbody>
                <?php while($n = $notes->fetch_assoc()): ?>
                <tr>
                    <td><?= $n['nom_matiere'] ?></td>
                    <td><input type="number" name="notes[<?= $n['id'] ?>][cc1]" value="<?= $n['note_cc1'] ?>" class="form-control"></td>
                    <td><input type="number" name="notes[<?= $n['id'] ?>][cc2]" value="<?= $n['note_cc2'] ?>" class="form-control"></td>
                    <td><input type="number" name="notes[<?= $n['id'] ?>][exam]" value="<?= $n['note_exam'] ?>" class="form-control"></td>
                    <td><?= $n['est_valide'] ? '✅' : '❌' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <button type="submit" name="save_notes" class="btn btn-secondary">Enregistrer Brouillon</button>
        <button type="submit" name="valider_notes" class="btn btn-success">Valider et Calculer Bulletin</button>
    </form>
</div>
<?php include 'footer_ecole.php'; ?>
