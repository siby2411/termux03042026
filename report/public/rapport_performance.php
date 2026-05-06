<?php
$page_title = "Rapport d'évaluation SYSCOHADA";
require_once 'inc_navbar.php';

$total_operations = $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES")->fetchColumn();
$total_montant = $pdo->query("SELECT SUM(montant) FROM ECRITURES_COMPTABLES")->fetchColumn();
$comptes_utilises = $pdo->query("SELECT COUNT(DISTINCT compte_debite_id) FROM ECRITURES_COMPTABLES")->fetchColumn();
?>
<div class="alert alert-success">
    <h5>✅ État des lieux après peuplement initial</h5>
    <ul>
        <li><?= $total_operations ?> opérations comptables enregistrées</li>
        <li><?= number_format($total_montant, 0, ',', ' ') ?> FCFA de flux comptables</li>
        <li><?= $comptes_utilises ?> comptes utilisés dans le plan comptable UEMOA</li>
    </ul>
</div>
<?php include 'inc_footer.php'; ?>
