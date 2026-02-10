<?php
// test_compte_resultat_final.php
// TEST FINAL DU COMPTE DE RÉSULTAT AVEC LES DONNÉES INSÉRÉES

require_once 'config/database.php';

try {
    $pdo = getPDOConnection();
    echo "<div class='container py-4'>";
    echo "<h1 class='text-center mb-4'><i class='fas fa-chart-line me-2'></i>Test Final - Compte de Résultat</h1>";
    
    // Test des PRODUITS
    $sql_produits = "
        SELECT 
            c.numero_compte,
            c.nom_compte,
            SUM(o.montant) as total_produits
        FROM operations_comptables o
        JOIN comptes_ohada c ON o.compte_credit = c.numero_compte
        WHERE c.nature_compte = 'produit'
        GROUP BY c.numero_compte, c.nom_compte
        HAVING total_produits > 0
        ORDER BY c.numero_compte
    ";
    
    $produits = $pdo->query($sql_produits)->fetchAll(PDO::FETCH_ASSOC);
    $total_produits = array_sum(array_column($produits, 'total_produits'));
    
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-success text-white'><h5 class='mb-0'><i class='fas fa-arrow-up me-2'></i>PRODUITS - " . count($produits) . " comptes</h5></div>";
    echo "<div class='card-body'>";
    foreach ($produits as $produit) {
        echo "<div class='d-flex justify-content-between border-bottom py-2'>";
        echo "<div><strong>{$produit['numero_compte']}</strong> - {$produit['nom_compte']}</div>";
        echo "<div class='text-success fw-bold'>" . number_format($produit['total_produits'], 2, ',', ' ') . " FCFA</div>";
        echo "</div>";
    }
    echo "<div class='d-flex justify-content-between mt-3 pt-2 border-top'>";
    echo "<div><strong>TOTAL PRODUITS</strong></div>";
    echo "<div class='text-success fw-bold fs-5'>" . number_format($total_produits, 2, ',', ' ') . " FCFA</div>";
    echo "</div>";
    echo "</div></div>";
    
    // Test des CHARGES
    $sql_charges = "
        SELECT 
            c.numero_compte,
            c.nom_compte,
            SUM(o.montant) as total_charges
        FROM operations_comptables o
        JOIN comptes_ohada c ON o.compte_debit = c.numero_compte
        WHERE c.nature_compte = 'charge'
        GROUP BY c.numero_compte, c.nom_compte
        HAVING total_charges > 0
        ORDER BY c.numero_compte
    ";
    
    $charges = $pdo->query($sql_charges)->fetchAll(PDO::FETCH_ASSOC);
    $total_charges = array_sum(array_column($charges, 'total_charges'));
    
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-danger text-white'><h5 class='mb-0'><i class='fas fa-arrow-down me-2'></i>CHARGES - " . count($charges) . " comptes</h5></div>";
    echo "<div class='card-body'>";
    foreach ($charges as $charge) {
        echo "<div class='d-flex justify-content-between border-bottom py-2'>";
        echo "<div><strong>{$charge['numero_compte']}</strong> - {$charge['nom_compte']}</div>";
        echo "<div class='text-danger fw-bold'>" . number_format($charge['total_charges'], 2, ',', ' ') . " FCFA</div>";
        echo "</div>";
    }
    echo "<div class='d-flex justify-content-between mt-3 pt-2 border-top'>";
    echo "<div><strong>TOTAL CHARGES</strong></div>";
    echo "<div class='text-danger fw-bold fs-5'>" . number_format($total_charges, 2, ',', ' ') . " FCFA</div>";
    echo "</div>";
    echo "</div></div>";
    
    // CALCUL DU RÉSULTAT
    $resultat = $total_produits - $total_charges;
    $resultat_class = $resultat >= 0 ? 'success' : 'danger';
    $resultat_icon = $resultat >= 0 ? 'check-circle' : 'exclamation-triangle';
    $resultat_text = $resultat >= 0 ? 'BÉNÉFICE' : 'PERTE';
    
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-$resultat_class text-white'><h5 class='mb-0'><i class='fas fa-calculator me-2'></i>RÉSULTAT DE L'EXERCICE</h5></div>";
    echo "<div class='card-body text-center'>";
    echo "<h3 class='text-$resultat_class'><i class='fas fa-$resultat_icon me-2'></i>$resultat_text : " . number_format(abs($resultat), 2, ',', ' ') . " FCFA</h3>";
    echo "<div class='row mt-3'>";
    echo "<div class='col-md-6'><strong>Total Produits:</strong> " . number_format($total_produits, 2, ',', ' ') . " FCFA</div>";
    echo "<div class='col-md-6'><strong>Total Charges:</strong> " . number_format($total_charges, 2, ',', ' ') . " FCFA</div>";
    echo "</div>";
    echo "</div></div>";
    
    // LIEN VERS LE COMPTE DE RÉSULTAT COMPLET
    echo "<div class='text-center'>";
    echo "<a href='compte_resultat.php' class='btn btn-primary btn-lg'>";
    echo "<i class='fas fa-external-link-alt me-2'></i>Voir le Compte de Résultat Complet";
    echo "</a>";
    echo "</div>";
    
    echo "</div>"; // Fermeture du container
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erreur: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Final Compte de Résultat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php
    // Le contenu PHP s'affichera ici
    ?>
</body>
</html>
