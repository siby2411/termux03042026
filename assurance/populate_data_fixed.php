<?php
require_once 'config/db.php';

$db = getDB();

echo "=== PEUPLEMENT DE LA BASE DE DONNÉES OMEGA ASSURANCE ===\n\n";

// Nettoyer les anciennes données
echo "Nettoyage des anciennes données...\n";
$db->exec("SET FOREIGN_KEY_CHECKS=0");
$db->exec("TRUNCATE TABLE paiements");
$db->exec("TRUNCATE TABLE sinistres");
$db->exec("TRUNCATE TABLE contrat_garanties");
$db->exec("TRUNCATE TABLE contrats");
$db->exec("TRUNCATE TABLE vehicules");
$db->exec("TRUNCATE TABLE clients");
$db->exec("SET FOREIGN_KEY_CHECKS=1");
echo "✓ Anciennes données supprimées\n\n";

// 1. Insertion des clients (particuliers)
echo "Insertion des clients particuliers...\n";
$clients = [];
$noms = ['DIOP', 'NDIAYE', 'FALL', 'SOW', 'BA', 'GUEYE', 'DIAGNE', 'SARR', 'MBAYE', 'LY', 'SECK', 'THIAM', 'DIALLO', 'TOURE', 'KANE', 'NDOUR', 'CISSE', 'DRAME', 'SANE', 'NDIAYE'];
$prenoms = ['Amadou', 'Mariama', 'Ousmane', 'Aissatou', 'Ibrahima', 'Fatou', 'Mamadou', 'Aminata', 'Cheikh', 'Ndeye', 'Papa', 'Khadija', 'Moustapha', 'Bineta', 'Souleymane'];
$villes = ['Dakar', 'Thiès', 'Saint-Louis', 'Touba', 'Ziguinchor', 'Kaolack', 'Mbour', 'Rufisque', 'Diourbel', 'Louga', 'Tambacounda', 'Kolda'];

for($i = 1; $i <= 60; $i++) {
    $nom = $noms[array_rand($noms)];
    $prenom = $prenoms[array_rand($prenoms)];
    $telephone = '77' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
    $numero_client = 'CLT-' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT);
    $email = strtolower($prenom . '.' . $nom . rand(1, 999)) . '@gmail.com';
    $ville = $villes[array_rand($villes)];
    $date_naissance = date('Y-m-d', strtotime('-' . rand(18, 70) . ' years'));
    
    $sql = "INSERT INTO clients (numero_client, type_client, nom, prenom, email, telephone, ville, date_naissance, statut) 
            VALUES (:num, 'particulier', :nom, :prenom, :email, :tel, :ville, :date_naiss, 'actif')";
    $stmt = $db->prepare($sql);
    try {
        $stmt->execute([
            ':num' => $numero_client,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':tel' => $telephone,
            ':ville' => $ville,
            ':date_naiss' => $date_naissance
        ]);
        $clients[] = $db->lastInsertId();
    } catch(PDOException $e) {
        echo "Erreur insertion client: " . $e->getMessage() . "\n";
    }
    
    if($i % 10 == 0) echo "  $i clients particuliers créés\n";
}
echo "✓ " . count($clients) . " clients particuliers créés\n";

// Clients entreprises
echo "\nInsertion des clients entreprises...\n";
$entreprises = ['OMEGA SARL', 'SENEGAL EXPRESS', 'DAKAR TRANS', 'AFRIQUE LOGISTICS', 'SUNU GROUPE', 'CARGILL SN', 'TOTAL SENEGAL', 'ORANGE SENEGAL', 'SONATEL', 'SGBS', 'ECOBANK', 'AIR SENEGAL'];
$nb_entreprises = 30;

for($i = 1; $i <= $nb_entreprises; $i++) {
    $raison = $entreprises[array_rand($entreprises)] . ' ' . rand(1, 100);
    $telephone = '33' . str_pad(rand(800000, 999999), 6, '0', STR_PAD_LEFT);
    $numero_client = 'ENT-' . date('Y') . str_pad($i, 4, '0', STR_PAD_LEFT);
    $email = strtolower(str_replace(' ', '', $raison)) . '@entreprise.sn';
    $ville = $villes[array_rand($villes)];
    
    $sql = "INSERT INTO clients (numero_client, type_client, raison_sociale, email, telephone, ville, statut) 
            VALUES (:num, 'entreprise', :raison, :email, :tel, :ville, 'actif')";
    $stmt = $db->prepare($sql);
    try {
        $stmt->execute([
            ':num' => $numero_client,
            ':raison' => $raison,
            ':email' => $email,
            ':tel' => $telephone,
            ':ville' => $ville
        ]);
        $clients[] = $db->lastInsertId();
    } catch(PDOException $e) {
        echo "Erreur insertion entreprise: " . $e->getMessage() . "\n";
    }
}
echo "✓ " . $nb_entreprises . " clients entreprises créés\n";
echo "Total clients: " . count($clients) . "\n\n";

