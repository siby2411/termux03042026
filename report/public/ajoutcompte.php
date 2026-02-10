<?php
require_once "../includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $compte_id = intval($_POST["compte_id"]);
    $intitule = trim($_POST["intitule"]);
    $classe = intval(substr($compte_id, 0, 1));  // Classe automatique
    $solde_normal = $_POST["solde_normal"];
    $nature = $_POST["nature_resultat"];

    // Vérification Syscohada : compte entre 1 et 8
    if ($classe < 1 || $classe > 8) {
        $message = "<div class='alert alert-danger'>Compte invalide selon SYSCOHADA.</div>";
    } else {
        try {
            $sql = "INSERT INTO PLAN_COMPTABLE_UEMOA 
                (compte_id, intitule_compte, classe, solde_normal, nature_resultat)
                VALUES (?, ?, ?, ?, ?)";
            $req = $pdo->prepare($sql);
            $req->execute([$compte_id, $intitule, $classe, $solde_normal, $nature]);

            $message = "<div class='alert alert-success'>Compte ajouté avec succès.</div>";

        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="p-4">

<h3>Ajouter un compte SYSCOHADA</h3>

<?= $message ?>

<form method="post" class="card p-3">

    <label class="form-label">Numéro de compte</label>
    <input type="number" name="compte_id" class="form-control" required>

    <label class="form-label mt-3">Intitulé du compte</label>
    <input type="text" name="intitule" class="form-control" required>

    <label class="form-label mt-3">Sens normal</label>
    <select name="solde_normal" class="form-control">
        <option value="D">Débit (D)</option>
        <option value="C">Crédit (C)</option>
    </select>

    <label class="form-label mt-3">Nature</label>
    <select name="nature_resultat" class="form-control">
        <option value="BIL">Bilan</option>
        <option value="EXP">Charges / Produits d’exploitation</option>
        <option value="FIN">Financiers</option>
        <option value="HAO">Hors activités ordinaires</option>
    </select>

    <button class="btn btn-primary mt-3">Enregistrer</button>

</form>

</body>
</html>

