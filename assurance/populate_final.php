<?php
require_once 'config/db.php';

$db = getDB();

echo "=== PEUPLEMENT FINAL OMEGA ASSURANCE ===\n\n";

// Nettoyage complet
$db->exec("SET FOREIGN_KEY_CHECKS=0");
$db->exec("TRUNCATE paiements");
$db->exec("TRUNCATE sinistres");
$db->exec("TRUNCATE contrats");
$db->exec("TRUNCATE vehicules");
$db->exec("TRUNCATE clients");
$db->exec("SET FOREIGN_KEY_CHECKS=1");
echo "✓ Base nettoyée\n\n";

// 1. Clients (50 particuliers + 20 entreprises)
echo "Création des clients...\n";
$clients = [];

// Particuliers
for($i = 1; $i <= 50; $i++) {
    $numero = 'CLT' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT);
    $nom = ['DIOP','NDIAYE','FALL','SOW','BA','GUEYE'][array_rand(['DIOP','NDIAYE','FALL','SOW','BA','GUEYE'])];
    $prenom = ['Amadou','Mariama','Ousmane','Fatou','Ibrahima'][array_rand(['Amadou','Mariama','Ousmane','Fatou','Ibrahima'])];
    $tel = '77' . rand(1000000, 9999999);
    
    $db->exec("INSERT INTO clients (numero_client, type_client, nom, prenom, telephone, ville, statut) 
               VALUES ('$numero', 'particulier', '$nom', '$prenom', '$tel', 'Dakar', 'actif')");
    $clients[] = $db->lastInsertId();
}

// Entreprises
$raisons = ['OMEGA SARL', 'SENEXPRESS', 'DAKAR TRANS', 'AFRIQUE LOGISTIC', 'SUNU GROUPE'];
for($i = 1; $i <= 20; $i++) {
    $numero = 'ENT' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT);
    $raison = $raisons[array_rand($raisons)] . ' ' . rand(1, 50);
    $tel = '33' . rand(800000, 999999);
    
    $db->exec("INSERT INTO clients (numero_client, type_client, raison_sociale, telephone, ville, statut) 
               VALUES ('$numero', 'entreprise', '$raison', '$tel', 'Dakar', 'actif')");
    $clients[] = $db->lastInsertId();
}
echo "✓ " . count($clients) . " clients créés\n";

// 2. Véhicules (sans doublons d'immatriculation)
echo "Création des véhicules...\n";
$vehicules = [];
$immatriculations = [];
$marques = ['TOYOTA', 'RENAULT', 'PEUGEOT', 'HYUNDAI', 'KIA', 'NISSAN', 'HONDA'];
$modeles = ['Corolla', 'Clio', '208', 'i10', 'Picanto', 'Micra', 'Civic'];

