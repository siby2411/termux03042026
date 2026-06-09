<?php
require_once "../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dates = $_POST['date'];
    $libelles = $_POST['libelle'];
    $debits = $_POST['debit'];
    $credits = $_POST['credit'];

    $stmt = $pdo->prepare("INSERT INTO releves_bancaires (date_operation, libelle, debit, credit) VALUES (?, ?, ?, ?)");

    foreach ($dates as $i => $date) {
        if (!empty($libelles[$i])) {
            $stmt->execute([$date, $libelles[$i], $debits[$i], $credits[$i]]);
        }
    }

    header("Location: rapprochement.php?success=1");
    exit();
}
