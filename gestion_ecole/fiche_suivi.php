<?php 
include 'header_ecole.php'; 
require_once 'db_connect_ecole.php'; 
$conn = db_connect_ecole();
$code = $_GET['code_etudiant'] ?? '';

// Récupération infos étudiant et montant dû (via jointure classe)
$res_etu = $conn->query("SELECT e.*, c.montant_scolarite 
                         FROM etudiants e 
                         JOIN classes c ON e.classe_id = c.id 
                         WHERE e.code_etudiant = '$code'");
$etu = $res_etu->fetch_assoc();

// Récupération des paiements
$res_paiements = $conn->query("SELECT * FROM paiements_scolarite WHERE etudiant_id = {$etu['id']}");
$total_paye = 0;
?>

<div class="container mt-4">
    <h4>Suivi financier : <?= $etu['nom'] . ' ' . $etu['prenom'] ?> (<?= $code ?>)</h4>
    
    <div class="card p-4 mb-4">
        <h5>Situation comptable</h5>
        <table class="table">
            <?php while($p = $res_paiements->fetch_assoc()) { 
                $total_paye += $p['montant_verse']; ?>
                <tr>
                    <td><?= $p['date_paiement'] ?></td>
                    <td><?= $p['mois_paye'] ?></td>
                    <td><?= number_format($p['montant_verse'], 0) ?> FCFA</td>
                    <td>Reçu N°: <?= $p['recu_numero'] ?></td>
                </tr>
            <?php } ?>
        </table>
        
        <?php $reste = $etu['montant_scolarite'] - $total_paye; ?>
        <div class="alert <?= ($reste > 0) ? 'alert-warning' : 'alert-success' ?>">
            <strong>Total Dû :</strong> <?= number_format($etu['montant_scolarite'], 0) ?> FCFA |
            <strong>Total Versé :</strong> <?= number_format($total_paye, 0) ?> FCFA |
            <strong>Reste à payer :</strong> <?= number_format($reste, 0) ?> FCFA
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