foreach($clients as $client_id) {
    $nb = rand(1, 2);
    for($v = 0; $v < $nb; $v++) {
        // Générer une immatriculation unique
        do {
            $lettre1 = chr(rand(65, 90));
            $lettre2 = chr(rand(65, 90));
            $chiffre = rand(100, 999);
            $immat = $lettre1 . $lettre2 . '-' . $chiffre . '-SN';
        } while(in_array($immat, $immatriculations));
        
        $immatriculations[] = $immat;
        $marque = $marques[array_rand($marques)];
        $modele = $modeles[array_rand($modeles)];
        $valeur = rand(5000000, 25000000);
        
        $db->exec("INSERT INTO vehicules (immatriculation, marque, modele, annee_fabrication, valeur_venale, proprietaire_id, statut) 
                   VALUES ('$immat', '$marque', '$modele', " . rand(2015,2025) . ", $valeur, $client_id, 'actif')");
        $vehicules[] = $db->lastInsertId();
    }
}
echo "✓ " . count($vehicules) . " véhicules créés\n";

// 3. Contrats
echo "Création des contrats...\n";
$formules = ['Tiers', 'Tiers_Plus', 'Tous_Risques', 'Premium'];
$prix_formules = [
    'Tiers' => [150000, 180000, 200000, 220000, 250000],
    'Tiers_Plus' => [250000, 280000, 300000, 350000, 400000],
    'Tous_Risques' => [400000, 450000, 500000, 550000, 600000],
    'Premium' => [600000, 700000, 800000, 900000, 1000000]
];
$modes = ['Annuel', 'Semestriel', 'Trimestriel'];
$contrats = [];

for($i = 1; $i <= 100; $i++) {
    $client_id = $clients[array_rand($clients)];
    $vehicule_id = $vehicules[array_rand($vehicules)];
    $formule = $formules[array_rand($formules)];
    $prime_nette = $prix_formules[$formule][array_rand($prix_formules[$formule])];
    $taxe = $prime_nette * 0.10;
    $prime_ttc = $prime_nette + $taxe;
    $numero = 'CON-' . date('Y') . str_pad($i, 5, '0', STR_PAD_LEFT);
    $date_effet = date('Y-m-d', strtotime('-' . rand(0, 300) . ' days'));
    $date_echeance = date('Y-m-d', strtotime($date_effet . ' + 1 year'));
    $statut = (rand(1, 10) <= 7) ? 'actif' : 'expire';
    
    $db->exec("INSERT INTO contrats (numero_contrat, client_id, vehicule_id, formule, prime_nette, taxe, prime_ttc, mode_paiement, date_effet, date_echeance, statut, date_creation) 
               VALUES ('$numero', $client_id, $vehicule_id, '$formule', $prime_nette, $taxe, $prime_ttc, '{$modes[array_rand($modes)]}', '$date_effet', '$date_echeance', '$statut', '$date_effet')");
    $contrats[] = $db->lastInsertId();
}
echo "✓ " . count($contrats) . " contrats créés\n";

// 4. Sinistres
echo "Création des sinistres...\n";
$types = ['Accident', 'Vol', 'Incendie', 'Bris_de_glace'];
$statuts_sin = ['declare', 'expertise', 'indemnise', 'cloture'];

for($i = 1; $i <= 50; $i++) {
    if(empty($contrats)) break;
    $contrat_id = $contrats[array_rand($contrats)];
    $type = $types[array_rand($types)];
    $statut = $statuts_sin[array_rand($statuts_sin)];
    $montant_estime = rand(300000, 3000000);
    $montant_indemn = ($statut == 'indemnise' || $statut == 'cloture') ? $montant_estime * rand(70, 100)/100 : 0;
    $numero = 'SIN-' . date('Y') . str_pad($i, 5, '0', STR_PAD_LEFT);
    $date_surv = date('Y-m-d', strtotime('-' . rand(1, 200) . ' days'));
    
    $db->exec("INSERT INTO sinistres (numero_sinistre, contrat_id, date_survenance, date_declaration, type_sinistre, montant_estime, montant_indemnise, statut) 
               VALUES ('$numero', $contrat_id, '$date_surv', '$date_surv', '$type', $montant_estime, $montant_indemn, '$statut')");
}
echo "✓ 50 sinistres créés\n";

// 5. Paiements
echo "Création des paiements...\n";
$modes_reg = ['Especes', 'Virement', 'Orange_Money', 'Wave'];
$paiements_count = 0;

foreach($contrats as $contrat_id) {
    $result = $db->query("SELECT prime_ttc, date_effet FROM contrats WHERE id = $contrat_id");
    $c = $result->fetch(PDO::FETCH_ASSOC);
    if($c) {
        $quittance = 'Q-' . date('Ymd') . '-' . $contrat_id . rand(100,999);
        $db->exec("INSERT INTO paiements (contrat_id, numero_quittance, date_paiement, montant, mode_reglement, statut) 
                   VALUES ($contrat_id, '$quittance', '{$c['date_effet']}', {$c['prime_ttc']}, '{$modes_reg[array_rand($modes_reg)]}', 'valide')");
        $paiements_count++;
    }
}
echo "✓ $paiements_count paiements créés\n\n";

// Résumé final
echo "=== RÉSUMÉ FINAL ===\n";
$stats = $db->query("SELECT 
    (SELECT COUNT(*) FROM clients) as clients,
    (SELECT COUNT(*) FROM vehicules) as vehicules,
    (SELECT COUNT(*) FROM contrats) as contrats,
    (SELECT COUNT(*) FROM contrats WHERE statut='actif') as actifs,
    (SELECT COUNT(*) FROM sinistres) as sinistres,
    (SELECT COUNT(*) FROM paiements) as paiements,
    (SELECT IFNULL(SUM(prime_ttc),0) FROM contrats WHERE statut='actif') as primes
")->fetch(PDO::FETCH_ASSOC);

echo "----------------------------------------\n";
echo "Clients:           {$stats['clients']}\n";
echo "Véhicules:         {$stats['vehicules']}\n";
echo "Contrats:          {$stats['contrats']}\n";
echo "Contrats actifs:   {$stats['actifs']}\n";
echo "Sinistres:         {$stats['sinistres']}\n";
echo "Paiements:         {$stats['paiements']}\n";
echo "Total primes:      " . number_format($stats['primes'], 0, ',', ' ') . " FCFA\n";
echo "----------------------------------------\n";

echo "\n✅ Peuplement terminé avec succès !\n";
?>
