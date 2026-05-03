<?php
require_once 'includes/db.php';
$pdo = getPDO();

echo "=== PEUPLEMENT DE LA BASE DE DONNÉES PORTAL E-COMMERCE ===\n";

// Vider les tables existantes
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE lignes_panier");
$pdo->exec("TRUNCATE TABLE paniers_session");
$pdo->exec("TRUNCATE TABLE sessions_portail");
$pdo->exec("TRUNCATE TABLE notifications_commande");
$pdo->exec("TRUNCATE TABLE lignes_commande");
$pdo->exec("TRUNCATE TABLE commandes");
$pdo->exec("TRUNCATE TABLE produits");
$pdo->exec("TRUNCATE TABLE categories");
$pdo->exec("TRUNCATE TABLE clients");
$pdo->exec("TRUNCATE TABLE fournisseurs");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "✓ Tables vidées\n";

// ==============================================
// 1. CLIENTS (avec noms sénégalais)
// ==============================================
echo "\nCréation des clients...\n";

$clients = [
    ['nom' => 'Diop', 'prenom' => 'Mamadou', 'email' => 'mamadou.diop@email.sn', 'telephone' => '77 123 45 67', 'ville' => 'Dakar', 'type' => 'particulier'],
    ['nom' => 'Fall', 'prenom' => 'Aïcha', 'email' => 'aicha.fall@email.sn', 'telephone' => '78 234 56 78', 'ville' => 'Thiès', 'type' => 'particulier'],
    ['nom' => 'NDIAYE ET FILS', 'prenom' => '', 'email' => 'contact@ndiaye.sn', 'telephone' => '33 123 45 67', 'ville' => 'Dakar', 'type' => 'entreprise', 'ninea' => '123456789'],
    ['nom' => 'Sow', 'prenom' => 'Oumar', 'email' => 'oumar.sow@email.sn', 'telephone' => '76 345 67 89', 'ville' => 'Saint-Louis', 'type' => 'particulier'],
    ['nom' => 'DIENG CORPORATION', 'prenom' => '', 'email' => 'commercial@dieng.sn', 'telephone' => '33 987 65 43', 'ville' => 'Dakar', 'type' => 'entreprise', 'ninea' => '987654321'],
];

