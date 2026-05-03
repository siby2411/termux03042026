<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pdo = getPDO();

echo "========================================\n";
echo "🏨 PEUPLEMENT DE L'HÔTEL OMEGA\n";
echo "========================================\n\n";

// ==============================================
// 1. VIDER LES TABLES EXISTANTES
// ==============================================
echo "🧹 Nettoyage des données existantes...\n";
$tables = ['recettes', 'recettes_journalieres', 'reservations', 'charges', 'paie', 'personnel', 'clients', 'chambres'];
foreach ($tables as $table) {
    $pdo->exec("DELETE FROM $table");
    $pdo->exec("ALTER TABLE $table AUTO_INCREMENT = 1");
}
echo "✓ Données nettoyées\n\n";

// ==============================================
// 2. CRÉATION DES CHAMBRES
// ==============================================
echo "📦 Création des chambres...\n";
$chambres = [
    ['101', 'simple', 25000, 1, 'Chambre simple avec lit 140cm, salle de bain privative, clim', 'TV, Wi-Fi, Clim'],
    ['102', 'simple', 25000, 1, 'Chambre simple avec lit 140cm, salle de bain privative, clim', 'TV, Wi-Fi, Clim'],
    ['103', 'simple', 25000, 1, 'Chambre simple avec vue sur jardin', 'TV, Wi-Fi, Clim, Balcon'],
    ['201', 'double', 45000, 2, 'Chambre double avec lit 160cm, salle de bain, balcon', 'TV, Wi-Fi, Clim, Mini-bar'],
    ['202', 'double', 45000, 2, 'Chambre double avec vue sur piscine', 'TV, Wi-Fi, Clim, Mini-bar'],
    ['203', 'double', 45000, 2, 'Chambre double familiale', 'TV, Wi-Fi, Clim, Canapé-lit'],
    ['301', 'suite', 85000, 4, 'Suite familiale avec salon, deux chambres', 'TV, Wi-Fi, Clim, Salon, Cuisine'],
    ['302', 'suite', 85000, 4, 'Suite avec jacuzzi, vue mer', 'TV, Wi-Fi, Clim, Jacuzzi, Terrasse'],
    ['401', 'presidentielle', 150000, 6, 'Suite présidentielle avec terrasse, jacuzzi', 'TV, Wi-Fi, Clim, Jacuzzi, Salon, Salle à manger'],
];

foreach ($chambres as $c) {
    $stmt = $pdo->prepare("INSERT INTO chambres (numero, type, prix_nuit, capacite, description, equipements, statut) VALUES (?, ?, ?, ?, ?, ?, 'disponible')");
    $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5]]);
    echo "  ✓ Chambre {$c[0]} - {$c[1]} (" . formatMoney($c[2]) . "/nuit)\n";
}
echo "\n";

// ==============================================
// 3. CRÉATION DES CLIENTS (noms sénégalais)
// ==============================================
echo "👥 Création des clients...\n";
$clients = [
    ['DIOP', 'Mamadou', 'mamadou.diop@email.sn', '77 123 45 67', 'Dakar'],
    ['FALL', 'Aïcha', 'aicha.fall@email.sn', '78 234 56 78', 'Thiès'],
    ['NDIAYE', 'Oumar', 'oumar.ndiaye@email.sn', '76 345 67 89', 'Saint-Louis'],
    ['SOW', 'Fatou', 'fatou.sow@email.sn', '77 456 78 90', 'Dakar'],
    ['GUEYE', 'Ibrahima', 'ibrahima.gueye@email.sn', '78 567 89 01', 'Touba'],
    ['DIALLO', 'Aminata', 'aminata.diallo@email.sn', '76 678 90 12', 'Kaolack'],
    ['BA', 'Cheikh', 'cheikh.ba@email.sn', '77 789 01 23', 'Ziguinchor'],
    ['SARR', 'Ndeye', 'ndeye.sarr@email.sn', '78 890 12 34', 'Dakar'],
    ['CISSÉ', 'Moussa', 'moussa.cisse@email.sn', '76 901 23 45', 'Thiès'],
    ['KANE', 'Rokhaya', 'rokhaya.kane@email.sn', '77 012 34 56', 'Saint-Louis'],
];

