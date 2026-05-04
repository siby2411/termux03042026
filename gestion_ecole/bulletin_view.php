<?php
// Fichier : bulletin_view.php - Affichage complet du bulletin
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect_ecole.php';

// Vérification de la session
if (!isset($_SESSION['role'])) {
    $_SESSION['message'] = "Veuillez vous connecter pour accéder au bulletin.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php");
    exit();
}

// Connexion à la base
$conn = db_connect_ecole();
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

// Récupération du code étudiant
$code_etudiant = $_GET['code_etudiant'] ?? null;
if (!$code_etudiant) {
    $_SESSION['message'] = "Code étudiant manquant pour l'affichage du bulletin.";
    $_SESSION['msg_type'] = "danger";
    header("Location: crud_etudiants.php");
    exit();
}

// Sécurité : Un étudiant ne peut voir que son propre bulletin
if ($_SESSION['role'] == 'etudiant' && $code_etudiant !== $_SESSION['code_etudiant']) {
    $_SESSION['message'] = "Accès refusé. Vous ne pouvez consulter que votre propre bulletin.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php");
    exit();
}

// --- 1. Récupération des informations générales et totaux du bulletin ---
$query_info = "
    SELECT
        e.nom, e.prenom, e.date_naissance, e.code_etudiant,
        c.nom_classe, c.annee_academique,
        f.nom AS nom_filiere,
        b.moyenne_semestre1, b.moyenne_semestre2, b.moyenne_annuelle, b.statut_final
    FROM etudiants e
    JOIN classes c ON e.id_classe = c.id_classe
    JOIN filieres f ON c.id_filiere = f.id
    JOIN bulletins b ON e.code_etudiant = b.code_etudiant
    WHERE e.code_etudiant = ?
    ORDER BY b.annee_academique DESC
    LIMIT 1
";

$stmt_info = $conn->prepare($query_info);
$stmt_info->bind_param("s", $code_etudiant);
$stmt_info->execute();
$bulletin_data = $stmt_info->get_result()->fetch_assoc();
$stmt_info->close();

if (!$bulletin_data) {
    echo "<div class='alert alert-warning'>Aucun bulletin généré pour cet étudiant.</div>";
    include 'footer_ecole.php';
    exit();
}

// --- 2. Récupération du détail des notes par matière ---
// Note : m.id est utilisé ici car c'est le nom de la colonne dans votre table 'matieres'
$query_notes = "
    SELECT
        n.semestre, m.nom_matiere, m.coefficient,
        n.note_cc1, n.note_cc2, n.note_examen, n.moyenne_matiere
    FROM notes n
    JOIN matieres m ON n.id_matiere = m.id
    WHERE n.code_etudiant = ?
    ORDER BY n.semestre, m.nom_matiere
";

$stmt_notes = $conn->prepare($query_notes);
$stmt_notes->bind_param("s", $code_etudiant);
$stmt_notes->execute();
$notes_result = $stmt_notes->get_result();

$notes_par_semestre = ['S1' => [], 'S2' => []];
while ($row = $notes_result->fetch_assoc()) {
    $notes_par_semestre[$row['semestre']][] = $row;
}
$stmt_notes->close();
$conn->close();

include 'header_ecole.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary"><i class="bi bi-file-earmark-person"></i> Bulletin Scolaire</h1>
        <button onclick="window.print()" class="btn btn-outline-secondary d-print-none">
            <i class="bi bi-printer"></i> Imprimer
        </button>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Informations de l'Étudiant</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nom et Prénom :</strong> <?= htmlspecialchars($bulletin_data['prenom'] . ' ' . $bulletin_data['nom']); ?></p>
                    <p><strong>Code Étudiant :</strong> <span class="badge bg-light text-dark"><?= htmlspecialchars($bulletin_data['code_etudiant']); ?></span></p>
                    <p><strong>Date de Naissance :</strong> <?= htmlspecialchars($bulletin_data['date_naissance']); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p><strong>Filière :</strong> <?= htmlspecialchars($bulletin_data['nom_filiere']); ?></p>
                    <p><strong>Classe :</strong> <?= htmlspecialchars($bulletin_data['nom_classe']); ?></p>
                    <p><strong>Année :</strong> <?= htmlspecialchars($bulletin_data['annee_academique']); ?></p>
                </div>
            </div>

            <hr>

            <h5 class="mt-3 mb-3 text-secondary">Résultats Globaux</h5>
            <div class="row text-center font-weight-bold">
                <div class="col-md-4 mb-2">
                    <div class="p-3 border rounded <?php echo ($bulletin_data['moyenne_semestre1'] >= 10) ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                        Moyenne S1 : <?php echo number_format($bulletin_data['moyenne_semestre1'], 2); ?> / 20
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-3 border rounded <?php echo ($bulletin_data['moyenne_semestre2'] >= 10) ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                        Moyenne S2 : <?php echo number_format($bulletin_data['moyenne_semestre2'], 2); ?> / 20
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="p-3 border rounded bg-primary text-white shadow-sm">
                        Moyenne Annuelle : <?php echo number_format($bulletin_data['moyenne_annuelle'], 2); ?> / 20
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <span class="h5">Résultat Final : 
                    <span class="<?= ($bulletin_data['statut_final'] == 'Admis') ? 'text-success' : 'text-danger' ?>">
                        <?= $bulletin_data['statut_final'] ?>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <?php foreach (['S1', 'S2'] as $sem): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white fw-bold">
            Détails des notes - Semestre <?= $sem ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Matière</th>
                            <th class="text-center">Coefficient</th>
                            <th class="text-center">CC1</th>
                            <th class="text-center">CC2</th>
                            <th class="text-center">Examen</th>
                            <th class="text-center">Moyenne / 20</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($notes_par_semestre[$sem])): ?>
                            <?php foreach ($notes_par_semestre[$sem] as $n): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($n['nom_matiere']) ?></td>
                                <td class="text-center"><?= $n['coefficient'] ?></td>
                                <td class="text-center"><?= $n['note_cc1'] ?></td>
                                <td class="text-center"><?= $n['note_cc2'] ?></td>
                                <td class="text-center"><?= $n['note_examen'] ?></td>
                                <td class="text-center fw-bold <?= ($n['moyenne_matiere'] < 10) ? 'text-danger' : 'text-success' ?>">
                                    <?= number_format($n['moyenne_matiere'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Aucune donnée disponible pour ce semestre.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'footer_ecole.php'; ?>
