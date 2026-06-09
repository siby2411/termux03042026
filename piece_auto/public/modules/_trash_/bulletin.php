<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$annee = $_GET['annee'] ?? '2025-2026';

// 1. Récupération Étudiant + Classe
$stmt = $conn->prepare("SELECT e.*, c.nom_class FROM etudiants e JOIN classes c ON e.classe_id = c.id WHERE e.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) die("Étudiant introuvable.");

// 2. Récupération des notes avec coefficients des UV
$sql_notes = "SELECT n.*, uv.nom_uv, uv.coefficient, m.nom_matiere 
              FROM notes n
              JOIN unites_valeur uv ON n.id_matiere = uv.matiere_id AND n.semestre = uv.semestre
              JOIN matieres m ON n.id_matiere = m.id
              WHERE n.code_etudiant = ? AND n.annee_academique = ?
              ORDER BY n.semestre, m.nom_matiere";
$stmt_n = $conn->prepare($sql_notes);
$stmt_n->bind_param("ss", $student['code_etudiant'], $annee);
$stmt_n->execute();
$notes = $stmt_n->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Calculs des Moyennes
$semestres = [1 => ['sum' => 0, 'coef' => 0], 2 => ['sum' => 0, 'coef' => 0]];
foreach ($notes as $n) {
    $sem = $n['semestre'];
    $moy_mat = $n['moyenne_matiere']; // Colonne générée en SQL
    $semestres[$sem]['sum'] += ($moy_mat * $n['coefficient']);
    $semestres[$sem]['coef'] += $n['coefficient'];
}

$m1 = ($semestres[1]['coef'] > 0) ? $semestres[1]['sum'] / $semestres[1]['coef'] : 0;
$m2 = ($semestres[2]['coef'] > 0) ? $semestres[2]['sum'] / $semestres[2]['coef'] : 0;
$general = ($m1 > 0 && $m2 > 0) ? ($m1 + $m2) / 2 : ($m1 + $m2);

include 'layout_ecole.php'; 
?>

<div class="container bg-white p-5 shadow-lg rounded">
    <div class="row border-bottom pb-3 mb-4">
        <div class="col-md-6">
            <h2 class="text-primary fw-bold">BULLETIN DE NOTES</h2>
            <p class="mb-0">Année Académique : <strong><?= $annee ?></strong></p>
            <p>Classe : <strong><?= $student['nom_class'] ?></strong></p>
        </div>
        <div class="col-md-6 text-end">
            <h4><?= strtoupper($student['nom']) ?> <?= $student['prenom'] ?></h4>
            <p class="text-muted">Matricule : <?= $student['code_etudiant'] ?></p>
        </div>
    </div>

    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>Matière (UV)</th>
                <th>Semestre</th>
                <th>CC1</th>
                <th>CC2</th>
                <th>Examen</th>
                <th>Coef</th>
                <th>Moyenne</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($notes as $n): ?>
            <tr>
                <td class="text-start"><?= $n['nom_matiere'] ?> (<?= $n['nom_uv'] ?>)</td>
                <td>S<?= $n['semestre'] ?></td>
                <td><?= $n['note_cc1'] ?></td>
                <td><?= $n['note_cc2'] ?></td>
                <td class="fw-bold"><?= $n['note_exam'] ?></td>
                <td><?= $n['coefficient'] ?></td>
                <td class="bg-light fw-bold"><?= number_format($n['moyenne_matiere'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light">
                <h6>Moyenne Semestre 1</h6>
                <h4 class="text-primary"><?= number_format($m1, 2) ?> / 20</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light">
                <h6>Moyenne Semestre 2</h6>
                <h4 class="text-primary"><?= number_format($m2, 2) ?> / 20</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-primary text-white text-center">
                <h6>MOYENNE GÉNÉRALE</h6>
                <h3 class="fw-bold"><?= number_format($general, 2) ?> / 20</h3>
            </div>
        </div>
    </div>

    <div class="mt-5 d-print-none">
        <button onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer"></i> Imprimer le Bulletin</button>
        <a href="crud_etudiants.php" class="btn btn-outline-secondary">Retour Liste</a>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