foreach ($clients as $c) {
    $stmt = $pdo->prepare("INSERT INTO clients (type_client, nom, prenom, email, telephone, ville, ninea, registre_commerce, mot_de_passe, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $pwd = password_hash('client123', PASSWORD_DEFAULT);
    $stmt->execute([
        $c['type'],
        $c['nom'],
        $c['prenom'] ?: null,
        $c['email'],
        $c['telephone'],
        $c['ville'],
        $c['ninea'] ?? null,
        $c['ninea'] ?? null,
        $pwd
    ]);
    echo "✓ Client: " . ($c['prenom'] ? $c['prenom'] . ' ' : '') . $c['nom'] . "\n";
}

// ==============================================
// 2. FOURNISSEURS
// ==============================================
echo "\nCréation des fournisseurs...\n";

$fournisseurs = [
    ['nom' => 'SUNU TECH', 'contact' => 'Amadou Ba', 'email' => 'contact@sunutech.sn', 'telephone' => '33 111 22 33', 'ville' => 'Dakar'],
    ['nom' => 'EXPRESS INFORMATIQUE', 'contact' => 'Fatou Ndiaye', 'email' => 'commercial@express-info.sn', 'telephone' => '33 444 55 66', 'ville' => 'Thiès'],
    ['nom' => 'MATERIEL PLUS', 'contact' => 'Ibrahima Gueye', 'email' => 'contact@materielplus.sn', 'telephone' => '77 777 88 99', 'ville' => 'Dakar'],
];

foreach ($fournisseurs as $f) {
    $stmt = $pdo->prepare("INSERT INTO fournisseurs (nom, contact_nom, email, telephone, ville, mot_de_passe, actif) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $pwd = password_hash('fournisseur123', PASSWORD_DEFAULT);
    $stmt->execute([$f['nom'], $f['contact'], $f['email'], $f['telephone'], $f['ville'], $pwd]);
    echo "✓ Fournisseur: {$f['nom']}\n";
}

// ==============================================
// 3. CATÉGORIES
// ==============================================
echo "\nCréation des catégories...\n";

$categories = [
    ['nom' => 'Informatique', 'description' => 'Ordinateurs, périphériques et accessoires informatiques'],
    ['nom' => 'Électronique', 'description' => 'Smartphones, tablettes, TV et appareils électroniques'],
    ['nom' => 'Réseau', 'description' => 'Routeurs, switches, câbles et équipements réseau'],
    ['nom' => 'Accessoires', 'description' => 'Souris, claviers, casques, sacoches'],
    ['nom' => 'Consommables', 'description' => 'Cartouches, toner, papier, fournitures de bureau'],
];

foreach ($categories as $cat) {
    $pdo->prepare("INSERT INTO categories (nom, description, actif) VALUES (?, ?, 1)")->execute([$cat['nom'], $cat['description']]);
    echo "✓ Catégorie: {$cat['nom']}\n";
}

// ==============================================
// 4. PRODUITS
// ==============================================
echo "\nCréation des produits...\n";

$fournisseurs = $pdo->query("SELECT id FROM fournisseurs")->fetchAll();
$categories = $pdo->query("SELECT id, nom FROM categories")->fetchAll();
$cat_map = [];
foreach ($categories as $cat) { $cat_map[$cat['nom']] = $cat['id']; }

$produits = [
    ['code' => 'PC-001', 'nom' => 'Ordinateur Portable Dell Latitude', 'cat' => 'Informatique', 'prix' => 450000, 'stock' => 15, 'seuil' => 5],
    ['code' => 'PC-002', 'nom' => 'Ordinateur Portable HP EliteBook', 'cat' => 'Informatique', 'prix' => 525000, 'stock' => 8, 'seuil' => 3],
    ['code' => 'SM-001', 'nom' => 'Smartphone Samsung Galaxy A54', 'cat' => 'Électronique', 'prix' => 185000, 'stock' => 25, 'seuil' => 10],
    ['code' => 'SM-002', 'nom' => 'iPhone 14 Pro', 'cat' => 'Électronique', 'prix' => 650000, 'stock' => 5, 'seuil' => 2],
    ['code' => 'ACC-001', 'nom' => 'Souris sans fil Logitech', 'cat' => 'Accessoires', 'prix' => 12500, 'stock' => 50, 'seuil' => 20],
    ['code' => 'ACC-002', 'nom' => 'Clavier mécanique RGB', 'cat' => 'Accessoires', 'prix' => 35000, 'stock' => 20, 'seuil' => 8],
    ['code' => 'RES-001', 'nom' => 'Routeur WiFi TP-Link', 'cat' => 'Réseau', 'prix' => 45000, 'stock' => 12, 'seuil' => 5],
    ['code' => 'CONS-001', 'nom' => 'Cartouche d\'encre HP 664', 'cat' => 'Consommables', 'prix' => 12500, 'stock' => 30, 'seuil' => 10],
];

foreach ($produits as $p) {
    $stmt = $pdo->prepare("INSERT INTO produits (code, nom, categorie_id, prix_vente, quantite_stock, seuil_alerte, fournisseur_principal, actif) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([
        $p['code'], $p['nom'], $cat_map[$p['cat']], $p['prix'], $p['stock'], $p['seuil'],
        $fournisseurs[array_rand($fournisseurs)]['id']
    ]);
    echo "✓ Produit: {$p['nom']}\n";
}

// ==============================================
// 5. COMMANDES DE TEST
// ==============================================
echo "\nCréation des commandes test...\n";

$clients = $pdo->query("SELECT id FROM clients")->fetchAll();
$produits_liste = $pdo->query("SELECT id, prix_vente FROM produits")->fetchAll();

for ($i = 0; $i < 15; $i++) {
    $client = $clients[array_rand($clients)];
    $type = rand(0, 1) ? 'vente' : 'achat';
    $date = date('Y-m-d', strtotime('-' . rand(0, 30) . ' days'));
    $statuts = ['confirmée', 'en_préparation', 'expédiée', 'livrée'];
    $statut = $statuts[array_rand($statuts)];
    
    // Sélectionner 1-3 produits
    $selected = array_rand($produits_liste, rand(1, 3));
    if (!is_array($selected)) $selected = [$selected];
    
    $total_ht = 0;
    $lignes = [];
    foreach ($selected as $idx) {
        $prod = $produits_liste[$idx];
        $qte = rand(1, 3);
        $total_ht += $prod['prix_vente'] * $qte;
        $lignes[] = ['produit_id' => $prod['id'], 'quantite' => $qte, 'prix' => $prod['prix_vente']];
    }
    
    $total_tva = $total_ht * 0.18;
    $total_ttc = $total_ht + $total_tva;
    
    $num_cmd = 'CMD-' . date('Ymd') . '-' . rand(1000, 9999);
    
    $stmt = $pdo->prepare("INSERT INTO commandes (numero_commande, type_commande, client_id, date_commande, total_ht, total_tva, total_ttc, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$num_cmd, $type, $client['id'], $date, $total_ht, $total_tva, $total_ttc, $statut]);
    $cmd_id = $pdo->lastInsertId();
    
    foreach ($lignes as $l) {
        $stmt = $pdo->prepare("INSERT INTO lignes_commande (commande_id, produit_id, quantite, prix_unitaire, tva, total_ht, total_tva, total_ttc) VALUES (?, ?, ?, ?, 18, ?, ?, ?)");
        $sous_total = $l['prix'] * $l['quantite'];
        $sous_tva = $sous_total * 0.18;
        $stmt->execute([$cmd_id, $l['produit_id'], $l['quantite'], $l['prix'], $sous_total, $sous_tva, $sous_total + $sous_tva]);
    }
    
    if ($i % 5 == 0) echo "  $i commandes créées...\n";
}
echo "✓ " . $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn() . " commandes créées\n";

// ==============================================
// 6. RÉCAPITULATIF
// ==============================================
echo "\n========================================\n";
echo "PEUPLEMENT TERMINÉ AVEC SUCCÈS !\n";
echo "========================================\n";
echo "📊 RÉCAPITULATIF:\n";
echo "👥 Clients: " . $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn() . "\n";
echo "🏢 Fournisseurs: " . $pdo->query("SELECT COUNT(*) FROM fournisseurs")->fetchColumn() . "\n";
echo "📦 Produits: " . $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn() . "\n";
echo "📦 Catégories: " . $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() . "\n";
echo "📋 Commandes: " . $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn() . "\n";
echo "\n🔑 Identifiants de connexion:\n";
echo "   Client: mamadou.diop@email.sn / client123\n";
echo "   Client entreprise: contact@ndiaye.sn / client123\n";
echo "   Fournisseur: contact@sunutech.sn / fournisseur123\n";