// 2. Insertion des véhicules
echo "Insertion des véhicules...\n";
$marques = ['Toyota', 'Renault', 'Peugeot', 'Hyundai', 'Kia', 'Suzuki', 'Nissan', 'Honda', 'Mitsubishi', 'Volkswagen', 'Mercedes', 'BMW', 'Audi'];
$modeles = ['Corolla', 'Clio', '208', 'i10', 'Picanto', 'Swift', 'Micra', 'Civic', 'Lancer', 'Golf', 'C220', 'Serie3', 'A4'];
$energies = ['Essence', 'Diesel', 'Electrique', 'Hybride'];
$couleurs = ['Blanc', 'Noir', 'Gris', 'Bleu', 'Rouge', 'Argent', 'Vert'];
$vehicules = [];

foreach($clients as $client_id) {
    // 1 à 2 véhicules par client
    $nb_vehicules = rand(1, 2);
    for($v = 0; $v < $nb_vehicules; $v++) {
        $marque = $marques[array_rand($marques)];
        $modele = $modeles[array_rand($modeles)];
        $immat = strtoupper(substr($marque, 0, 2)) . '-' . rand(100, 999) . '-' . strtoupper(substr($modele, 0, 2));
        $valeur = rand(5000000, 25000000);
        $couleur = $couleurs[array_rand($couleurs)];
        $annee = rand(2015, 2025);
        
        $sql = "INSERT INTO vehicules (immatriculation, marque, modele, annee_fabrication, energie, valeur_venale, couleur, proprietaire_id, statut) 
                VALUES (:immat, :marque, :modele, :annee, :energie, :valeur, :couleur, :proprietaire, 'actif')";
        $stmt = $db->prepare($sql);
        try {
            $stmt->execute([
                ':immat' => $immat,
                ':marque' => $marque,
                ':modele' => $modele,
                ':annee' => $annee,
                ':energie' => $energies[array_rand($energies)],
                ':valeur' => $valeur,
                ':couleur' => $couleur,
                ':proprietaire' => $client_id
            ]);
            $vehicules[] = $db->lastInsertId();
        } catch(PDOException $e) {
            // Ignorer les doublons d'immatriculation
        }
    }
}
echo "✓ " . count($vehicules) . " véhicules créés\n\n";

// 3. Insertion des contrats
echo "Insertion des contrats...\n";
$formules = ['Tiers', 'Tiers_Plus', 'Tous_Risques', 'Premium'];
$modes_paiement = ['Annuel', 'Semestriel', 'Trimestriel', 'Mensuel'];
$contrats = [];

for($i = 1; $i <= 120; $i++) {
    if(empty($vehicules)) break;
    
    $client_id = $clients[array_rand($clients)];
    $vehicule_id = $vehicules[array_rand($vehicules)];
    $formule = $formules[array_rand($formules)];
    
    // Calcul prime selon formule
    switch($formule) {
        case 'Tiers': $prime_base = rand(150000, 250000); break;
        case 'Tiers_Plus': $prime_base = rand(250000, 400000); break;
        case 'Tous_Risques': $prime_base = rand(400000, 600000); break;
        case 'Premium': $prime_base = rand(600000, 900000); break;
        default: $prime_base = 200000;
    }
    
    $taxe = $prime_base * 0.10; // 10% taxe
    $prime_ttc = $prime_base + $taxe;
    $date_effet = date('Y-m-d', strtotime('-' . rand(0, 365) . ' days'));
    $date_echeance = date('Y-m-d', strtotime($date_effet . ' + 1 year'));
    $numero_contrat = 'CON-' . date('Y') . str_pad($i, 6, '0', STR_PAD_LEFT);
    $statut = rand(1, 10) > 2 ? 'actif' : 'expire';
    
    $sql = "INSERT INTO contrats (numero_contrat, client_id, vehicule_id, formule, prime_nette, taxe, prime_ttc, mode_paiement, date_effet, date_echeance, statut, date_creation) 
            VALUES (:num, :client, :vehicule, :formule, :prime_nette, :taxe, :prime_ttc, :mode, :date_effet, :date_echeance, :statut, :date_creation)";
    $stmt = $db->prepare($sql);
    try {
        $stmt->execute([
            ':num' => $numero_contrat,
            ':client' => $client_id,
            ':vehicule' => $vehicule_id,
            ':formule' => $formule,
            ':prime_nette' => $prime_base,
            ':taxe' => $taxe,
            ':prime_ttc' => $prime_ttc,
            ':mode' => $modes_paiement[array_rand($modes_paiement)],
            ':date_effet' => $date_effet,
            ':date_echeance' => $date_echeance,
            ':statut' => $statut,
            ':date_creation' => $date_effet
        ]);
        $contrats[] = $db->lastInsertId();
    } catch(PDOException $e) {
        echo "Erreur contrat: " . $e->getMessage() . "\n";
    }
    
    if($i % 20 == 0) echo "  $i contrats créés\n";
}
echo "✓ " . count($contrats) . " contrats créés\n\n";

