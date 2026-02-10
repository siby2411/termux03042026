<?php
require_once __DIR__ . '/../config/database.php'; // définit $conn
include __DIR__ . '/../views/sidebar.php';
include __DIR__ . '/../views/topbar.php';

// Total écritures
$stmt = $conn->query("SELECT COUNT(*) as total FROM ECRITURES_COMPTABLES");
$totalEcritures = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Totaux par classe pour Chart.js
$totalsParClasse = [];
$stmt2 = $conn->query("SELECT pc.classe, SUM(ec.montant) as total
                       FROM ECRITURES_COMPTABLES ec
                       JOIN PLAN_COMPTABLE_UEMOA pc ON ec.compte_debite_id = pc.compte_id
                       GROUP BY pc.classe
                       ORDER BY pc.classe");
while($row = $stmt2->fetch(PDO::FETCH_ASSOC)){
    $totalsParClasse[] = $row;
}

// Préparer les données pour Chart.js
$classes = [];
$totaux = [];
foreach($totalsParClasse as $t){
    $classes[] = 'Classe ' . $t['classe'];
    $totaux[] = $t['total'];
}
?>

