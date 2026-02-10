

<?php
$page_title = "Saisie des écritures";
require_once "../includes/auth_check.php";
require_once "../includes/db.php";
require_once "layout.php";

// Traitement du formulaire
$errors = [];
$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_op = $_POST['date_operation'];
    $libelle = $_POST['libelle_operation'];
    $compte_debite = $_POST['compte_debite_id'];
    $compte_credite = $_POST['compte_credite_id'];
    $montant = $_POST['montant'];

    // Vérification comptes
    function checkCompte($pdo, $id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM PLAN_COMPTABLE_UEMOA WHERE compte_id=?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    if (!checkCompte($pdo, $compte_debite)) {
        $errors[] = "Le compte débité n'existe pas. 
                     <a href='ajout_compte.php' class='btn btn-sm btn-warning'>Créer maintenant</a>";
    }
    if (!checkCompte($pdo, $compte_credite)) {
        $errors[] = "Le compte crédité n'existe pas. 
                     <a href='ajout_compte.php' class='btn btn-sm btn-warning'>Créer maintenant</a>";
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES 
            (societe_id,date_operation,libelle_operation,compte_debite_id,compte_credite_id,montant) 
            VALUES (1,?,?,?,?,?)");
        if ($stmt->execute([$date_op,$libelle,$compte_debite,$compte_credite,$montant])) {
            $success = "Écriture ajoutée avec succès !";
        }
    }
}
?>

<div class="card p-4 shadow-sm">
    <?php foreach($errors as $e): ?>
        <div class="alert alert-danger"><?= $e ?></div>
    <?php endforeach; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date_operation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Libellé</label>
            <input type="text" name="libelle_operation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Compte débité</label>
            <input type="number" name="compte_debite_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Compte crédité</label>
            <input type="number" name="compte_credite_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Montant</label>
            <input type="number" step="0.01" name="montant" class="form-control" required>
        </div>
        <button class="btn btn-primary">Ajouter</button>
    </form>
</div>

<?php include "footer.php"; ?>





