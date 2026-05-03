<?php
// test_compte_resultat.php
// TEST DU COMPTE DE RÉSULTAT AVEC LES NOUVEAUX COMPTES

require_once 'config/database.php';

try {
    $pdo = getPDOConnection();
    echo "<div class='alert alert-success'>✅ Connexion à la base de données réussie</div>";
    
    // Test des comptes de produits
    $sql_produits = "SELECT COUNT(*) as nb FROM comptes_ohada WHERE nature_compte = 'produit'";
    $nb_produits = $pdo->query($sql_produits)->fetch(PDO::FETCH_ASSOC)['nb'];
    echo "<div class='alert alert-info'>📊 Comptes produits: $nb_produits comptes trouvés</div>";
    
    // Test des comptes de charges
    $sql_charges = "SELECT COUNT(*) as nb FROM comptes_ohada WHERE nature_compte = 'charge'";
    $nb_charges = $pdo->query($sql_charges)->fetch(PDO::FETCH_ASSOC)['nb'];
    echo "<div class='alert alert-info'>📊 Comptes charges: $nb_charges comptes trouvés</div>";
    
    // Test des opérations existantes
    $sql_operations = "SELECT COUNT(*) as nb FROM operations_comptables";
    $nb_operations = $pdo->query($sql_operations)->fetch(PDO::FETCH_ASSOC)['nb'];
    echo "<div class='alert alert-info'>📝 Opérations comptables: $nb_operations opérations trouvées</div>";
    
    // Test de la requête des produits
    $sql_test_produits = "
        SELECT 
            c.numero_compte,
            c.nom_compte,
            SUM(o.montant) as total
        FROM operations_comptables o
        JOIN comptes_ohada c ON o.compte_credit = c.numero_compte
        WHERE c.nature_compte = 'produit'
        GROUP BY c.numero_compte, c.nom_compte
        HAVING total > 0
        ORDER BY c.numero_compte
    ";
    
    $produits = $pdo->query($sql_test_produits)->fetchAll(PDO::FETCH_ASSOC);
    echo "<div class='alert alert-info'>🔍 Produits avec mouvements: " . count($produits) . " comptes</div>";
    
    // Test de la requête des charges
    $sql_test_charges = "
        SELECT 
            c.numero_compte,
            c.nom_compte,
            SUM(o.montant) as total
        FROM operations_comptables o
        JOIN comptes_ohada c ON o.compte_debit = c.numero_compte
        WHERE c.nature_compte = 'charge'
        GROUP BY c.numero_compte, c.nom_compte
        HAVING total > 0
        ORDER BY c.numero_compte
    ";
    
    $charges = $pdo->query($sql_test_charges)->fetchAll(PDO::FETCH_ASSOC);
    echo "<div class='alert alert-info'>🔍 Charges avec mouvements: " . count($charges) . " comptes</div>";
    
    // Lien vers le compte de résultat complet
    echo "<div class='alert alert-success text-center'>";
    echo "<h4>✅ Système prêt !</h4>";
    echo "<a href='compte_resultat.php' class='btn btn-primary btn-lg mt-2'>";
    echo "<i class='fas fa-chart-line me-2'></i>Accéder au Compte de Résultat Complet";
    echo "</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Erreur: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Compte de Résultat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4"><i class="fas fa-vial me-2"></i>Test du Compte de Résultat</h1>
        <?php
        // Le contenu PHP ci-dessus s'affichera ici
        ?>
    </div>
</body>
</html>
