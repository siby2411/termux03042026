<?php
// Fichier : bulletin_edit.php
// Version colorée "bulletin officiel" Bootstrap 5

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php';

// Vérification de rôle et paramètre code_etudiant
if (!isset($_SESSION['role']) || !isset($_GET['code_etudiant'])) {
    $_SESSION['message'] = "Accès non autorisé ou étudiant non spécifié.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php"); 
    exit();
}

$code_etudiant = htmlspecialchars($_GET['code_etudiant']);
$conn = db_connect_ecole();

// Récupération des infos de l'étudiant et classe
$stmt = $conn->prepare("
    SELECT 
        e.nom, e.prenom, e.date_naissance, e.code_etudiant,
        c.nom_classe, c.annee_academique, c.id_filiere
    FROM etudiants e
    JOIN classes c ON e.id_classe = c.id_classe
    WHERE e.code_etudiant = ?
");
$stmt->bind_param("s", $code_etudiant);
$stmt->execute();
$etudiant_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$etudiant_data) {
    $_SESSION['message'] = "Étudiant non trouvé.";
    $_SESSION['msg_type'] = "danger";
    header("Location: crud_etudiants.php");
    exit();
}

// Récupération des matières et notes
$id_filiere = $etudiant_data['id_filiere'];
$annee_academique = $etudiant_data['annee_academique'];

