<?php
$page_title = "Ajouter un compte";
require_once "../includes/db.php";
require_once "layout.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $compte_id = intval($_POST["compte_id"]);
    $intitule = trim($_POST["intitule"]);
    $classe = intval(substr($compte_id, 0, 1));
    $solde_normal = $_POST["solde_normal"];
    $nature = $_POST["nature_resultat"];

    if ($classe < 1 || $classe > 8) {
        $message = "<div class='alert alert-danger'>Compte invalide selon SYSCOHADA.</div>";
    } else {

        try {
            $req = $pdo->prepare("
                INSERT INTO PLAN_COMPTABLE_UEMOA 
                (compte_id, intitule_compte, classe, solde_normal, nature_resultat)
                VALUES (?, ?, ?, ?, ?)
            ");
            $req->execute([$compte_id, $intitule, $classe, $solde_normal, $nature]);

            $message = "<div class='alert alert-success'>Compte ajouté avec succès.</div>";

        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur : ". $e->getMessage() ."</div>";
        }
    }
}
?>

<div class="card p-4">
<h5>Ajouter un compte SYSCOHADA</h5>

<?= $message ?>

<form method="post">

<label class="form-label">Numéro de compte</label>
<input type="number" name="compte_id" class="form-control" required>

<label class="form-label mt-2">Intitulé</label>
<input type="text" name="intitule" class="form-control" required>

<label class="form-label mt-2">Sens normal</label>
<select name="solde_normal" class="form-control">
<option value="D">Débit</option>
<option value="C">Crédit</option>
</select>

<label class="form-label mt-2">Nature</label>
<select name="nature_resultat" class="form-control">
<option value="BIL">Bilan</option>
<option value="EXP">Exploitation</option>
<option value="FIN">Financier</option>
<option value="HAO">Hors Activité Ordinaire</option>
</select>

<button class="btn btn-primary mt-3">Enregistrer</button>
</form>

</div>




