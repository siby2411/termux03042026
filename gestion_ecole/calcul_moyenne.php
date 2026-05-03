<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$page_title = "Moteur de Calcul - OMEGA";
include 'header_ecole.php';

$message = "";
if (isset($_POST['run_calc'])) {
    // Calcul de la moyenne pondérée (40% CC, 60% Exam) pour toutes les notes
    $sql = "UPDATE notes SET moyenne_matiere = ( ( (IFNULL(note_cc1,0) + IFNULL(note_cc2,0))/2 ) * 0.4) + (IFNULL(note_exam,0) * 0.6)";
    if ($conn->query($sql)) {
        $message = "Les moyennes de toutes les matières ont été recalculées avec succès.";
    } else {
        $message = "Erreur lors du calcul : " . $conn->error;
    }
}
?>

<div class="container mt-5">
    <div class="card shadow border-0">
        <div class="card-body text-center p-5">
            <i class="bi bi-cpu fs-1 text-primary mb-3"></i>
            <h2>Moteur de Calcul Académique</h2>
            <p class="text-muted">Cet outil synchronise les notes saisies et applique les coefficients pour générer les bulletins.</p>
            
            <?php if($message): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <button type="submit" name="run_calc" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-play-fill"></i> Lancer la Mise à Jour des Moyennes
                </button>
            </form>
            <div class="mt-4">
                <a href="index.php" class="btn btn-link">Retour au Tableau de Bord</a>
            </div>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
