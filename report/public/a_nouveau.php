<?php
require_once "../config/database.php";
require_once "../includes/auth_check.php";
$page_title = "Gestion des À Nouveaux";
include "../includes/header.php";

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $exercice           = $_POST["exercice"];
    $resultat_exercice  = $_POST["resultat_exercice"];
    $report_nouveau     = $_POST["report_nouveau"];
    $reserves           = $_POST["reserves"];

    $stmt = $pdo->prepare("
        INSERT INTO a_nouveaux (exercice, resultat_exercice, report_nouveau, reserves)
        VALUES (?, ?, ?, ?)
    ");

    if ($stmt->execute([$exercice, $resultat_exercice, $report_nouveau, $reserves])) {
        $success = "À nouveaux enregistrés avec succès !";
    } else {
        $error = "Erreur lors de l’enregistrement.";
    }
}
?>

<div class="container mt-4">
    <h3 class="text-center mb-4">🧾 Gestion des À Nouveaux</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card shadow p-4">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Exercice</label>
                <input type="number" name="exercice" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Résultat de l’exercice</label>
                <input type="number" step="0.01" name="resultat_exercice" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Report à nouveau</label>
                <input type="number" step="0.01" name="report_nouveau" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Réserves</label>
                <input type="number" step="0.01" name="reserves" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

