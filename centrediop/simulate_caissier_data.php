<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "=== SIMULATION DE DONNÉES POUR LES CAISSIERS ===\n\n";

// Récupérer les IDs des caissiers
$caissiers = $db->query("SELECT id, prenom, nom FROM users WHERE role = 'caissier'")->fetchAll();

if (count($caissiers) < 2) {
    echo "❌ Il faut d'abord créer les deux caissiers\n";
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

// Liste des traitements
$traitements = [
    ['nom' => 'Consultation générale', 'prix' => 5000],
    ['nom' => 'Consultation pédiatrie', 'prix' => 7000],
    ['nom' => 'Consultation cardiologie', 'prix' => 10000],
    ['nom' => 'Échographie cardiaque', 'prix' => 35000],
    ['nom' => 'Électrocardiogramme', 'prix' => 15000],
    ['nom' => 'Biopsie cutanée', 'prix' => 20000],
    ['nom' => 'Fond d\'œil', 'prix' => 12000],
    ['nom' => 'Hémoglobine glyquée', 'prix' => 10000],
];

$modes = ['especes', 'carte', 'mobile', 'cheque'];

// Simuler des paiements pour chaque caissier
echo "Génération de paiements pour chaque caissier...\n";

foreach ($caissiers as $caissier) {
    $nb_paiements = rand(5, 15);
    $total_caissier = 0;
    
    echo "\nCaissier {$caissier['prenom']} {$caissier['nom']}:\n";
    
    for ($i = 0; $i < $nb_paiements; $i++) {
        $patient = $patients[array_rand($patients)];
        $traitement = $traitements[array_rand($traitements)];
        $mode = $modes[array_rand($modes)];
        
        // Date aléatoire dans les 30 derniers jours
        $date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days ' . rand(0, 23) . ' hours ' . rand(0, 59) . ' minutes'));
        
        $insert = $db->prepare("
            INSERT INTO paiements (
                patient_id, caissier_id, montant_total, mode_paiement,
                statut, description, date_paiement, created_at
            ) VALUES (?, ?, ?, ?, 'valide', ?, ?, ?)
        ");
        
        try {
            $insert->execute([
                $patient['id'],
                $caissier['id'],
                $traitement['prix'],
                $mode,
                $traitement['nom'],
                $date,
                $date
            ]);
            $total_caissier += $traitement['prix'];
            echo "  ✅ Paiement {$traitement['prix']} F - {$patient['prenom']} {$patient['nom']} ({$mode})\n";
        } catch (Exception $e) {
            echo "  ❌ Erreur: " . $e->getMessage() . "\n";
        }
    }
    
    echo "  Total pour {$caissier['prenom']}: " . number_format($total_caissier, 0, ',', ' ') . " F\n";
}

echo "\n=== VÉRIFICATION ===\n";
$verif = $db->query("
    SELECT 
        u.prenom, u.nom,
        COUNT(p.id) as nb_paiements,
        COALESCE(SUM(p.montant_total), 0) as total
    FROM users u
    LEFT JOIN paiements p ON u.id = p.caissier_id
    WHERE u.role = 'caissier'
    GROUP BY u.id
");

echo "\nRésultats par caissier:\n";
while ($row = $verif->fetch()) {
    echo "{$row['prenom']} {$row['nom']}: {$row['nb_paiements']} paiements - " . number_format($row['total'], 0, ',', ' ') . " F\n";
}

echo "\n✅ Simulation terminée!\n";
?>
