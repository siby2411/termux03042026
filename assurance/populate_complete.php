<?php
require_once 'config/db.php';

$db = getDB();

echo "=== PEUPLEMENT COMPLET OMEGA ASSURANCE ===\n\n";

// Nettoyage complet
echo "Nettoyage des anciennes données...\n";
$db->exec("SET FOREIGN_KEY_CHECKS=0");
$db->exec("DELETE FROM paiements");
$db->exec("DELETE FROM sinistres");
$db->exec("DELETE FROM contrat_garanties");
$db->exec("DELETE FROM contrats");
$db->exec("DELETE FROM vehicules");
$db->exec("DELETE FROM clients");
$db->exec("SET FOREIGN_KEY_CHECKS=1");
echo "✓ Base nettoyée\n\n";

// 1. Insertion des clients
echo "Insertion des clients...\n";
$clients = [];
$noms = ['DIOP', 'NDIAYE', 'FALL', 'SOW', 'BA', 'GUEYE', 'DIAGNE', 'SARR', 'MBAYE', 'LY'];
$prenoms = ['Amadou', 'Mariama', 'Ousmane', 'Aissatou', 'Ibrahima', 'Fatou', 'Mamadou', 'Aminata'];
$villes = ['Dakar', 'Thiès', 'Saint-Louis', 'Touba', 'Ziguinchor', 'Kaolack', 'Mbour'];

// 50 clients particuliers
for($i = 1; $i <= 50; $i++) {
    $nom = $noms[array_rand($noms)];
    $prenom = $prenoms[array_rand($prenoms)];
    $telephone = '77' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
    $numero = 'CLT-' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT);
    
    $sql = "INSERT INTO clients (numero_client, type_client, nom, prenom, telephone, ville) 
            VALUES (:num, 'particulier', :nom, :prenom, :tel, :ville)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':num' => $numero,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':tel' => $telephone,
        ':ville' => $villes[array_rand($villes)]
    ]);
    $clients[] = $db->lastInsertId();
}

// 20 entreprises
$entreprises = ['OMEGA SARL', 'SENEXPRESS', 'DAKAR TRANS', 'AFRIQUE LOGISTIC', 'SUNU GROUPE', 'CARGILL SN', 'TOTAL SN'];
for($i = 1; $i <= 20; $i++) {
    $raison = $entreprises[array_rand($entreprises)] . ' ' . rand(1, 50);
    $telephone = '33' . str_pad(rand(800000, 999999), 6, '0', STR_PAD_LEFT);
    $numero = 'ENT-' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT);
    
    $sql = "INSERT INTO clients (numero_client, type_client, raison_sociale, telephone, ville) 
            VALUES (:num, 'entreprise', :raison, :tel, :ville)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':num' => $numero,
        ':raison' => $raison,
        ':tel' => $telephone,
        ':ville' => $villes[array_rand($villes)]
    ]);
    $clients[] = $db->lastInsertId();
}
echo "✓ " . count($clients) . " clients créés\n";

// 2. Insertion des véhicules
echo "Insertion des véhicules...\n";
$marques = ['Toyota', 'Renault', 'Peugeot', 'Hyundai', 'Kia', 'Nissan', 'Honda'];
$modeles = ['Corolla', 'Clio', '208', 'i10', 'Picanto', 'Micra', 'Civic'];
$vehicules = [];

foreach($clients as $client_id) {
    $nb = rand(1, 2);
    for($v = 0; $v < $nb; $v++) {
        $marque = $marques[array_rand($marques)];
        $modele = $modeles[array_rand($modeles)];
        $immat = strtoupper(substr($marque, 0, 2)) . '-' . rand(100, 999) . '-' . rand(1000, 9999);
        $valeur = rand(5000000, 20000000);
        
        $sql = "INSERT INTO vehicules (immatriculation, marque, modele, annee_fabrication, valeur_venale, proprietaire_id) 
                VALUES (:immat, :marque, :modele, :annee, :valeur, :proprio)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':immat' => $immat,
            ':marque' => $marque,
            ':modele' => $modele,
            ':annee' => rand(2015, 2025),
            ':valeur' => $valeur,
            ':proprio' => $client_id
        ]);
        $vehicules[] = $db->lastInsertId();
    }
}
echo "✓ " . count($vehicules) . " véhicules créés\n";

// 3. Insertion des contrats (avec données valides)
echo "Insertion des contrats...\n";
$formules = ['Tiers', 'Tiers_Plus', 'Tous_Risques', 'Premium'];
$modes = ['Annuel', 'Semestriel', 'Trimestriel'];
$contrats = [];

$prime_tiers = [150000, 180000, 200000, 220000, 250000];
$prime_tiers_plus = [250000, 280000, 300000, 350000, 400000];
$prime_tous_risques = [400000, 450000, 500000, 550000, 600000];
$prime_premium = [600000, 700000, 800000, 900000, 1000000];

