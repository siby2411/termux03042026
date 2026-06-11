<?php
require_once 'config/database.php';

$pdo = getPDO();

echo "📊 STATISTIQUES DU CENTRE\n";
echo "=========================\n\n";

// Total des consultations
$total_cons = $pdo->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
echo "Consultations totales: $total_cons\n";

// Consultations avec actes
$avec_actes = $pdo->query("
    SELECT COUNT(DISTINCT consultation_id) 
    FROM consultation_actes
")->fetchColumn();
echo "Consultations avec actes: $avec_actes\n";

// Consultations sans actes
$sans_actes = $total_cons - $avec_actes;
echo "Consultations sans actes: $sans_actes\n\n";

// Montant total des actes
$total_actes = $pdo->query("SELECT SUM(prix_applique) FROM consultation_actes")->fetchColumn();
echo "Montant total des actes: " . number_format($total_actes, 0, ',', ' ') . " FCFA\n";

// Total des paiements
$total_paiements = $pdo->query("SELECT SUM(montant_total) FROM paiements")->fetchColumn();
echo "Total des paiements: " . number_format($total_paiements, 0, ',', ' ') . " FCFA\n\n";

// Impayés
$impayes = $pdo->query("
    SELECT COUNT(DISTINCT c.id) 
    FROM consultations c
    LEFT JOIN paiements p ON c.id = p.consultation_id
    WHERE p.id IS NULL
")->fetchColumn();
echo "Consultations impayées: $impayes\n\n";

// Top 5 patients avec le plus de consultations
echo "TOP 5 PATIENTS:\n";
$top_patients = $pdo->query("
    SELECT p.prenom, p.nom, COUNT(c.id) as nb_cons
    FROM patients p
    JOIN consultations c ON p.id = c.patient_id
    GROUP BY p.id
    ORDER BY nb_cons DESC
    LIMIT 5
")->fetchAll();

foreach ($top_patients as $p) {
    echo "  - {$p['prenom']} {$p['nom']}: {$p['nb_cons']} consultations\n";
}

// Services les plus actifs
echo "\nTOP 5 SERVICES:\n";
$top_services = $pdo->query("
    SELECT s.name, COUNT(c.id) as nb_cons
    FROM services s
    JOIN consultations c ON s.id = c.service_id
    GROUP BY s.id
    ORDER BY nb_cons DESC
    LIMIT 5
")->fetchAll();

foreach ($top_services as $s) {
    echo "  - {$s['name']}: {$s['nb_cons']} consultations\n";
}
