<?php
session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();

// 1. Récupérer le stock actuel de TOUS les produits
$stocks = [];
$result_stocks = $conn->query("SELECT id_produit, code_produit, designation, stock_actuel FROM produits ORDER BY designation ASC");
while ($row = $result_stocks->fetch_assoc()) {
    $stocks[$row['id_produit']] = $row;
    // Initialiser les totaux de mouvement
    $stocks[$row['id_produit']]['total_appro'] = 0;
    $stocks[$row['id_produit']]['total_vente'] = 0;
}

// 2. Calculer le total des approvisionnements (depuis le début ou sur une période donnée)
// Pour la simplicité, nous prenons tous les appros depuis la création de la DB
$result_appro = $conn->query("SELECT id_produit, SUM(quantite_entree) as total_appro FROM approvisionnements GROUP BY id_produit");
while ($row = $result_appro->fetch_assoc()) {
    if (isset($stocks[$row['id_produit']])) {
        $stocks[$row['id_produit']]['total_appro'] = $row['total_appro'];
    }
}

// 3. Calculer le total des Ventes/BL (décrémentation)
// Nous devons joindre les détails facture et vérifier que ce n'est pas un Bon de Commande (BC)
$sql_vente = "SELECT df.id_produit, SUM(df.quantite) as total_vente
              FROM details_facture df
              JOIN factures f ON df.id_facture = f.id_facture
              WHERE f.type_document IN ('FACTURE', 'BL')
              GROUP BY df.id_produit";

$result_vente = $conn->query($sql_vente);
while ($row = $result_vente->fetch_assoc()) {
    if (isset($stocks[$row['id_produit']])) {
        $stocks[$row['id_produit']]['total_vente'] = $row['total_vente'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Suivi Stock</title>
    <style>
        .alerte_rupture { background-color: #fdd; }
    </style>
</head>
<body>
    <h1>Tableau de Bord Suivi des Stocks</h1>
    <p><a href="dashboard.php">Retour au Tableau de Bord</a></p>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Code</th>
                <th>Désignation</th>
                <th>Total Appro (Entrées)</th>
                <th>Total Ventes/BL (Sorties)</th>
                <th>Stock Théorique (Appro - Vente)</th>
                <th>Stock Actuel (Réel DB)</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stocks as $s): ?>
            <?php 
                $stock_theorique = $s['total_appro'] - $s['total_vente'];
                $difference = $s['stock_actuel'] - $stock_theorique;
                $statut = ($s['stock_actuel'] <= 5) ? 'Rupture/Alerte' : 'OK'; 
                $class = ($statut !== 'OK') ? 'alerte_rupture' : '';
            ?>
            <tr class="<?php echo $class; ?>">
                <td><?php echo htmlspecialchars($s['code_produit']); ?></td>
                <td><?php echo htmlspecialchars($s['designation']); ?></td>
                <td><?php echo $s['total_appro']; ?></td>
                <td><?php echo $s['total_vente']; ?></td>
                <td><?php echo $stock_theorique; ?></td>
                <td><strong style="color: <?php echo ($statut !== 'OK' ? 'red' : 'green'); ?>"><?php echo $s['stock_actuel']; ?></strong></td>
                <td><?php echo $statut; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p>Le Stock Actuel (Réel DB) est le stock géré par les triggers.</p>
    <p>Le Stock Théorique est la différence cumulée des mouvements enregistrés.</p>
</body>
</html>