// 4. Insertion des sinistres
echo "Insertion des sinistres...\n";
$types_sinistre = ['Accident', 'Vol', 'Incendie', 'Bris_de_glace', 'Catastrophe_naturelle'];
$statuts_sinistre = ['declare', 'expertise', 'en_cours', 'indemnise', 'cloture'];
$nb_sinistres = 60;

for($i = 1; $i <= $nb_sinistres; $i++) {
    if(empty($contrats)) break;
    
    $contrat_id = $contrats[array_rand($contrats)];
    $type = $types_sinistre[array_rand($types_sinistre)];
    $statut = $statuts_sinistre[array_rand($statuts_sinistre)];
    $montant_estime = rand(500000, 5000000);
    $montant_indemnise = ($statut == 'indemnise' || $statut == 'cloture') ? $montant_estime * rand(60, 100)/100 : 0;
    
    $numero_sinistre = 'SIN-' . date('Y') . str_pad($i, 6, '0', STR_PAD_LEFT);
    $date_survenance = date('Y-m-d', strtotime('-' . rand(1, 180) . ' days'));
    $date_declaration = date('Y-m-d', strtotime($date_survenance . ' + ' . rand(1, 5) . ' days'));
    
    $sql = "INSERT INTO sinistres (numero_sinistre, contrat_id, date_survenance, date_declaration, type_sinistre, montant_estime, montant_indemnise, statut) 
            VALUES (:num, :contrat, :date_surv, :date_decl, :type, :montant_estime, :montant_indemn, :statut)";
    $stmt = $db->prepare($sql);
    try {
        $stmt->execute([
            ':num' => $numero_sinistre,
            ':contrat' => $contrat_id,
            ':date_surv' => $date_survenance,
            ':date_decl' => $date_declaration,
            ':type' => $type,
            ':montant_estime' => $montant_estime,
            ':montant_indemn' => $montant_indemnise,
            ':statut' => $statut
        ]);
    } catch(PDOException $e) {
        // Ignorer erreurs
    }
}
echo "✓ $nb_sinistres sinistres créés\n\n";

// 5. Insertion des paiements
echo "Insertion des paiements...\n";
$modes_reglement = ['Especes', 'Cheque', 'Virement', 'Orange_Money', 'Wave'];
$nb_paiements = 0;

foreach($contrats as $contrat_id) {
    // Récupérer les infos du contrat
    $stmt = $db->prepare("SELECT prime_ttc, date_effet FROM contrats WHERE id = :id");
    $stmt->execute([':id' => $contrat_id]);
    $contrat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($contrat) {
        $prime = $contrat['prime_ttc'];
        $date_effet = $contrat['date_effet'];
        
        // Premier paiement
        $numero_quittance = 'QUIT-' . date('Ymd') . '-' . $contrat_id . rand(100, 999);
        $sql = "INSERT INTO paiements (contrat_id, numero_quittance, date_paiement, montant, mode_reglement, statut) 
                VALUES (:contrat, :quittance, :date, :montant, :mode, 'valide')";
        $stmt = $db->prepare($sql);
        try {
            $stmt->execute([
                ':contrat' => $contrat_id,
                ':quittance' => $numero_quittance,
                ':date' => $date_effet,
                ':montant' => $prime,
                ':mode' => $modes_reglement[array_rand($modes_reglement)]
            ]);
            $nb_paiements++;
        } catch(PDOException $e) {}
    }
}
echo "✓ $nb_paiements paiements créés\n\n";

// Résumé final
echo "=== PEUPLEMENT TERMINÉ AVEC SUCCÈS ===\n";
echo "Résumé des données insérées:\n";
echo "----------------------------------------\n";
echo "Clients:          " . count($clients) . "\n";
echo "Véhicules:        " . count($vehicules) . "\n";
echo "Contrats:         " . count($contrats) . "\n";
echo "Sinistres:        $nb_sinistres\n";
echo "Paiements:        $nb_paiements\n";
echo "----------------------------------------\n";

// Vérification finale
echo "\nVérification en base de données:\n";
$verif = $db->query("SELECT 'Clients' as type, COUNT(*) as total FROM clients UNION SELECT 'Contrats', COUNT(*) FROM contrats UNION SELECT 'Sinistres', COUNT(*) FROM sinistres UNION SELECT 'Paiements', COUNT(*) FROM paiements");
while($row = $verif->fetch()) {
    echo "  - " . $row['type'] . ": " . $row['total'] . "\n";
}
?>
