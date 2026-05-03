<?php
require_once 'config/db.php';

$db = getDB();

echo "=== PEUPLEMENT DES DONNÉES ===\n\n";

// Clients
echo "Création des clients...\n";
$clients = [];
for($i = 1; $i <= 50; $i++) {
    $numero = 'CLT' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT);
    $db->exec("INSERT INTO clients (numero_client, type_client, nom, prenom, telephone, ville) 
               VALUES ('$numero', 'particulier', 'Client$i', 'Nom$i', '77" . rand(1000000,9999999) . "', 'Dakar')");
    $clients[] = $db->lastInsertId();
}
echo "✓ 50 clients créés\n";

// Véhicules
echo "Création des véhicules...\n";
$vehicules = [];
$marques = ['Toyota', 'Renault', 'Peugeot', 'Hyundai'];
foreach($clients as $cid) {
    for($v = 0; $v < rand(1,2); $v++) {
        $marque = $marques[array_rand($marques)];
        $immat = strtoupper(substr($marque,0,2)) . '-' . rand(100,999) . '-SN';
        $db->exec("INSERT INTO vehicules (immatriculation, marque, modele, annee_fabrication, valeur_venale, proprietaire_id) 
                   VALUES ('$immat', '$marque', 'Modele', " . rand(2015,2025) . ", " . rand(5000000,20000000) . ", $cid)");
        $vehicules[] = $db->lastInsertId();
    }
}
echo "✓ " . count($vehicules) . " véhicules créés\n";

// Contrats
echo "Création des contrats...\n";
$formules = ['Tiers', 'Tiers_Plus', 'Tous_Risques', 'Premium'];
$prix = ['Tiers'=>150000, 'Tiers_Plus'=>280000, 'Tous_Risques'=>450000, 'Premium'=>700000];
$contrats = [];

for($i = 1; $i <= 80; $i++) {
    $cid = $clients[array_rand($clients)];
    $vid = $vehicules[array_rand($vehicules)];
    $formule = $formules[array_rand($formules)];
    $prime_nette = $prix[$formule] + rand(-20000, 50000);
    $taxe = $prime_nette * 0.1;
    $prime_ttc = $prime_nette + $taxe;
    $numero = 'CON-' . date('Y') . str_pad($i, 5, '0', STR_PAD_LEFT);
    $date_effet = date('Y-m-d', strtotime('-' . rand(0, 300) . ' days'));
    $date_echeance = date('Y-m-d', strtotime($date_effet . ' + 1 year'));
    $statut = rand(1,10) <= 8 ? 'actif' : 'expire';
    
    $db->exec("INSERT INTO contrats (numero_contrat, client_id, vehicule_id, formule, prime_nette, taxe, prime_ttc, mode_paiement, date_effet, date_echeance, statut) 
               VALUES ('$numero', $cid, $vid, '$formule', $prime_nette, $taxe, $prime_ttc, 'Annuel', '$date_effet', '$date_echeance', '$statut')");
    $contrats[] = $db->lastInsertId();
}
echo "✓ " . count($contrats) . " contrats créés\n";

// Sinistres
echo "Création des sinistres...\n";
$types = ['Accident', 'Vol', 'Incendie', 'Bris_de_glace'];
for($i = 1; $i <= 40; $i++) {
    $cid = $contrats[array_rand($contrats)];
    $type = $types[array_rand($types)];
    $montant = rand(300000, 3000000);
    $numero = 'SIN-' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT);
    $date = date('Y-m-d', strtotime('-' . rand(1, 180) . ' days'));
    $statut = ['declare', 'expertise', 'indemnise'][array_rand(['declare', 'expertise', 'indemnise'])];
    
    $db->exec("INSERT INTO sinistres (numero_sinistre, contrat_id, date_survenance, date_declaration, type_sinistre, montant_estime, montant_indemnise, statut) 
               VALUES ('$numero', $cid, '$date', '$date', '$type', $montant, " . ($statut=='indemnise'?$montant*0.8:0) . ", '$statut')");
}
echo "✓ 40 sinistres créés\n";

// Paiements
echo "Création des paiements...\n";
foreach($contrats as $cid) {
    $result = $db->query("SELECT prime_ttc, date_effet FROM contrats WHERE id=$cid");
    $c = $result->fetch(PDO::FETCH_ASSOC);
    if($c) {
        $quittance = 'Q-' . date('Ymd') . '-' . $cid;
        $db->exec("INSERT INTO paiements (contrat_id, numero_quittance, date_paiement, montant, mode_reglement, statut) 
                   VALUES ($cid, '$quittance', '{$c['date_effet']}', {$c['prime_ttc']}, 'Virement', 'valide')");
    }
}
echo "✓ " . count($contrats) . " paiements créés\n";

// Résumé
echo "\n=== RÉSUMÉ ===\n";
$stats = $db->query("SELECT 
    (SELECT COUNT(*) FROM clients) as clients,
    (SELECT COUNT(*) FROM vehicules) as vehicules,
    (SELECT COUNT(*) FROM contrats) as contrats,
    (SELECT COUNT(*) FROM contrats WHERE statut='actif') as actifs,
    (SELECT COUNT(*) FROM sinistres) as sinistres,
    (SELECT IFNULL(SUM(prime_ttc),0) FROM contrats WHERE statut='actif') as primes
")->fetch(PDO::FETCH_ASSOC);

echo "Clients: {$stats['clients']}\n";
echo "Véhicules: {$stats['vehicules']}\n";
echo "Contrats: {$stats['contrats']}\n";
echo "Contrats actifs: {$stats['actifs']}\n";
echo "Sinistres: {$stats['sinistres']}\n";
echo "Total primes: " . number_format($stats['primes'], 0, ',', ' ') . " FCFA\n";

echo "\n✅ Peuplement terminé !\n";
?>

