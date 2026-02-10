<?php
// Fichier : detail_document.php
// Affiche le document (Facture/BL/BC) après sa création

session_start();
include_once 'db_connect.php';

if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$id_facture = intval($_GET['id'] ?? 0);
$type_document = strtoupper($_GET['type'] ?? 'FACTURE');

if ($id_facture === 0) {
    header("Location: facturation.php");
    exit();
}

$conn = db_connect();

// Récupérer l'entête du document
$sql_entete = "SELECT f.numero_facture, f.date_facture, f.total_facture, f.type_document, 
               c.nom AS nom_client, c.adresse, v.nom AS nom_vendeur
               FROM factures f
               JOIN clients c ON f.id_client = c.id_client
               JOIN vendeurs v ON f.id_vendeur = v.id_vendeur
               WHERE f.id_facture = ?";
$stmt_entete = $conn->prepare($sql_entete);
$stmt_entete->bind_param("i", $id_facture);
$stmt_entete->execute();
$document = $stmt_entete->get_result()->fetch_assoc();

// Récupérer les détails/lignes du document
$sql_details = "SELECT p.code_produit, p.designation, df.quantite, df.prix_vente, df.sous_total
                FROM details_facture df
                JOIN produits p ON df.id_produit = p.id_produit
                WHERE df.id_facture = ?";
$stmt_details = $conn->prepare($sql_details);
$stmt_details->bind_param("i", $id_facture);
$stmt_details->execute();
$details = $stmt_details->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

if (!$document) {
    $_SESSION['message'] = "Document introuvable.";
    header("Location: facturation.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail Document <?php echo $document['numero_facture']; ?></title>
    </head>
<body>
    <button onclick="window.print()">Imprimer ce Document</button>
    <p><a href="facturation.php">Créer un nouveau Document</a> | <a href="dashboard.php">Tableau de Bord</a></p>

    <div style="border: 1px solid black; padding: 20px;">
        <h2><?php echo $document['type_document']; ?> N° <?php echo $document['numero_facture']; ?></h2>
        <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($document['date_facture'])); ?></p>
        <p><strong>Vendeur:</strong> <?php echo htmlspecialchars($document['nom_vendeur']); ?></p>
        <hr>
        <p><strong>Client:</strong> <?php echo htmlspecialchars($document['nom_client']); ?></p>
        <p><strong>Adresse:</strong> <?php echo htmlspecialchars($document['adresse']); ?></p>
    </div>

    <h3>Détails</h3>
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">
        <thead>
            <tr>
                <th>Code Produit</th>
                <th>Désignation</th>
                <th>Quantité</th>
                <th>Prix Vente</th>
                <th>Montant Partiel (Sous-Total)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $d): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['code_produit']); ?></td>
                <td><?php echo htmlspecialchars($d['designation']); ?></td>
                <td><?php echo $d['quantite']; ?></td>
                <td><?php echo number_format($d['prix_vente'], 2, ',', ' '); ?></td>
                <td><?php echo number_format($d['sous_total'], 2, ',', ' '); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" align="right"><strong>TOTAL GLOBAL (TTC):</strong></td>
                <td><strong><?php echo number_format($document['total_facture'], 2, ',', ' '); ?></strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
