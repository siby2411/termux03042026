<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if (!isset($_GET['id'])) {
    die("ID de paiement manquant.");
}

$id = intval($_GET['id']);

// Requête pour récupérer les détails du paiement, de l'étudiant et de sa classe
$sql = "SELECT p.*, e.nom, e.prenom, e.code_etudiant, c.nom_classe 
        FROM paiements_scolarite p 
        JOIN etudiants e ON p.etudiant_id = e.id 
        JOIN classes c ON e.id_classe = c.id_classe
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Paiement introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu_<?= $data['recu_numero'] ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 14px; color: #000; background: #fff; }
        .ticket { width: 80mm; margin: auto; padding: 10px; border: 1px dashed #ccc; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .info { margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 5px 0; }
        .total { font-size: 18px; font-weight: bold; text-align: center; margin-top: 20px; border: 2px solid #000; padding: 10px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; }
        .signature { margin-top: 40px; display: flex; justify-content: space-between; font-size: 11px; }
        
        @media print {
            .no-print { display: none; }
            .ticket { border: none; width: 100%; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center; margin: 20px;">
    <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Imprimer le Reçu</button>
    <a href="crud_paiements.php" style="margin-left: 10px;">Retour à la gestion</a>
</div>

<div class="ticket">
    <div class="header">
        <h2>OMEGA CONSULTING</h2>
        <small>Expertise & Formation Informatique</small><br>
        <small>Dakar, Sénégal | Tel: +221 33 000 00 00</small>
    </div>

    <div class="info">
        <strong>RECU N° :</strong> <?= $data['recu_numero'] ?><br>
        <strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($data['date_paiement'])) ?><br>
        <strong>Caissier :</strong> Administrateur
    </div>

    <div class="details">
        <table>
            <tr>
                <td><strong>Étudiant :</strong></td>
                <td style="text-align: right;"><?= strtoupper($data['nom']) ?> <?= $data['prenom'] ?></td>
            </tr>
            <tr>
                <td><strong>Matricule :</strong></td>
                <td style="text-align: right;"><?= $data['code_etudiant'] ?></td>
            </tr>
            <tr>
                <td><strong>Classe :</strong></td>
                <td style="text-align: right;"><?= $data['nom_classe'] ?></td>
            </tr>
            <tr>
                <td><strong>Libellé :</strong></td>
                <td style="text-align: right;">Scolarité / Mensualité</td>
            </tr>
            <tr>
                <td><strong>Mode :</strong></td>
                <td style="text-align: right;"><?= $data['mode_paiement'] ?></td>
            </tr>
        </table>
    </div>

    <div class="total">
        TOTAL : <?= number_format($data['montant_verse'], 0, ',', ' ') ?> FCFA
    </div>

    <div class="signature">
        <div>L'Étudiant<br>(Signature)</div>
        <div>La Comptabilité<br>(Cachet)</div>
    </div>

    <div class="footer">
        <p>Merci pour votre confiance !<br><em>Les frais payés ne sont pas remboursables.</em></p>
        <small>Généré par OMEGA ERP le <?= date('d/m/Y') ?></small>
    </div>
</div>

<script>
    // Optionnel : Lancer l'impression automatiquement
    // window.onload = function() { window.print(); }
</script>

</body>
</html>
