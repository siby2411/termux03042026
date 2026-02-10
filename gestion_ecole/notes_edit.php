<?php
// Fichier : notes_edit.php - Saisie et Calcul des Notes (Version Colorée)

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php'; 

// Vérification de la session Admin et du paramètre code_etudiant
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin' || !isset($_GET['code_etudiant'])) {
    $_SESSION['message'] = "Accès non autorisé ou étudiant non spécifié.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php"); 
    exit();
}

$conn = db_connect_ecole();
$code_etudiant = htmlspecialchars($_GET['code_etudiant']);

$message = '';
$msg_type = '';

// --- 1. Gestion de la soumission du formulaire ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_notes'])) {
    $notes_data = $_POST['notes'];
    $annee_academique = $_POST['annee_academique']; 
    $conn->begin_transaction();

    try {
        foreach ($notes_data as $id_matiere => $semestre_data) {
            foreach ($semestre_data as $semestre => $notes) {
                $cc1 = floatval($notes['cc1']);
                $cc2 = floatval($notes['cc2']);
                $examen = floatval($notes['examen']);

                $sql_insert_or_update = "
                    INSERT INTO notes (code_etudiant, id_matiere, annee_academique, semestre, note_cc1, note_cc2, note_examen)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        note_cc1 = VALUES(note_cc1), 
                        note_cc2 = VALUES(note_cc2), 
                        note_examen = VALUES(note_examen);
                ";
                
                $stmt = $conn->prepare($sql_insert_or_update);
                $stmt->bind_param("sissddd", $code_etudiant, $id_matiere, $annee_academique, $semestre, $cc1, $cc2, $examen);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erreur pour matière ID {$id_matiere} ({$semestre}): " . $stmt->error);
                }
                $stmt->close();
            }
        }
        $conn->commit();
        $message = "✅ Notes de l'étudiant {$code_etudiant} enregistrées avec succès !";
        $msg_type = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "❌ Erreur lors de l'enregistrement : " . $e->getMessage();
        $msg_type = "danger";
    }
}

// --- 2. Récupération info étudiant ---
$etudiant_query = $conn->prepare("
    SELECT e.nom, e.prenom, e.code_etudiant, c.annee_academique, c.id_filiere
    FROM etudiants e
    JOIN classes c ON e.id_classe = c.id_classe
    WHERE e.code_etudiant = ?
");
$etudiant_query->bind_param("s", $code_etudiant);
$etudiant_query->execute();
$etudiant_result = $etudiant_query->get_result();

if ($etudiant_result->num_rows === 0) {
    $_SESSION['message'] = "Étudiant non trouvé.";
    $_SESSION['msg_type'] = "danger";
    header("Location: crud_etudiants.php"); 
    exit();
}

$etudiant = $etudiant_result->fetch_assoc();
$id_filiere = $etudiant['id_filiere'];
$annee_academique = $etudiant['annee_academique'];

// --- 3. Récupération matières et notes existantes ---
$matieres_query = $conn->prepare("
    SELECT m.id_matiere, m.nom_matiere, m.coefficient, m.semestre,
           n.note_cc1, n.note_cc2, n.note_examen
    FROM matieres m
    LEFT JOIN notes n ON m.id_matiere = n.id_matiere 
        AND n.code_etudiant = ? 
        AND n.annee_academique = ?
        AND n.semestre = m.semestre
    WHERE m.id_filiere = ?
    ORDER BY m.semestre, m.nom_matiere
");
$matieres_query->bind_param("ssi", $code_etudiant, $annee_academique, $id_filiere);
$matieres_query->execute();
$matieres_result = $matieres_query->get_result();

$matieres_s1 = []; $matieres_s2 = []; $matieres_annuel = [];
$total_coef_s1 = $total_coef_s2 = $total_coef_annuel = 0;

while ($row = $matieres_result->fetch_assoc()) {
    $coef = $row['coefficient'];
    $cc1 = floatval($row['note_cc1'] ?? 0.0);
    $cc2 = floatval($row['note_cc2'] ?? 0.0);
    $examen = floatval($row['note_examen'] ?? 0.0);

    $moyenne = ($cc1 || $cc2 || $examen) ? (0.3*$cc1 + 0.3*$cc2 + 0.4*$examen) : 0;
    $row['moyenne'] = number_format($moyenne, 2);

    switch ($row['semestre']) {
        case 'S1': $matieres_s1[] = $row; $total_coef_s1 += $coef; break;
        case 'S2': $matieres_s2[] = $row; $total_coef_s2 += $coef; break;
        case 'Annuel': $matieres_annuel[] = $row; $total_coef_annuel += $coef; break;
    }
}

$conn->close();
include 'header_ecole.php'; 
?>

<div class="container my-4">
    <h1 class="mb-4 text-primary">📝 Saisie et Calcul des Notes</h1>

    <?php if($message): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header text-white" style="background-color: #0d6efd;">
            <h5 class="mb-1"><?= htmlspecialchars($etudiant['prenom'].' '.$etudiant['nom']); ?></h5>
            <small>Code Étudiant : <?= htmlspecialchars($code_etudiant); ?> | Année : <?= htmlspecialchars($annee_academique); ?></small>
        </div>
        <div class="card-body">
            <form action="notes_edit.php?code_etudiant=<?= $code_etudiant ?>" method="POST">
                <input type="hidden" name="save_notes" value="1">
                <input type="hidden" name="annee_academique" value="<?= htmlspecialchars($annee_academique); ?>">

                <?php
                function render_semestre($name, $matieres, $total_coef) {
                    if(empty($matieres)) {
                        echo "<p class='alert alert-info mt-3'>Aucune matière pour {$name}.</p>";
                        return;
                    }
                ?>
                    <h3 class="mt-4 text-secondary"><?= $name ?> (Total Coef: <?= number_format($total_coef,2) ?>)</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Matière</th>
                                    <th>Coef.</th>
                                    <th>CC1</th>
                                    <th>CC2</th>
                                    <th>Examen</th>
                                    <th>Moyenne</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($matieres as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['nom_matiere']) ?></td>
                                    <td><?= number_format($m['coefficient'],2) ?></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm"
                                               name="notes[<?= $m['id_matiere'] ?>][<?= $m['semestre'] ?>][cc1]" 
                                               value="<?= number_format($m['note_cc1'] ?? 0,2,'.','') ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm"
                                               name="notes[<?= $m['id_matiere'] ?>][<?= $m['semestre'] ?>][cc2]" 
                                               value="<?= number_format($m['note_cc2'] ?? 0,2,'.','') ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm"
                                               name="notes[<?= $m['id_matiere'] ?>][<?= $m['semestre'] ?>][examen]" 
                                               value="<?= number_format($m['note_examen'] ?? 0,2,'.','') ?>">
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" 
                                              style="background-color: <?= ($m['moyenne']>=10) ? '#198754' : '#dc3545' ?>; color:white;">
                                            <?= $m['moyenne'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                }

                render_semestre('Semestre S1', $matieres_s1, $total_coef_s1);
                render_semestre('Semestre S2', $matieres_s2, $total_coef_s2);
                render_semestre('Semestre Annuel', $matieres_annuel, $total_coef_annuel);
                ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg me-2" style="background-color:#198754; border:none;">💾 Enregistrer</button>
                    <a href="crud_etudiants.php" class="btn btn-secondary btn-lg" style="background-color:#6c757d; color:white;">🔙 Retour</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>

