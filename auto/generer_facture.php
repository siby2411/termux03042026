<?php
include 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupération des données de la location, du client et de la voiture
$sql = "SELECT l.*, 
               c.nom, c.prenom, c.email, c.telephone, c.adresse,
               v.marque, v.modele, v.prix_journalier
        FROM locations l
        JOIN clients c ON l.client_id = c.id
        JOIN voitures v ON l.voiture_id = v.id
        WHERE l.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

if (!$data) {
    die("Facture introuvable.");
}

// Calcul du nombre de jours pour l'affichage
$d1 = new DateTime($data['date_debut']);
$d2 = new DateTime($data['date_fin']);
$nb_jours = $d1->diff($d2)->days;
if($nb_jours == 0) $nb_jours = 1; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #<?php echo $data['id']; ?> - Omega Auto</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #3498db; padding-bottom: 20px; }
        .company-info h1 { margin: 0; color: #3498db; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #eee; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-section { text-align: right; font-size: 1.2em; }
        .total-amount { font-size: 1.5em; font-weight: bold; color: #2c3e50; }
        .footer-note { margin-top: 50px; font-size: 0.8em; color: #777; text-align: center; }
        
        /* Bouton d'impression caché lors de l'impression réelle */
        @media print {
            .no-print { display: none; }
            .invoice-box { border: none; box-shadow: none; }
        }
        .btn-print { background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; cursor: pointer; border: none; }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center; margin-bottom: 20px;">
    <button onclick="window.print()" class="btn-print">🖨️ Imprimer / Enregistrer en PDF</button>
    <a href="liste_locations.php" style="margin-left:10px;">Retour</a>
</div>

<div class="invoice-box">
    <div class="header">
        <div class="company-info">
            <h1>OMEGA AUTO</h1>
            <p>Dakar, Sénégal<br>Contact: +221 77 000 00 00<br>Email: contact@omega-auto.sn</p>
        </div>
        <div class="invoice-meta">
            <h2>FACTURE</h2>
            <p><strong>N° :</strong> #LOC-<?php echo str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?><br>
            <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($data['date_location'])); ?></p>
        </div>
    </div>

    <div class="details-grid">
        <div class="client-info">
            <h3 style="color:#3498db;">Client :</h3>
            <p><strong><?php echo htmlspecialchars($data['nom'] . ' ' . $data['prenom']); ?></strong><br>
            <?php echo htmlspecialchars($data['adresse']); ?><br>
            Tél: <?php echo htmlspecialchars($data['telephone']); ?></p>
        </div>
        <div class="rental-info">
            <h3 style="color:#3498db;">Détails Location :</h3>
            <p><strong>Véhicule :</strong> <?php echo htmlspecialchars($data['marque'] . ' ' . $data['modele']); ?><br>
            <strong>Période :</strong> du <?php echo date('d/m/Y', strtotime($data['date_debut'])); ?><br>
            au <?php echo date('d/m/Y', strtotime($data['date_fin'])); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Prix Unit.</th>
                <th>Quantité (Jours)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Location véhicule <?php echo htmlspecialchars($data['marque'] . ' ' . $data['modele']); ?></td>
                <td><?php echo number_format($data['prix_journalier'], 2, ',', ' '); ?> €</td>
                <td><?php echo $nb_jours; ?></td>
                <td><?php echo number_format($data['cout_total'], 2, ',', ' '); ?> €</td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <p>Sous-total : <?php echo number_format($data['cout_total'], 2, ',', ' '); ?> €</p>
        <p>TVA (0%) : 0,00 €</p>
        <p class="total-amount">TOTAL À PAYER : <?php echo number_format($data['cout_total'], 2, ',', ' '); ?> €</p>
    </div>

    <div class="footer-note">
        <p>Merci de votre confiance !<br>
        <em>Cette facture est générée automatiquement et ne nécessite pas de signature.</em></p>
    </div>
</div>

</body>
</html>
