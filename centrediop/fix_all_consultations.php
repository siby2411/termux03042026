<?php
require_once 'config/database.php';

$pdo = getPDO();

echo "🔍 RECHERCHE DES CONSULTATIONS SANS ACTES\n";
echo "==========================================\n\n";

// Trouver toutes les consultations sans actes
$stmt = $pdo->query("
    SELECT c.id, p.prenom, p.nom, c.date_consultation
    FROM consultations c
    JOIN patients p ON c.patient_id = p.id
    LEFT JOIN consultation_actes ca ON c.id = ca.consultation_id
    WHERE ca.id IS NULL
    ORDER BY c.date_consultation DESC
");

$sans_actes = $stmt->fetchAll();

echo "📊 Consultations sans actes trouvées: " . count($sans_actes) . "\n\n";

if (empty($sans_actes)) {
    echo "✅ Aucune consultation sans actes !\n";
    exit;
}

// Afficher les 5 premières
echo "Exemples:\n";
for ($i = 0; $i < min(5, count($sans_actes)); $i++) {
    $c = $sans_actes[$i];
    echo "  - ID {$c['id']}: {$c['prenom']} {$c['nom']} - " . date('d/m/Y', strtotime($c['date_consultation'])) . "\n";
}

echo "\n";

// Demander confirmation
echo "Voulez-vous ajouter un acte 'Consultation générale' (5000 FCFA) à toutes ces consultations ? (oui/non) ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if ($line == 'oui' || $line == 'o') {
    $pdo->beginTransaction();
    
    $count = 0;
    foreach ($sans_actes as $c) {
        // Ajouter l'acte 1 (Consultation générale) à 5000 FCFA
        $stmt = $pdo->prepare("
            INSERT INTO consultation_actes (consultation_id, acte_id, prix_applique)
            VALUES (?, 1, 5000)
        ");
        $stmt->execute([$c['id']]);
        $count++;
        
        if ($count % 10 == 0) {
            echo "  $count consultations traitées...\n";
        }
    }
    
    $pdo->commit();
    echo "\n✅ $count consultations mises à jour avec succès !\n";
    
} else {
    echo "❌ Opération annulée\n";
}

// Vérifier le résultat
$stmt = $pdo->query("
    SELECT COUNT(*) as reste
    FROM consultations c
    LEFT JOIN consultation_actes ca ON c.id = ca.consultation_id
    WHERE ca.id IS NULL
");
$reste = $stmt->fetchColumn();

echo "\n📊 Reste après correction: $reste consultations sans actes\n";
