<?php
include 'includes/db.php';

echo "### Démarrage de l'injection des données de test ###\n";

try {
    // 1. Nettoyage (Optionnel - à commenter si vous voulez garder vos données actuelles)
    // $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE produits; TRUNCATE clients; TRUNCATE devis; TRUNCATE commandes; TRUNCATE stock_logs; SET FOREIGN_KEY_CHECKS = 1;");

    // 2. Injection de 10 Produits (High-Tech)
    $produits = [
        ['REF-MAC01', 'MacBook Pro M3', 2499.00, 15, 2],
        ['REF-DEL02', 'Dell XPS 15', 1850.00, 10, 3],
        ['REF-SCR03', 'Écran 4K LG 27"', 450.00, 25, 5],
        ['REF-KBD04', 'Clavier Logitech MX', 110.00, 50, 10],
        ['REF-MOU05', 'Souris Ergonomique', 85.00, 40, 8],
        ['REF-SRV06', 'Serveur NAS 8TB', 1200.00, 5, 1],
        ['REF-SWI07', 'Switch Cisco 24p', 890.00, 12, 2],
        ['REF-WIF08', 'Borne WiFi 6E', 299.00, 20, 4],
        ['REF-CAB09', 'Lot 50x Câbles RJ45', 150.00, 100, 20],
        ['REF-UPS10', 'Onduleur 1500VA', 350.00, 8, 2]
    ];

    $stmtP = $pdo->prepare("INSERT INTO produits (ref_interne, designation, prix_unitaire, stock_actuel, seuil_alerte) VALUES (?, ?, ?, ?, ?)");
    foreach ($produits as $p) { $stmtP->execute($p); }
    echo "- 10 Produits injectés.\n";

    // 3. Injection de 10 Clients (Sénégal & International)
    $clients = [
        ['Sonatel Orange', 'contact@sonatel.sn', '+221 33 800 00 00', 'Dakar'],
        ['Baobab Group', 'info@baobab.com', '+221 33 900 11 22', 'Dakar'],
        ['TotalEnergies SN', 'b2b@total.sn', '+221 33 700 88 99', 'Rufisque'],
        ['Gainde 2000', 'admin@gainde.sn', '+221 33 600 55 44', 'Dakar'],
        ['Auchan Sénégal', 'logistique@auchan.sn', '+221 33 500 22 11', 'Mermoz'],
        ['Free Sénégal', 'corp@free.sn', '+221 33 400 33 44', 'Almadies'],
        ['Wave Mobile', 'finance@wave.com', '+221 33 300 11 00', 'Plateau'],
        ['Dakar Port', 'it@pad.sn', '+221 33 200 44 55', 'Dakar'],
        ['CFAO Motors', 'sales@cfao.sn', '+221 33 100 66 77', 'Km 4'],
        ['Kirène Group', 'usine@kirene.sn', '+221 33 000 99 88', 'Diass']
    ];

    $stmtC = $pdo->prepare("INSERT INTO clients (nom, email, telephone, ville) VALUES (?, ?, ?, ?)");
    foreach ($clients as $c) { $stmtC->execute($c); }
    echo "- 10 Clients injectés.\n";

    // 4. Simulation de Commandes Facturées (pour Analytics BI)
    // On crée des commandes sur les 4 derniers mois pour voir la courbe de CA
    $mois = ['2026-01-10', '2026-01-25', '2026-02-05', '2026-02-20', '2026-03-01', '2026-03-05'];
    $stmtCmd = $pdo->prepare("INSERT INTO commandes (client_nom, total_ht, etat, date_commande) VALUES (?, ?, 'facturee', ?)");
    
    foreach ($mois as $m) {
        $stmtCmd->execute(['Sonatel Orange', rand(5000, 15000), $m]);
    }
    echo "- 6 Commandes historiques injectées pour le graphique de CA.\n";

    echo "### Injection terminée avec succès ! ###\n";

} catch (Exception $e) {
    die("ERREUR : " . $e->getMessage());
}
?>