for($i = 1; $i <= 100; $i++) {
    $client_id = $clients[array_rand($clients)];
    $vehicule_id = $vehicules[array_rand($vehicules)];
    $formule = $formules[array_rand($formules)];
    
    // Sélection prime selon formule
    switch($formule) {
        case 'Tiers': $prime_nette = $prime_tiers[array_rand($prime_tiers)]; break;
        case 'Tiers_Plus': $prime_nette = $prime_tiers_plus[array_rand($prime_tiers_plus)]; break;
        case 'Tous_Risques': $prime_nette = $prime_tous_risques[array_rand($prime_tous_risques)]; break;
        default: $prime_nette = $prime_premium[array_rand($prime_premium)];
    }
    
    $taxe = $prime_nette * 0.10;
    $prime_ttc = $prime_nette + $taxe;
    $date_effet = date('Y-m-d', strtotime('-' . rand(0, 300) . ' days'));
    $date_echeance = date('Y-m-d', strtotime($date_effet . ' + 1 year'));
    $numero = 'CON-' . date('Y') . str_pad($i, 6, '0', STR_PAD_LEFT);
    $statut = (rand(1, 10) <= 7) ? 'actif' : 'expire';
    
    $sql = "INSERT INTO contrats (numero_contrat, client_id, vehicule_id, formule, prime_nette, taxe, prime_ttc, mode_paiement, date_effet, date_echeance, statut, date_creation) 
            VALUES (:num, :client, :vehicule, :formule, :p_nette, :taxe, :p_ttc, :mode, :debut, :fin, :statut, :debut)";
    $stmt = $db->prepare($sql);
    
    try {
        $stmt->execute([
            ':num' => $numero,
            ':client' => $client_id,
            ':vehicule' => $vehicule_id,
            ':formule' => $formule,
            ':p_nette' => $prime_nette,
            ':taxe' => $taxe,
            ':p_ttc' => $prime_ttc,
            ':mode' => $modes[array_rand($modes)],
            ':debut' => $date_effet,
            ':fin' => $date_echeance,
            ':statut' => $statut
        ]);
        $contrats[] = $db->lastInsertId();
    } catch(PDOException $e) {
        echo "Erreur contrat: " . $e->getMessage() . "\n";
    }
}
echo "✓ " . count($contrats) . " contrats créés\n";

// 4. Insertion des sinistres
echo "Insertion des sinistres...\n";
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
    
    $sql = "INSERT INTO sinistres (numero_sinistre, contrat_id, date_survenance, date_declaration, type_sinistre, montant_estime, montant_indemnise, statut) 
            VALUES (:num, :contrat, :date_surv, :date_surv, :type, :estime, :indemn, :statut)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':num' => $numero,
        ':contrat' => $contrat_id,
        ':date_surv' => $date_surv,
        ':type' => $type,
        ':estime' => $montant_estime,
        ':indemn' => $montant_indemn,
        ':statut' => $statut
    ]);
}
echo "✓ 50 sinistres créés\n";

// 5. Insertion des paiements
echo "Insertion des paiements...\n";
$modes_paiement = ['Especes', 'Virement', 'Orange_Money', 'Wave'];
$nb_paiements = 0;

foreach($contrats as $contrat_id) {
    $stmt = $db->prepare("SELECT prime_ttc, date_effet FROM contrats WHERE id = :id");
    $stmt->execute([':id' => $contrat_id]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($c) {
        $quittance = 'Q-' . date('Ymd') . '-' . $contrat_id;
        $sql = "INSERT INTO paiements (contrat_id, numero_quittance, date_paiement, montant, mode_reglement, statut) 
                VALUES (:cid, :quit, :date, :montant, :mode, 'valide')";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':cid' => $contrat_id,
            ':quit' => $quittance,
            ':date' => $c['date_effet'],
            ':montant' => $c['prime_ttc'],
            ':mode' => $modes_paiement[array_rand($modes_paiement)]
        ]);
        $nb_paiements++;
    }
}
echo "✓ $nb_paiements paiements créés\n\n";

// Résumé final
echo "=== RÉSUMÉ FINAL ===\n";
$stats = $db->query("SELECT 
    (SELECT COUNT(*) FROM clients) as clients,
    (SELECT COUNT(*) FROM vehicules) as vehicules,
    (SELECT COUNT(*) FROM contrats) as contrats,
    (SELECT COUNT(*) FROM sinistres) as sinistres,
    (SELECT COUNT(*) FROM paiements) as paiements,
    (SELECT SUM(prime_ttc) FROM contrats WHERE statut='actif') as primes
")->fetch(PDO::FETCH_ASSOC);

echo "Clients:  {$stats['clients']}\n";
echo "Véhicules: {$stats['vehicules']}\n";
echo "Contrats: {$stats['contrats']}\n";
echo "Sinistres: {$stats['sinistres']}\n";
echo "Paiements: {$stats['paiements']}\n";
echo "Primes actives: " . number_format($stats['primes'], 0, ',', ' ') . " FCFA\n";

echo "\n✅ Peuplement terminé avec succès !\n";
?>