foreach ($clients as $c) {
    $stmt = $pdo->prepare("INSERT INTO clients (nom, prenom, email, telephone, ville) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4]]);
    echo "  ✓ Client: {$c[1]} {$c[0]}\n";
}
echo "\n";

// ==============================================
// 4. CRÉATION DES RÉSERVATIONS
// ==============================================
echo "📅 Création des réservations...\n";

$clients_ids = $pdo->query("SELECT id FROM clients")->fetchAll();
$chambres_ids = $pdo->query("SELECT id, prix_nuit FROM chambres")->fetchAll();
$modes = ['Espèces', 'Carte', 'Virement', 'Mobile Money'];

$reservations_data = [
    ['2026-01-05', '2026-01-07', 1], ['2026-01-10', '2026-01-12', 2],
    ['2026-01-15', '2026-01-18', 3], ['2026-01-20', '2026-01-22', 1],
    ['2026-01-25', '2026-01-27', 4], ['2026-02-01', '2026-02-03', 5],
    ['2026-02-05', '2026-02-08', 6], ['2026-02-10', '2026-02-13', 2],
    ['2026-02-15', '2026-02-18', 3], ['2026-02-20', '2026-02-23', 4],
    ['2026-02-25', '2026-02-27', 7], ['2026-03-01', '2026-03-03', 8],
    ['2026-03-05', '2026-03-08', 9], ['2026-03-10', '2026-03-13', 1],
    ['2026-03-15', '2026-03-19', 10], ['2026-03-20', '2026-03-23', 5],
    ['2026-03-25', '2026-03-28', 2],
];

$reservation_count = 0;
foreach ($reservations_data as $data) {
    $client = $clients_ids[array_rand($clients_ids)];
    $chambre = $chambres_ids[array_rand($chambres_ids)];
    $date_arrivee = $data[0];
    $date_depart = $data[1];
    
    $arrivee = new DateTime($date_arrivee);
    $depart = new DateTime($date_depart);
    $nb_nuits = $arrivee->diff($depart)->days;
    $prix_total = $chambre['prix_nuit'] * $nb_nuits;
    $statut = $date_depart < date('Y-m-d') ? 'Terminée' : ($date_arrivee <= date('Y-m-d') ? 'En cours' : 'Confirmée');
    $mode = $modes[array_rand($modes)];
    
    $stmt = $pdo->prepare("INSERT INTO reservations (client_id, chambre_id, date_arrivee, date_depart, nb_nuits, prix_total, statut, mode_paiement) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$client['id'], $chambre['id'], $date_arrivee, $date_depart, $nb_nuits, $prix_total, $statut, $mode]);
    $reservation_count++;
}
echo "  ✓ $reservation_count réservations créées\n\n";

// ==============================================
// 5. CRÉATION DES RECETTES
// ==============================================
echo "💰 Création des recettes...\n";

$reservations = $pdo->query("SELECT id, prix_total, date_arrivee, mode_paiement FROM reservations")->fetchAll();
$recette_count = 0;
foreach ($reservations as $r) {
    $stmt = $pdo->prepare("INSERT INTO recettes (reservation_id, montant, date_recette, mode_paiement) VALUES (?, ?, ?, ?)");
    $stmt->execute([$r['id'], $r['prix_total'], $r['date_arrivee'], $r['mode_paiement']]);
    $recette_count++;
}
echo "  ✓ $recette_count recettes créées\n\n";

// ==============================================
// 6. CRÉATION DES CHARGES
// ==============================================
echo "📊 Création des charges...\n";

$charges_data = [
    ['Électricité', 125000, '2026-01-05', 'Électricité'],
    ['Eau', 45000, '2026-01-10', 'Eau'],
    ['Internet', 35000, '2026-01-15', 'Internet'],
    ['Électricité', 138000, '2026-02-05', 'Électricité'],
    ['Eau', 48000, '2026-02-10', 'Eau'],
    ['Internet', 35000, '2026-02-15', 'Internet'],
    ['Électricité', 142000, '2026-03-05', 'Électricité'],
    ['Eau', 52000, '2026-03-10', 'Eau'],
    ['Internet', 35000, '2026-03-15', 'Internet'],
    ['Fournitures', 28000, '2026-01-20', 'Fournitures'],
    ['Fournitures', 32000, '2026-02-20', 'Fournitures'],
    ['Fournitures', 35000, '2026-03-20', 'Fournitures'],
    ['Entretien', 75000, '2026-01-25', 'Entretien'],
    ['Entretien', 65000, '2026-02-25', 'Entretien'],
    ['Entretien', 85000, '2026-03-25', 'Entretien'],
];

foreach ($charges_data as $c) {
    $stmt = $pdo->prepare("INSERT INTO charges (libelle, montant, date_charge, categorie) VALUES (?, ?, ?, ?)");
    $stmt->execute([$c[0], $c[1], $c[2], $c[3]]);
}
echo "  ✓ " . count($charges_data) . " charges créées\n\n";

// ==============================================
// 7. CRÉATION DES RECETTES JOURNALIÈRES
// ==============================================
echo "📈 Calcul des recettes journalières...\n";

$recettes_journalieres = $pdo->query("
    SELECT 
        date_recette as date,
        SUM(montant) as montant_total,
        COUNT(*) as nb_reservations
    FROM recettes
    GROUP BY date_recette
")->fetchAll();

$total_chambres = $pdo->query("SELECT COUNT(*) FROM chambres")->fetchColumn();
foreach ($recettes_journalieres as $rj) {
    $taux_occupation = ($rj['nb_reservations'] / $total_chambres) * 100;
    $stmt = $pdo->prepare("INSERT INTO recettes_journalieres (date, montant_total, nb_reservations, taux_occupation) VALUES (?, ?, ?, ?)");
    $stmt->execute([$rj['date'], $rj['montant_total'], $rj['nb_reservations'], $taux_occupation]);
}
echo "  ✓ " . count($recettes_journalieres) . " entrées journalières\n\n";

// ==============================================
// 8. CRÉATION DU PERSONNEL
// ==============================================
echo "👔 Création du personnel...\n";

$personnel_data = [
    ['SARR', 'Papa', '77 111 22 33', 'Réceptionniste', 150000, '2023-01-15'],
    ['GUEYE', 'Mariama', '78 222 33 44', 'Femme de ménage', 100000, '2023-02-01'],
    ['DIALLO', 'Ibrahima', '76 333 44 55', 'Garde', 120000, '2023-03-10'],
    ['NDIAYE', 'Aminata', '77 444 55 66', 'Gouvernante', 180000, '2022-11-20'],
];

foreach ($personnel_data as $p) {
    $stmt = $pdo->prepare("INSERT INTO personnel (nom, prenom, telephone, poste, salaire_base, date_embauche) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5]]);
    echo "  ✓ {$p[1]} {$p[0]} - {$p[3]}\n";
}
echo "\n";

// ==============================================
// 9. CRÉATION DES FICHES DE PAIE
// ==============================================
echo "📑 Création des fiches de paie...\n";

$personnel = $pdo->query("SELECT id, salaire_base FROM personnel")->fetchAll();
$paie_count = 0;
$mois_actuel = date('m');

foreach ($personnel as $p) {
    for ($m = 1; $m <= $mois_actuel; $m++) {
        $prime = rand(20000, 80000);
        $deduction = rand(15000, 35000);
        $salaire_net = $p['salaire_base'] + $prime - $deduction;
        $paye = ($m < $mois_actuel) ? 1 : (rand(0, 1));
        
        $stmt = $pdo->prepare("INSERT INTO paie (personnel_id, mois, annee, salaire_brut, prime, deduction, salaire_net, paye) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$p['id'], $m, 2026, $p['salaire_base'], $prime, $deduction, $salaire_net, $paye]);
        $paie_count++;
    }
}
echo "  ✓ $paie_count fiches de paie créées\n\n";

// ==============================================
// 10. RÉCAPITULATIF FINAL
// ==============================================
echo "========================================\n";
echo "✅ PEUPLEMENT TERMINÉ AVEC SUCCÈS !\n";
echo "========================================\n";
echo "\n📊 RÉCAPITULATIF:\n";
echo "🏨 Chambres: " . $pdo->query("SELECT COUNT(*) FROM chambres")->fetchColumn() . "\n";
echo "👥 Clients: " . $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn() . "\n";
echo "📅 Réservations: " . $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn() . "\n";
echo "💰 Recettes totales: " . formatMoney($pdo->query("SELECT SUM(montant) FROM recettes")->fetchColumn()) . "\n";
echo "📊 Charges totales: " . formatMoney($pdo->query("SELECT SUM(montant) FROM charges")->fetchColumn()) . "\n";
echo "📈 Taux d'occupation moyen: " . round($pdo->query("SELECT AVG(taux_occupation) FROM recettes_journalieres")->fetchColumn(), 1) . "%\n";
echo "👔 Personnel: " . $pdo->query("SELECT COUNT(*) FROM personnel")->fetchColumn() . "\n";
echo "📑 Fiches de paie: " . $pdo->query("SELECT COUNT(*) FROM paie")->fetchColumn() . "\n";
echo "\n🔑 Accès au dashboard: http://127.0.0.1:8081/statistiques/index.php\n";
