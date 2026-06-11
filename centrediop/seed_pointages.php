<?php
require_once 'config/database.php';

$pdo = getPDO();

echo "🌱 PEUPLEMENT DES POINTAGES DU PERSONNEL\n";
echo "========================================\n\n";

// Récupérer tout le personnel actif
$personnel = $pdo->query("
    SELECT id, prenom, nom, role 
    FROM users 
    WHERE actif = 1 
    AND role IN ('medecin', 'sagefemme', 'caissier', 'pharmacien', 'admin')
")->fetchAll();

echo "Personnel trouvé: " . count($personnel) . " personnes\n\n";

// Générer des pointages pour les 30 derniers jours
$jours = 30;
$total_pointages = 0;

for ($i = 0; $i < $jours; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $est_weekend = date('N', strtotime($date)) >= 6; // 6 = samedi, 7 = dimanche
    
    echo "Jour " . ($i+1) . "/$jours: $date ";
    
    $pointages_jour = 0;
    
    foreach ($personnel as $p) {
        // 90% de présence en semaine, 20% le week-end
        $presence = $est_weekend ? (rand(1, 100) <= 20) : (rand(1, 100) <= 90);
        
        if ($presence) {
            // Heure d'arrivée variable selon le rôle
            if ($p['role'] == 'admin') {
                $heure_arrivee = rand(7, 9) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                $heure_depart = rand(17, 19) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
            } elseif ($p['role'] == 'medecin') {
                $heure_arrivee = rand(8, 10) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                $heure_depart = rand(18, 20) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
            } else {
                $heure_arrivee = rand(7, 9) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                $heure_depart = rand(16, 18) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
            }
            
            // Parfois, certains n'ont pas pointé leur départ
            $a_depart = rand(1, 100) <= 95; // 95% pointent le départ
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO pointages (user_id, date_pointage, heure_arrivee, heure_depart, statut)
                    VALUES (?, ?, ?, ?, 'present')
                    ON DUPLICATE KEY UPDATE
                        heure_arrivee = VALUES(heure_arrivee),
                        heure_depart = VALUES(heure_depart)
                ");
                
                $stmt->execute([
                    $p['id'],
                    $date,
                    $heure_arrivee,
                    $a_depart ? $heure_depart : null
                ]);
                
                $pointages_jour++;
                
            } catch (Exception $e) {
                // Ignorer les doublons
            }
        }
    }
    
    echo " - $pointages_jour pointages\n";
    $total_pointages += $pointages_jour;
}

echo "\n✅ Total des pointages générés: $total_pointages\n";

// Calculer les heures travaillées
echo "\n📊 MISE À JOUR DES HEURES TRAVAILLÉES\n";
echo "======================================\n\n";

$pointages_calc = $pdo->query("
    SELECT user_id, 
           SUM(TIMESTAMPDIFF(HOUR, heure_arrivee, heure_depart)) as heures
    FROM pointages
    WHERE heure_depart IS NOT NULL
    GROUP BY user_id
")->fetchAll();

$update = $pdo->prepare("UPDATE users SET heures_travaillees = ? WHERE id = ?");
$count = 0;

foreach ($pointages_calc as $p) {
    $update->execute([$p['heures'], $p['user_id']]);
    $count++;
}

echo "✅ Heures travaillées mises à jour pour $count utilisateurs\n";

// Statistiques finales
echo "\n📊 RÉCAPITULATIF DES POINTAGES\n";
echo "==============================\n";

$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_pointages,
        COUNT(DISTINCT user_id) as personnels_concernes,
        MIN(date_pointage) as premiere_date,
        MAX(date_pointage) as derniere_date,
        AVG(TIMESTAMPDIFF(HOUR, heure_arrivee, heure_depart)) as moyenne_heures
    FROM pointages
    WHERE heure_depart IS NOT NULL
")->fetch();

echo "Total pointages: " . $stats['total_pointages'] . "\n";
echo "Personnels concernés: " . $stats['personnels_concernes'] . "\n";
echo "Période: du " . $stats['premiere_date'] . " au " . $stats['derniere_date'] . "\n";
echo "Moyenne heures/jour: " . round($stats['moyenne_heures'], 1) . "h\n";

// Pointages d'aujourd'hui
$aujourdhui = $pdo->query("
    SELECT COUNT(*) as count 
    FROM pointages 
    WHERE date_pointage = CURDATE()
")->fetchColumn();

echo "\nPointages aujourd'hui: $aujourdhui\n";

// Détail par rôle
echo "\n📋 DÉTAIL PAR RÔLE\n";
echo "==================\n";

$roles = $pdo->query("
    SELECT 
        u.role,
        COUNT(p.id) as pointages,
        AVG(TIMESTAMPDIFF(HOUR, p.heure_arrivee, p.heure_depart)) as moyenne_heures
    FROM users u
    LEFT JOIN pointages p ON u.id = p.user_id
    WHERE u.role IN ('medecin', 'sagefemme', 'caissier', 'pharmacien', 'admin')
    GROUP BY u.role
    ORDER BY pointages DESC
")->fetchAll();

foreach ($roles as $r) {
    echo str_pad(ucfirst($r['role']) . ":", 12) . " " . $r['pointages'] . " pointages, " . round($r['moyenne_heures']) . "h en moyenne\n";
}
?>
