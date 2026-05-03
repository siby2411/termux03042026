<?php
require_once 'config/database.php';

echo "=== TEST DU FLUX COMPLET PATIENT -> RENDEZ-VOUS -> PAIEMENT ===\n\n";

$db = new Database();
$conn = $db->getConnection();

// 1. Vérifier Dr. Fall
echo "1. Médecin Dr. Fall:\n";
$stmt = $conn->prepare("SELECT u.*, s.name as service_nom 
                        FROM users u
                        JOIN services s ON u.service_id = s.id
                        WHERE u.username = 'dr.fall'");
$stmt->execute();
$dr_fall = $stmt->fetch();

if ($dr_fall) {
    echo "   ✅ ID: {$dr_fall['id']} - Dr. {$dr_fall['prenom']} {$dr_fall['nom']}\n";
    echo "   Service: {$dr_fall['service_nom']} (ID: {$dr_fall['service_id']})\n\n";
} else {
    echo "   ❌ Dr. Fall non trouvé\n\n";
}

// 2. Vérifier les rendez-vous récents
echo "2. Derniers rendez-vous créés:\n";
$rdv = $conn->query("
    SELECT rv.id, rv.date_rdv, rv.heure_rdv, rv.statut,
           p.nom, p.prenom, p.code_patient_unique,
           s.name as service_nom
    FROM rendez_vous rv
    JOIN patients p ON rv.patient_id = p.id
    JOIN services s ON rv.service_id = s.id
    ORDER BY rv.id DESC
    LIMIT 5
")->fetchAll();

if (empty($rdv)) {
    echo "   Aucun rendez-vous trouvé\n";
} else {
    foreach ($rdv as $r) {
        echo "   📅 RDV #{$r['id']} - {$r['date_rdv']} {$r['heure_rdv']}\n";
        echo "      Patient: {$r['prenom']} {$r['nom']} (Code: {$r['code_patient_unique']})\n";
        echo "      Service: {$r['service_nom']} - Statut: {$r['statut']}\n\n";
    }
}

// 3. Vérifier les paiements
echo "3. Derniers paiements:\n";
$paiements = $conn->query("
    SELECT p.*, pat.nom, pat.prenom, pat.code_patient_unique
    FROM paiements p
    JOIN patients pat ON p.patient_id = pat.id
    ORDER BY p.id DESC
    LIMIT 5
")->fetchAll();

if (empty($paiements)) {
    echo "   Aucun paiement trouvé\n";
} else {
    foreach ($paiements as $p) {
        echo "   💰 Facture: {$p['numero_facture']} - {$p['montant_total']} FCFA\n";
        echo "      Patient: {$p['prenom']} {$p['nom']} (Code: {$p['code_patient_unique']})\n\n";
    }
}

echo "\n=== INSTRUCTIONS ===\n";
echo "1. Connectez-vous en tant que Dr. Fall:\n";
echo "   URL: http://localhost:8000/modules/auth/login.php\n";
echo "   Identifiants: dr.fall / pediatre123\n\n";
echo "2. Créez un nouveau patient avec rendez-vous:\n";
echo "   Allez dans 'Nouveau Patient et Rendez-vous'\n\n";
echo "3. Notez le code patient généré\n\n";
echo "4. Connectez-vous en tant que caissier:\n";
echo "   Identifiants: caissier1 / caissier123\n\n";
echo "5. Dans le dashboard caissier, recherchez le patient par:\n";
echo "   - Code patient\n";
echo "   - Nom/Prénom\n";
echo "   - Date du rendez-vous\n\n";
echo "6. Cliquez sur 'Payer' pour le rendez-vous correspondant\n";
?>
