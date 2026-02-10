<?php
require_once "../config/database.php";
require_once "../includes/auth_check.php";
$page_title = "Ratios Financiers";
include "layout.php";

$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = [
        $_POST["exercice"],
        $_POST["actif_courant"],
        $_POST["passif_courant"],
        $_POST["stocks"],
        $_POST["dettes_total"],
        $_POST["capitaux_propres"],
        $_POST["resultat_net"],
        $_POST["total_actif"],
        $_POST["total_charges"],
        $_POST["total_produits"],
        $_POST["marge_brute"],
        $_POST["marge_nette"]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO ratios_financiers 
        (exercice, actif_courant, passif_courant, stocks, dettes_total, capitaux_propres, resultat_net, total_actif, total_charges, total_produits, marge_brute, marge_nette)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    if ($stmt->execute($data)) {
        $success = "Ratios enregistrés !";
    }
}
?>

<div class="container mt-4">
    <h3 class="mb-4 text-center">📊 Calcul des Ratios Financiers</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="card shadow p-4">

        <form method="POST">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Exercice</label>
                    <input name="exercice" type="number" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Actif courant</label>
                    <input name="actif_courant" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Passif courant</label>
                    <input name="passif_courant" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Stocks</label>
                    <input name="stocks" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Dettes Totales</label>
                    <input name="dettes_total" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Capitaux propres</label>
                    <input name="capitaux_propres" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Résultat net</label>
                    <input name="resultat_net" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Total actif</label>
                    <input name="total_actif" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Total charges</label>
                    <input name="total_charges" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Total produits</label>
                    <input name="total_produits" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Marge brute</label>
                    <input name="marge_brute" type="number" step="0.01" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Marge nette</label>
                    <input name="marge_nette" type="number" step="0.01" class="form-control">
                </div>

            </div>

            <button class="btn btn-success w-100">Enregistrer les ratios</button>
        </form>
    </div>
</div>



