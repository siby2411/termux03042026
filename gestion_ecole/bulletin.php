<?php
// bulletin.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect_ecole.php';

try {
    $conn = db_connect_ecole();

    // Paramètre : id étudiant (id dans etudiants) ou code_etudiant
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $code = isset($_GET['code_etudiant']) ? trim($_GET['code_etudiant']) : '';

    if ($id <= 0 && $code === '') {
        throw new Exception("Paramètre manquant. Fournir id (etudiants.id) ou code_etudiant.");
    }

    // Récupérer l'étudiant
    if ($id > 0) {
        $stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM etudiants WHERE code_etudiant = ?");
        $stmt->bind_param("s", $code);
    }
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$student) throw new Exception("Étudiant introuvable.");

    $code_etudiant = $student['code_etudiant'];
    // Année académique : param ou défaut
    $annee = $_GET['annee'] ?? (date('Y') . '-' . (date('Y') + 1));

    // Récupérer bulletin (ou afficher qu'il n'est pas encore créé)
    $stmt = $conn->prepare("SELECT * FROM bulletins WHERE code_etudiant = ? AND annee_academique = ?");
    $stmt->bind_param("ss", $code_etudiant, $annee);
    $stmt->execute();
    $bulletin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Récupérer toutes les notes organisées par matière (tous semestres)
    $stmt = $conn->prepare("
        SELECT n.*, m.nom_matiere, m.code_matiere, uv.nom_uv, uv.code_uv, uv.coefficient
        FROM notes n
        JOIN matieres m ON n.id_matiere = m.id
        LEFT JOIN unites_valeur uv ON uv.matiere_id = m.id AND uv.semestre = n.semestre
        WHERE n.code_etudiant = ? AND n.annee_academique = ?
        ORDER BY m.nom_matiere, n.semestre
    ");
    $stmt->bind_param("ss", $code_etudiant, $annee);
    $stmt->execute();
    $notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Organiser par matière -> semestre
    $matieres = [];
    foreach ($notes as $r) {
        $mat = $r['id_matiere'];
        $sem = $r['semestre'];
        if (!isset($matieres[$mat])) {
            $matieres[$mat] = [
                'nom' => $r['nom_matiere'],
                'code' => $r['code_matiere'],
                'uvs' => []
            ];
        }
        $matieres[$mat]['uvs'][$sem] = $r;
    }

    // Calcul des moyennes semestrielles (pondérées par coefficient)
    function calc_semestre_avg($rows, $sem) {
        // rows est la liste d'entrées notes pour le semestre sem
        $num = 0.0;
        $den = 0.0;
        foreach ($rows as $r) {
            if ((string)$r['semestre'] !== (string)$sem) continue;
            $coeff = isset($r['coefficient']) && $r['coefficient'] > 0 ? (float)$r['coefficient'] : 1.0;
            $moy = (is_null($r['note_cc1']) ? 0 : (float)$r['note_cc1']) + (is_null($r['note_cc2']) ? 0 : (float)$r['note_cc2']);
            // si les deux notes null -> ignore
            if ($r['note_cc1'] === null && $r['note_cc2'] === null) {
                $moy_sem = null;
            } else {
                $moy_sem = ($r['note_cc1'] ?? 0 + $r['note_cc2'] ?? 0) / 2.0;
            }
            if ($moy_sem === null) continue;
            $num += $moy_sem * $coeff;
            $den += $coeff;
        }
        if ($den == 0) return null;
        return $num / $den;
    }

    // Calculer moyennes semestres en agrégeant toutes les matières
    // On va parcourir notes et grouper par semestre
    $sem1_rows = array_filter($notes, fn($x) => (string)$x['semestre'] === '1' || (string)$x['semestre'] === 'S1');
    $sem2_rows = array_filter($notes, fn($x) => (string)$x['semestre'] === '2' || (string)$x['semestre'] === 'S2');

    // Utiliser coefficient de uv si présent sinon 1
    $calc_sem = function($rows) {
        $num = 0.0; $den = 0.0;
        foreach ($rows as $r) {
            $coeff = isset($r['coefficient']) && $r['coefficient'] > 0 ? (float)$r['coefficient'] : 1.0;
            if ($r['note_cc1'] === null && $r['note_cc2'] === null && $r['note_exam'] === null) continue;
            $moy_sem = null;
            if ($r['note_cc1'] !== null || $r['note_cc2'] !== null) {
                $n1 = $r['note_cc1'] ?? 0;
                $n2 = $r['note_cc2'] ?? 0;
                $moy_sem = ($n1 + $n2) / 2.0;
            }
            $exam = $r['note_exam'] !== null ? (float)$r['note_exam'] : null;
            // to compute moyenne_matiere: if exam null -> use sem only; if sem null -> use exam only
            if ($moy_sem === null && $exam === null) continue;
            if ($moy_sem === null) $m = $exam;
            elseif ($exam === null) $m = $moy_sem;
            else $m = 0.4 * $moy_sem + 0.6 * $exam;
            $num += $m * $coeff;
            $den += $coeff;
        }
        if ($den == 0) return null;
        return $num / $den;
    };

    $moy_sem1 = $calc_sem($sem1_rows);
    $moy_sem2 = $calc_sem($sem2_rows);
    $moy_annuelle = null;
    if ($moy_sem1 !== null && $moy_sem2 !== null) {
        $moy_annuelle = ($moy_sem1 + $moy_sem2) / 2.0;
    } elseif ($moy_sem1 !== null) {
        $moy_annuelle = $moy_sem1;
    } elseif ($moy_sem2 !== null) {
        $moy_annuelle = $moy_sem2;
    }

} catch (Exception $e) {
    $err = $e->getMessage();
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Bulletin - <?php echo htmlspecialchars($student['nom'] . ' ' . $student['prenom'] ?? ''); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .field-label { font-weight:600; }
    .small-muted { font-size:0.9rem; color:#666; }
  </style>
</head>
<body class="bg-light">
<div class="container my-4">
    <?php if (!empty($err)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php exit; ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h3 class="mb-0">Bulletin scolaire</h3>
                    <div class="small-muted">Année académique : <?php echo htmlspecialchars($annee); ?></div>
                </div>
                <div class="text-end">
                    <div class="field-label">Élève</div>
                    <div><?php echo htmlspecialchars($student['nom'] . ' ' . $student['prenom']); ?></div>
                    <div class="small-muted">Code : <?php echo htmlspecialchars($code_etudiant); ?></div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Matière</th>
                            <th>UV</th>
                            <th>Semestre</th>
                            <th>CC1</th>
                            <th>CC2</th>
                            <th>Exam</th>
                            <th>Moyenne Matière</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matieres as $mat_id => $mi): ?>
                            <?php
                              // two rows possible (sem1 & sem2)
                              $r1 = $mi['uvs']['1'] ?? null;
                              $r2 = $mi['uvs']['2'] ?? null;
                            ?>
                            <?php if ($r1): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mi['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($r1['nom_uv'] ?? '-'); ?></td>
                                    <td>1</td>
                                    <td><?php echo $r1['note_cc1'] ?? '-'; ?></td>
                                    <td><?php echo $r1['note_cc2'] ?? '-'; ?></td>
                                    <td><?php echo $r1['note_exam'] ?? '-'; ?></td>
                                    <td><?php echo number_format((float)$r1['moyenne_matiere'],2); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($r2): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mi['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($r2['nom_uv'] ?? '-'); ?></td>
                                    <td>2</td>
                                    <td><?php echo $r2['note_cc1'] ?? '-'; ?></td>
                                    <td><?php echo $r2['note_cc2'] ?? '-'; ?></td>
                                    <td><?php echo $r2['note_exam'] ?? '-'; ?></td>
                                    <td><?php echo number_format((float)$r2['moyenne_matiere'],2); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="6" class="text-end">Moyenne Semestre 1</th>
                            <th><?php echo $moy_sem1 !== null ? number_format($moy_sem1,2) : '-'; ?></th>
                        </tr>
                        <tr class="table-light">
                            <th colspan="6" class="text-end">Moyenne Semestre 2</th>
                            <th><?php echo $moy_sem2 !== null ? number_format($moy_sem2,2) : '-'; ?></th>
                        </tr>
                        <tr class="table-secondary">
                            <th colspan="6" class="text-end">Moyenne Annuelle</th>
                            <th><?php echo $moy_annuelle !== null ? number_format($moy_annuelle,2) : '-'; ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between">
                <div>
                    <small class="text-muted">Généré le <?php echo date('Y-m-d H:i'); ?></small>
                </div>
                <div>
                    <a href="crud_etudiants.php" class="btn btn-sm btn-outline-secondary">Retour</a>
                    <a href="#" onclick="window.print()" class="btn btn-sm btn-primary">Imprimer</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

