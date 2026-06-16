<?php 
include 'header_ecole.php'; 
require_once 'db_connect_ecole.php'; 
$conn = db_connect_ecole();
$code = $_GET['code_etudiant'] ?? '';

// Récupération infos étudiant
$res_etu = $conn->query("SELECT e.*, c.montant_scolarite 
                         FROM etudiants e 
                         JOIN classes c ON e.classe_id = c.id 
                         WHERE e.code_etudiant = '$code'");
$etu = $res_etu->fetch_assoc();

if (!$etu) {
    echo "<div class='container mt-5 alert alert-danger'>Étudiant non trouvé.</div>";
    include 'footer_ecole.php';
    exit();
}

// Requête unifiée corrigée pour gérer les types de dates différents
$sql_paiements = "
    SELECT date_paiement, 'Scolarité' as type, montant_verse, recu_numero 
    FROM paiements_scolarite 
    WHERE etudiant_id = {$etu['id']}
    UNION
    SELECT CONCAT(date_paiement, ' 00:00:00') as date_paiement, 'Inscription' as type, montant_verse, 'N/A' as recu_numero 
    FROM paiements_inscription 
    WHERE code_etudiant = '{$etu['code_etudiant']}'
    ORDER BY date_paiement DESC";

$res_paiements = $conn->query($sql_paiements);
$total_paye = 0;
?>

<div class="container mt-4">
    <h4>Suivi financier : <?= htmlspecialchars($etu['nom'] . ' ' . $etu['prenom']) ?> (<?= htmlspecialchars($code) ?>)</h4>
    
    <div class="card p-4 mb-4">
        <h5>Historique des paiements</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Reçu</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $res_paiements->fetch_assoc()) { 
                    $total_paye += $p['montant_verse']; ?>
                    <tr>
                        <td><?= $p['date_paiement'] ?></td>
                        <td><span class="badge bg-info"><?= $p['type'] ?></span></td>
                        <td><?= number_format($p['montant_verse'], 0, ' ', ' ') ?> FCFA</td>
                        <td><?= htmlspecialchars($p['recu_numero']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <?php $reste = $etu['montant_scolarite'] - $total_paye; ?>
        <div class="alert <?= ($reste > 0) ? 'alert-warning' : 'alert-success' ?> mt-3">
            <strong>Total Dû :</strong> <?= number_format($etu['montant_scolarite'], 0, ' ', ' ') ?> FCFA |
            <strong>Total Versé :</strong> <?= number_format($total_paye, 0, ' ', ' ') ?> FCFA |
            <strong>Reste à payer :</strong> <?= number_format($reste, 0, ' ', ' ') ?> FCFA
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
