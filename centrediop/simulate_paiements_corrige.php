<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "=== SIMULATION DE PAIEMENTS POUR LES CAISSIERS ===\n\n";

// Récupérer les caissiers
$caissiers = $db->query("SELECT id, prenom, nom FROM users WHERE role = 'caissier'")->fetchAll();
if (count($caissiers) < 2) {
    echo "❌ Moins de 2 caissiers trouvés\n";
    exit;
}

echo "Caissiers trouvés:\n";
foreach ($caissiers as $c) {
    echo "  - {$c['prenom']} {$c['nom']} (ID: {$c['id']})\n";
}
echo "\n";

// Récupérer les patients
$patients = $db->query("SELECT id, prenom, nom FROM patients")->fetchAll();
if (empty($patients)) {
    echo "❌ Aucun patient trouvé\n";
    exit;
}

echo "Patients disponibles: " . count($patients) . "\n\n";

// Fonction pour générer un numéro de facture unique
function generateFactureNumber($db) {
    $prefix = 'FACT-' . date('Ymd');
    $stmt = $db->prepare("SELECT COUNT(*) FROM paiements WHERE numero_facture LIKE ?");
    $stmt->execute([$prefix . '%']);
    $count = $stmt->fetchColumn() + 1;
    return $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

// Liste des services/traitements pour les observations
$services = [
    ['nom' => 'Consultation générale', 'prix' => 5000],
    ['nom' => 'Consultation pédiatrie', 'prix' => 7000],
    ['nom' => 'Consultation cardiologie', 'prix' => 10000],
    ['nom' => 'Consultation dermatologie', 'prix' => 7000],
    ['nom' => 'Consultation ophtalmologie', 'prix' => 8000],
    ['nom' => 'Échographie cardiaque', 'prix' => 35000],
    ['nom' => 'Électrocardiogramme', 'prix' => 15000],
    ['nom' => 'Biopsie cutanée', 'prix' => 20000],
    ['nom' => 'Fond d\'œil', 'prix' => 12000],
    ['nom' => 'Tonométrie', 'prix' => 8000],
    ['nom' => 'Hémoglobine glyquée', 'prix' => 10000],
    ['nom' => 'Consultation de suivi', 'prix' => 5000],
];

$modes_paiement = ['especes', 'carte', 'cheque', 'mobile_money', 'assurance'];
$statuts = ['paye', 'partiel', 'impaye'];

// Préparer la requête d'insertion
$insert = $db->prepare("
    INSERT INTO paiements (
        numero_facture, patient_id, caissier_id, 
        montant_total, montant_paye, montant_restant,
        mode_paiement, statut, date_paiement, observations
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
");

$total_paiements = 0;

foreach ($caissiers as $caissier) {
    $nb_paiements = rand(8, 15);
    $total_caissier = 0;
    
    echo "\n📊 Génération pour {$caissier['prenom']} {$caissier['nom']}:\n";
    echo str_repeat('-', 50) . "\n";
    
    for ($i = 0; $i < $nb_paiements; $i++) {
        // Sélectionner un patient aléatoire
        $patient = $patients[array_rand($patients)];
        
        // Sélectionner un service aléatoire
        $service = $services[array_rand($services)];
        
        // Générer le numéro de facture
        $numero_facture = generateFactureNumber($db);
        
        // Déterminer le montant et le statut
        $montant_total = $service['prix'];
        
        // 80% des paiements sont complets, 15% partiels, 5% impayés
        $rand = rand(1, 100);
        if ($rand <= 80) {
            // Paiement complet
            $montant_paye = $montant_total;
            $montant_restant = 0;
            $statut = 'paye';
        } elseif ($rand <= 95) {
            // Paiement partiel (50% à 90% du montant)
            $pourcentage = rand(50, 90) / 100;
            $montant_paye = round($montant_total * $pourcentage, -2); // Arrondi à la centaine
            $montant_restant = $montant_total - $montant_paye;
            $statut = 'partiel';
        } else {
            // Impayé
            $montant_paye = 0;
            $montant_restant = $montant_total;
            $statut = 'impaye';
        }
        
        // Mode de paiement (plus de chances pour espèces)
        $mode = $modes_paiement[array_rand($modes_paiement)];
        
        // Observations
        $observations = $service['nom'] . " - " . date('d/m/Y');
        
        // Date aléatoire dans les 30 derniers jours (ou aujourd'hui)
        $date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days ' . rand(0, 23) . ' hours ' . rand(0, 59) . ' minutes'));
        
        try {
            $insert->execute([
                $numero_facture,
                $patient['id'],
                $caissier['id'],
                $montant_total,
                $montant_paye,
                $montant_restant,
                $mode,
                $statut,
                $observations
            ]);
            
            $total_caissier += $montant_paye;
            $total_paiements++;
            
            // Afficher un résumé
            $statut_icone = $statut == 'paye' ? '✅' : ($statut == 'partiel' ? '⚠️' : '❌');
            echo sprintf(
                "%s Facture %s: %s %s - %s %s F (%s)\n",
                $statut_icone,
                $numero_facture,
                $patient['prenom'],
                $patient['nom'],
                str_pad(number_format($montant_paye, 0, ',', ' '), 8, ' ', STR_PAD_LEFT),
                'F',
                $mode
            );
            
        } catch (Exception $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
        }
    }
    
    echo str_repeat('-', 50) . "\n";
    echo "💰 Total encaissé: " . number_format($total_caissier, 0, ',', ' ') . " F\n";
}

echo "\n=== RÉSUMÉ ===\n";
echo "✅ Total paiements insérés: $total_paiements\n";

// Afficher les statistiques par caissier
echo "\n=== STATISTIQUES PAR CAISSIER ===\n";
$stats = $db->query("
    SELECT 
        u.prenom, u.nom,
        COUNT(p.id) as nb_paiements,
        COALESCE(SUM(p.montant_paye), 0) as total_encaisse,
        COALESCE(SUM(p.montant_total), 0) as total_facture,
        SUM(CASE WHEN p.statut = 'paye' THEN 1 ELSE 0 END) as payes,
        SUM(CASE WHEN p.statut = 'partiel' THEN 1 ELSE 0 END) as partiels,
        SUM(CASE WHEN p.statut = 'impaye' THEN 1 ELSE 0 END) as impayes
    FROM users u
    LEFT JOIN paiements p ON u.id = p.caissier_id
    WHERE u.role = 'caissier'
    GROUP BY u.id
");

while ($row = $stats->fetch()) {
    echo "\n{$row['prenom']} {$row['nom']}:\n";
    echo "  📊 Paiements: {$row['nb_paiements']} (✅ {$row['payes']} payés, ⚠️ {$row['partiels']} partiels, ❌ {$row['impayes']} impayés)\n";
    echo "  💰 Encaissé: " . number_format($row['total_encaisse'], 0, ',', ' ') . " F\n";
    echo "  📝 Total facturé: " . number_format($row['total_facture'], 0, ',', ' ') . " F\n";
}

// Afficher les 10 dernières factures
echo "\n=== DERNIÈRES FACTURES ===\n";
$dernieres = $db->query("
    SELECT p.*, pat.prenom as patient_prenom, pat.nom as patient_nom, u.prenom as caissier_prenom
    FROM paiements p
    JOIN patients pat ON p.patient_id = pat.id
    JOIN users u ON p.caissier_id = u.id
    ORDER BY p.date_paiement DESC
    LIMIT 10
");

while ($f = $dernieres->fetch()) {
    echo "Facture {$f['numero_facture']}: {$f['patient_prenom']} {$f['patient_nom']} - ";
    echo number_format($f['montant_paye'], 0, ',', ' ') . " F ({$f['statut']}) - ";
    echo "Caissier: {$f['caissier_prenom']}\n";
}

echo "\n✅ Simulation terminée avec succès!\n";
?>
