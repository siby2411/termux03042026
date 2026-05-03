<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$page_title = "Saisie des Notes - OMEGA";
include 'header_ecole.php';

$classe_id = $_GET['classe_id'] ?? 0;
$matiere_id = $_GET['matiere_id'] ?? 0;
$semestre = $_GET['semestre'] ?? 1;
$annee = "2025-2026";

if (isset($_POST['save_notes'])) {
    foreach ($_POST['notes'] as $code_etudiant => $n) {
        $cc1 = ($n['cc1'] !== "") ? $n['cc1'] : null;
        $cc2 = ($n['cc2'] !== "") ? $n['cc2'] : null;
        $exam = ($n['exam'] !== "") ? $n['exam'] : null;

        $stmt = $conn->prepare("INSERT INTO notes (code_etudiant, id_matiere, semestre, annee_academique, note_cc1, note_cc2, note_exam) 
                                VALUES (?, ?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE note_cc1=?, note_cc2=?, note_exam=?");
        $stmt->bind_param("siisdddddd", $code_etudiant, $matiere_id, $semestre, $annee, $cc1, $cc2, $exam, $cc1, $cc2, $exam);
        $stmt->execute();
    }
    echo "<div class='alert alert-success'>Notes enregistrées avec succès !</div>";
}

$classes = $conn->query("SELECT * FROM classes");
$matieres = ($classe_id) ? $conn->query("SELECT m.id, m.nom_matiere FROM matieres m JOIN unites_valeur uv ON m.id = uv.matiere_id WHERE uv.classe_id = $classe_id") : null;
$etudiants = ($classe_id && $matiere_id) ? $conn->query("SELECT * FROM etudiants WHERE classe_id = $classe_id ORDER BY nom ASC") : null;
?>

<div class="container mt-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">1. Choisir Classe</label>
                    <select name="classe_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Sélectionner --</option>
                        <?php while($c = $classes->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= ($classe_id == $c['id']) ? 'selected' : '' ?>><?= $c['nom_class'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php if ($matieres): ?>
                <div class="col-md-3">
                    <label class="form-label fw-bold">2. Matière</label>
                    <select name="matiere_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Sélectionner --</option>
                        <?php while($m = $matieres->fetch_assoc()): ?>
                            <option value="<?= $m['id'] ?>" <?= ($matiere_id == $m['id']) ? 'selected' : '' ?>><?= $m['nom_matiere'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">3. Semestre</label>
                    <select name="semestre" class="form-select" onchange="this.form.submit()">
                        <option value="1" <?= ($semestre == 1) ? 'selected' : '' ?>>S1</option>
                        <option value="2" <?= ($semestre == 2) ? 'selected' : '' ?>>S2</option>
                    </select>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($etudiants && $etudiants->num_rows > 0): ?>
    <form method="POST">
        <div class="table-responsive">
            <table class="table table-bordered bg-white align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Étudiant</th>
                        <th width="15%">CC1 (40%)</th>
                        <th width="15%">CC2 (40%)</th>
                        <th width="15%">EXAMEN (60%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($e = $etudiants->fetch_assoc()): 
                        // Récupérer notes existantes
                        $res_n = $conn->query("SELECT * FROM notes WHERE code_etudiant='".$e['code_etudiant']."' AND id_matiere=$matiere_id AND semestre=$semestre");
                        $dn = $res_n->fetch_assoc();
                    ?>
                    <tr>
                        <td><strong><?= $e['nom'] ?> <?= $e['prenom'] ?></strong><br><small class="text-muted"><?= $e['code_etudiant'] ?></small></td>
                        <td><input type="number" step="0.01" name="notes[<?= $e['code_etudiant'] ?>][cc1]" class="form-control" value="<?= $dn['note_cc1'] ?? '' ?>"></td>
                        <td><input type="number" step="0.01" name="notes[<?= $e['code_etudiant'] ?>][cc2]" class="form-control" value="<?= $dn['note_cc2'] ?? '' ?>"></td>
                        <td class="bg-warning-subtle"><input type="number" step="0.01" name="notes[<?= $e['code_etudiant'] ?>][exam]" class="form-control fw-bold" value="<?= $dn['note_exam'] ?? '' ?>"></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <button type="submit" name="save_notes" class="btn btn-primary w-100 py-3 fw-bold shadow">ENREGISTRER TOUTES LES NOTES</button>
    </form>
    <?php elseif($classe_id && $matiere_id): ?>
        <div class="alert alert-warning">Aucun étudiant inscrit dans cette classe.</div>
    <?php endif; ?>
</div>
<?php include 'footer_ecole.php'; ?>