$stmt_matieres = $conn->prepare("
    SELECT 
        m.id_matiere, m.nom_matiere, m.coefficient, m.semestre,
        COALESCE(n.note_cc1,0) AS note_cc1, 
        COALESCE(n.note_cc2,0) AS note_cc2,
        COALESCE(n.note_examen,0) AS note_exam
    FROM matieres m
    LEFT JOIN notes n ON m.id_matiere = n.id_matiere AND n.code_etudiant = ? AND n.annee_academique = ?
    WHERE m.id_filiere = ?
    ORDER BY m.semestre, m.nom_matiere
");
$stmt_matieres->bind_param("ssi", $code_etudiant, $annee_academique, $id_filiere);
$stmt_matieres->execute();
$result_matieres = $stmt_matieres->get_result();

$notes_semestres = ['S1' => [], 'S2' => []];
while ($row = $result_matieres->fetch_assoc()) {
    $cc1 = floatval($row['note_cc1']);
    $cc2 = floatval($row['note_cc2']);
    $exam = floatval($row['note_exam']);
    $moyenne = ($cc1*0.3 + $cc2*0.3 + $exam*0.4);
    $row['moyenne'] = number_format($moyenne,2);
    $notes_semestres[$row['semestre']][] = $row;
}
$stmt_matieres->close();
$conn->close();

// Calcul moyennes par semestre
function moyenne_semestre($notes) {
    $total_coef = 0;
    $total_points = 0;
    foreach ($notes as $n) {
        $coef = floatval($n['coefficient']);
        $moy = floatval($n['moyenne']);
        $total_coef += $coef;
        $total_points += $coef * $moy;
    }
    return $total_coef ? number_format($total_points/$total_coef,2) : "0.00";
}

$moy_s1 = moyenne_semestre($notes_semestres['S1']);
$moy_s2 = moyenne_semestre($notes_semestres['S2']);
$moy_annuelle = number_format( (floatval($moy_s1)+floatval($moy_s2))/2 ,2);
$statut_final = $moy_annuelle >= 10 ? "Admis" : "Refusé";

include 'header_ecole.php';
?>

<!-- Bandeau en-tête -->
<div class="text-center my-4">
    <h1 class="display-5 fw-bold">📋 Bulletin Scolaire Officiel</h1>
    <p class="lead">Année Académique : <?php echo htmlspecialchars($etudiant_data['annee_academique']); ?></p>
</div>

<!-- Informations Étudiant -->
<div class="card border-primary mb-4 shadow-sm">
    <div class="card-header bg-primary text-white fw-bold">Informations Générales</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nom et Prénom :</strong> <?php echo htmlspecialchars($etudiant_data['prenom']." ".$etudiant_data['nom']); ?></p>
                <p><strong>Code Étudiant :</strong> <?php echo htmlspecialchars($etudiant_data['code_etudiant']); ?></p>
                <p><strong>Date de Naissance :</strong> <?php echo htmlspecialchars($etudiant_data['date_naissance']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Classe :</strong> <?php echo htmlspecialchars($etudiant_data['nom_classe']); ?></p>
                <p><strong>Année Académique :</strong> <?php echo htmlspecialchars($etudiant_data['annee_academique']); ?></p>
                <p><strong>Statut Final :</strong> 
                    <span class="badge <?php echo $statut_final=="Admis"?"bg-success":"bg-danger"; ?> fs-6">
                        <?php echo $statut_final; ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Moyennes -->
<div class="row text-center mb-4">
    <div class="col-md-4">
        <div class="p-3 border rounded bg-light shadow-sm">
            <h5>Moyenne S1</h5>
            <span class="fs-4 fw-bold <?php echo $moy_s1>=10?"text-success":"text-danger"; ?>"><?php echo $moy_s1; ?> / 20</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 border rounded bg-light shadow-sm">
            <h5>Moyenne S2</h5>
            <span class="fs-4 fw-bold <?php echo $moy_s2>=10?"text-success":"text-danger"; ?>"><?php echo $moy_s2; ?> / 20</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 border rounded bg-light shadow-sm">
            <h5>Moyenne Annuelle</h5>
            <span class="fs-4 fw-bold <?php echo $moy_annuelle>=10?"text-success":"text-danger"; ?>"><?php echo $moy_annuelle; ?> / 20</span>
        </div>
    </div>
</div>

<!-- Tableaux des notes par semestre -->
<?php foreach(['S1','S2'] as $semestre): ?>
    <div class="card mb-4 shadow-sm">
        <div class="card-header <?php echo $semestre=='S1'?'bg-info':'bg-warning'; ?> text-white fw-bold">
            Détail des Notes - Semestre <?php echo $semestre; ?>
        </div>
        <div class="card-body">
            <?php if(!empty($notes_semestres[$semestre])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Matière</th>
                                <th>Coef</th>
                                <th>CC1</th>
                                <th>CC2</th>
                                <th>Exam</th>
                                <th>Moyenne Matière</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach($notes_semestres[$semestre] as $n): 
                                $moy = floatval($n['moyenne']);
                            ?>
                            <tr class="<?php echo $moy<10?'table-danger':''; ?>">
                                <td><?php echo htmlspecialchars($n['nom_matiere']); ?></td>
                                <td class="text-center"><?php echo number_format($n['coefficient'],2); ?></td>
                                <td class="text-center"><?php echo number_format(floatval($n['note_cc1']),2); ?></td>
                                <td class="text-center"><?php echo number_format(floatval($n['note_cc2']),2); ?></td>
                                <td class="text-center"><?php echo number_format(floatval($n['note_exam']),2); ?></td>
                                <td class="text-center">
                                    <span class="badge <?php echo $moy>=10?'bg-success':'bg-danger'; ?>">
                                        <?php echo number_format($moy,2); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-end fw-bold mt-2">
                    Moyenne Générale <?php echo $semestre; ?> : 
                    <span class="<?php echo ($semestre=='S1'?$moy_s1:$moy_s2)>=10?'text-success':'text-danger'; ?>">
                        <?php echo ($semestre=='S1'?$moy_s1:$moy_s2); ?> / 20
                    </span>
                </p>
            <?php else: ?>
                <p class="alert alert-info text-center">Aucune note enregistrée pour ce semestre.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<a href="crud_etudiants.php" class="btn btn-secondary mb-5">⬅ Retour à la Gestion des Étudiants</a>

<?php include 'footer_ecole.php'; ?>

