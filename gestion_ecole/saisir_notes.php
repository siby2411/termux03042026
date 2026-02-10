<?php
// saisir_notes.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect_ecole.php';

try {
    $conn = db_connect_ecole();

    // Année académique par défaut
    $annee = $_POST['annee_academique'] ?? $_GET['annee'] ?? (date('Y') . '-' . (date('Y') + 1));
    $semestre = $_POST['semestre'] ?? $_GET['semestre'] ?? '1';
    $code_etudiant = $_POST['code_etudiant'] ?? $_GET['code_etudiant'] ?? '';
    $id_etudiant = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // POST : sauvegarde
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notes'])) {
        $code_etudiant = $_POST['code_etudiant'];
        $annee = $_POST['annee_academique'];
        $semestre = $_POST['semestre'];

        // notes arrays: note_cc1[id_matiere] etc.
        $note_cc1 = $_POST['note_cc1'] ?? [];
        $note_cc2 = $_POST['note_cc2'] ?? [];
        $note_exam = $_POST['note_exam'] ?? [];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("
                INSERT INTO notes (code_etudiant, id_matiere, annee_academique, semestre, note_cc1, note_cc2, note_exam, moyenne_matiere)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE note_cc1 = VALUES(note_cc1), note_cc2 = VALUES(note_cc2),
                note_exam = VALUES(note_exam), moyenne_matiere = VALUES(moyenne_matiere)
            ");

            foreach ($note_cc1 as $matiere_id => $v) {
                $n1 = $v === '' ? null : (float)$v;
                $n2 = isset($note_cc2[$matiere_id]) && $note_cc2[$matiere_id] !== '' ? (float)$note_cc2[$matiere_id] : null;
                $exam = isset($note_exam[$matiere_id]) && $note_exam[$matiere_id] !== '' ? (float)$note_exam[$matiere_id] : null;

                // Calcul moyenne semestrielle et moyenne_matiere
                $moy_sem = null;
                if ($n1 !== null || $n2 !== null) {
                    $moy_sem = (($n1 ?? 0) + ($n2 ?? 0)) / 2.0;
                }
                if ($moy_sem === null && $exam === null) {
                    $moy_mat = null;
                } elseif ($moy_sem === null) {
                    $moy_mat = $exam;
                } elseif ($exam === null) {
                    $moy_mat = $moy_sem;
                } else {
                    $moy_mat = 0.4 * $moy_sem + 0.6 * $exam;
                }

                // bind params and execute
                $stmt->bind_param("sissdddi", $code_etudiant, $matiere_id, $annee, $semestre, $n1, $n2, $exam, $moy_mat);
                $stmt->execute();
            }
            $stmt->close();

            // Recalcul bulletin
            recalc_bulletin($conn, $code_etudiant, $annee);

            $conn->commit();
            $success = "Notes enregistrées et bulletin recalculé.";
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    // Si on a id_etudiant ou code_etudiant, récupérer l'étudiant et sa classe
    if ($id_etudiant > 0) {
        $s = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
        $s->bind_param("i", $id_etudiant);
        $s->execute();
        $student = $s->get_result()->fetch_assoc();
        $s->close();
        $code_etudiant = $student['code_etudiant'];
    } elseif ($code_etudiant) {
        $s = $conn->prepare("SELECT * FROM etudiants WHERE code_etudiant = ?");
        $s->bind_param("s", $code_etudiant);
        $s->execute();
        $student = $s->get_result()->fetch_assoc();
        $s->close();
    } else {
        $student = null;
    }

    // Liste étudiants pour select
    $students_list = $conn->query("SELECT id, nom, prenom, code_etudiant FROM etudiants ORDER BY nom, prenom");

    // Si on a étudiant, récupérer matières de sa classe
    $matières = [];
    if ($student) {
        $classe_id = $student['classe_id'];
        // Récupérer matières liées à la filière ou à la classe
        $stmt = $conn->prepare("
            SELECT m.id, m.nom_matiere, m.code_matiere, uv.id AS id_uv, uv.nom_uv, uv.coefficient, uv.semestre
            FROM matieres m
            LEFT JOIN unites_valeur uv ON uv.matiere_id = m.id
            WHERE m.filiere_id = (SELECT filiere_id FROM classes WHERE id = ?)
            ORDER BY m.nom_matiere, uv.semestre
        ");
        $stmt->bind_param("i", $classe_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $matières[$r['id']] = $r; // last uv may overwrite but we primarily need matiere list
        }
        $stmt->close();

        // Récupérer notes existantes pour cet étudiant/année/semestre
        $stmt = $conn->prepare("SELECT * FROM notes WHERE code_etudiant = ? AND annee_academique = ? AND semestre = ?");
        $stmt->bind_param("sss", $student['code_etudiant'], $annee, $semestre);
        $stmt->execute();
        $existing_notes = [];
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $existing_notes[$r['id_matiere']] = $r;
        }
        $stmt->close();
    }

    // Functions
    function recalc_bulletin($conn, $code_etudiant, $annee) {
        // calc moyenne sem1 et sem2 weighted by UV coefficient
        // semetre '1' then '2'
        $res = ['1' => null, '2' => null];
        foreach (['1','2'] as $sem) {
            $sql = "
                SELECT n.moyenne_matiere, IFNULL(uv.coefficient,1) AS coef
                FROM notes n
                LEFT JOIN matieres m ON m.id = n.id_matiere
                LEFT JOIN unites_valeur uv ON uv.matiere_id = m.id AND uv.semestre = n.semestre
                WHERE n.code_etudiant = ? AND n.annee_academique = ? AND n.semestre = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $code_etudiant, $annee, $sem);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $num = 0; $den = 0;
            foreach ($rows as $r) {
                if ($r['moyenne_matiere'] === null) continue;
                $num += $r['moyenne_matiere'] * $r['coef'];
                $den += $r['coef'];
            }
            if ($den == 0) $res[$sem] = null; else $res[$sem] = $num / $den;
        }

        $m1 = $res['1']; $m2 = $res['2'];
        $m_ann = null;
        if ($m1 !== null && $m2 !== null) $m_ann = ($m1 + $m2) / 2.0;
        elseif ($m1 !== null) $m_ann = $m1;
        elseif ($m2 !== null) $m_ann = $m2;

        // Upsert bulletin
        $stmt = $conn->prepare("SELECT id_bulletin FROM bulletins WHERE code_etudiant = ? AND annee_academique = ?");
        $stmt->bind_param("ss", $code_etudiant, $annee);
        $stmt->execute();
        $resq = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($resq) {
            $stmt = $conn->prepare("UPDATE bulletins SET moyenne_semestre1 = ?, moyenne_semestre2 = ?, moyenne_annuelle = ? WHERE code_etudiant = ? AND annee_academique = ?");
            $stmt->bind_param("ddsss", $m1, $m2, $m_ann, $code_etudiant, $annee);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO bulletins (code_etudiant, annee_academique, moyenne_semestre1, moyenne_semestre2, moyenne_annuelle) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddd", $code_etudiant, $annee, $m1, $m2, $m_ann);
            $stmt->execute();
            $stmt->close();
        }
    }

} catch (Exception $e) {
    $err = $e->getMessage();
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Saisir Notes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-4">
    <?php if (!empty($err)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Étudiant</label>
                    <select name="id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        <?php while ($s = $students_list->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo (isset($student) && $student['id']==$s['id'])?'selected':''; ?>>
                                <?php echo htmlspecialchars($s['nom'].' '.$s['prenom'].' ('.$s['code_etudiant'].')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Année</label>
                    <input type="text" name="annee" class="form-control" value="<?php echo htmlspecialchars($annee); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Semestre</label>
                    <select name="semestre" class="form-select">
                        <option value="1" <?php echo $semestre=='1'?'selected':''; ?>>1</option>
                        <option value="2" <?php echo $semestre=='2'?'selected':''; ?>>2</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary">Charger</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($student): ?>
        <div class="card">
            <div class="card-body">
                <h5><?php echo htmlspecialchars($student['nom'].' '.$student['prenom'].' — '.$student['code_etudiant']); ?></h5>
                <form method="post">
                    <input type="hidden" name="code_etudiant" value="<?php echo htmlspecialchars($student['code_etudiant']); ?>">
                    <input type="hidden" name="annee_academique" value="<?php echo htmlspecialchars($annee); ?>">
                    <input type="hidden" name="semestre" value="<?php echo htmlspecialchars($semestre); ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Matière</th>
                                    <th>UV</th>
                                    <th>Coef</th>
                                    <th>CC1</th>
                                    <th>CC2</th>
                                    <th>Exam</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($matières as $m): 
                                    $mid = $m['id'];
                                    $ex = $existing_notes[$mid] ?? null;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($m['nom_matiere']); ?></td>
                                    <td><?php echo htmlspecialchars($m['nom_uv'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($m['coefficient'] ?? '1'); ?></td>
                                    <td><input step="0.01" min="0" max="20" name="note_cc1[<?php echo $mid; ?>]" class="form-control form-control-sm" value="<?php echo $ex['note_cc1'] ?? ''; ?>"></td>
                                    <td><input step="0.01" min="0" max="20" name="note_cc2[<?php echo $mid; ?>]" class="form-control form-control-sm" value="<?php echo $ex['note_cc2'] ?? ''; ?>"></td>
                                    <td><input step="0.01" min="0" max="20" name="note_exam[<?php echo $mid; ?>]" class="form-control form-control-sm" value="<?php echo $ex['note_exam'] ?? ''; ?>"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="save_notes" class="btn btn-success">Enregistrer les notes</button>
                        <a href="bulletin.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-secondary">Voir bulletin</a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Sélectionne un étudiant pour charger ses matières et saisir les notes.</div>
    <?php endif; ?>
</div>
</body>
</html>

