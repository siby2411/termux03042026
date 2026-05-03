<?php
require_once 'includes/db.php';

$pdo = getPDO();

echo "=== PEUPLEMENT DE LA BASE AVEC DONNÉES SÉNÉGALAISES ===\n";

// ==============================================
// 1. EFFACER LES DONNÉES EXISTANTES
// ==============================================
echo "Nettoyage des données existantes...\n";
$tables = ['paiements', 'factures', 'comptes_rendus', 'rendezvous', 'presences', 'manipulateurs', 'radiologues', 'patients', 'users'];
foreach ($tables as $table) {
    $pdo->exec("DELETE FROM $table");
    $pdo->exec("ALTER TABLE $table AUTO_INCREMENT = 1");
}
echo "✓ Données nettoyées\n";

// ==============================================
// 2. CRÉATION DES UTILISATEURS
// ==============================================
echo "\nCréation du personnel...\n";

// Admin
$pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, role, is_active, is_staff, is_superuser) VALUES ('admin', ?, 'admin@cabinet.sn', 'Admin', 'System', 'admin', 1, 1, 1)")
    ->execute([password_hash('admin123', PASSWORD_DEFAULT)]);

// Radiologues
$radiologues_data = [
    ['first' => 'Mamadou', 'last' => 'Diop', 'specialite' => 'Radiologie Générale', 'phone' => '77 123 45 67', 'email' => 'mamadou.diop@cabinet.sn'],
    ['first' => 'Aïcha', 'last' => 'Fall', 'specialite' => 'IRM & Scanner', 'phone' => '78 234 56 78', 'email' => 'aicha.fall@cabinet.sn'],
    ['first' => 'Oumar', 'last' => 'Sow', 'specialite' => 'Mammographie', 'phone' => '76 345 67 89', 'email' => 'oumar.sow@cabinet.sn'],
];

foreach ($radiologues_data as $r) {
    $username = strtolower($r['first'] . '.' . $r['last']);
    $pwd = password_hash('radio123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'radiologue')")
        ->execute([$username, $pwd, $r['email'], $r['first'], $r['last'], $r['phone']]);
    $userId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO radiologues (user_id, specialite, numero_ordre, actif) VALUES (?, ?, ?, 1)")
        ->execute([$userId, $r['specialite'], 'ORD-' . rand(1000, 9999)]);
    echo "✓ Radiologue: Dr. {$r['first']} {$r['last']}\n";
}

// Manipulateurs
$manipulateurs_data = [
    ['first' => 'Papa', 'last' => 'Sarr', 'qualification' => 'Technicien Supérieur', 'phone' => '77 111 22 33', 'email' => 'papa.sarr@cabinet.sn'],
    ['first' => 'Mariama', 'last' => 'Gueye', 'qualification' => 'Manipulateur IRM', 'phone' => '78 222 33 44', 'email' => 'mariama.gueye@cabinet.sn'],
    ['first' => 'Ibrahima', 'last' => 'Diouf', 'qualification' => 'Manipulateur Scanner', 'phone' => '76 333 44 55', 'email' => 'ibrahima.diouf@cabinet.sn'],
];

foreach ($manipulateurs_data as $m) {
    $username = strtolower($m['first'] . '.' . $m['last']);
    $pwd = password_hash('manip123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'manipulateur')")
        ->execute([$username, $pwd, $m['email'], $m['first'], $m['last'], $m['phone']]);
    $userId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO manipulateurs (user_id, qualification, numero_licence, actif) VALUES (?, ?, ?, 1)")
        ->execute([$userId, $m['qualification'], 'LIC-' . rand(1000, 9999)]);
    echo "✓ Manipulateur: {$m['first']} {$m['last']}\n";
}

// ==============================================
// 3. CRÉATION DES PATIENTS
// ==============================================
echo "\nCréation des patients...\n";

$patients_data = [
    ['first' => 'Abdoulaye', 'last' => 'Diallo', 'phone' => '77 111 22 33', 'email' => 'abdoulaye.diallo@email.sn', 'naissance' => '1985-03-15', 'assurance' => 'IPM'],
    ['first' => 'Maimouna', 'last' => 'Ndiaye', 'phone' => '78 222 33 44', 'email' => 'maimouna.ndiaye@email.sn', 'naissance' => '1990-07-22', 'assurance' => 'CSS'],
    ['first' => 'Serigne', 'last' => 'Fall', 'phone' => '76 333 44 55', 'email' => 'serigne.fall@email.sn', 'naissance' => '1978-11-05', 'assurance' => 'SUNU'],
    ['first' => 'Awa', 'last' => 'Dieng', 'phone' => '70 444 55 66', 'email' => 'awa.dieng@email.sn', 'naissance' => '1982-09-18', 'assurance' => 'Privée'],
    ['first' => 'Bamba', 'last' => 'Sow', 'phone' => '77 555 66 77', 'email' => 'bamba.sow@email.sn', 'naissance' => '1965-05-30', 'assurance' => 'IPM'],
];

foreach ($patients_data as $p) {
    $username = strtolower($p['first'] . '.' . $p['last']);
    $pwd = password_hash('patient123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'patient')")
        ->execute([$username, $pwd, $p['email'], $p['first'], $p['last'], $p['phone']]);
    $userId = $pdo->lastInsertId();
    
    $last_code = $pdo->query("SELECT code_patient FROM patients ORDER BY id DESC LIMIT 1")->fetchColumn();
    $num = $last_code ? (int)substr($last_code, -4) + 1 : 1;
    $code_patient = "PAT-" . date('Y') . "-" . sprintf("%04d", $num);
    
    $pdo->prepare("INSERT INTO patients (user_id, code_patient, date_naissance, groupe_sanguin, assurance) VALUES (?, ?, ?, ?, ?)")
        ->execute([$userId, $code_patient, $p['naissance'], rand(0,1) ? 'O+' : 'A+', $p['assurance']]);
    echo "✓ Patient: {$p['first']} {$p['last']} ($code_patient)\n";
}

// ==============================================
// 4. CRÉATION DES RENDEZ-VOUS
// ==============================================
echo "\nCréation des rendez-vous...\n";

$patients = $pdo->query("SELECT id FROM patients")->fetchAll();
$examens = $pdo->query("SELECT id, tarif FROM examens WHERE actif = 1")->fetchAll();
$radiologues = $pdo->query("SELECT id FROM radiologues WHERE actif = 1")->fetchAll();
$manipulateurs = $pdo->query("SELECT id FROM manipulateurs WHERE actif = 1")->fetchAll();
$equipements = $pdo->query("SELECT id FROM equipements")->fetchAll();

$statuts = ['programme', 'confirme', 'termine', 'termine', 'termine'];
$rdv_count = 0;

for ($i = 0; $i < 50; $i++) {
    $patient = $patients[array_rand($patients)];
    $examen = $examens[array_rand($examens)];
    $radiologue = $radiologues[array_rand($radiologues)];
    $manipulateur = $manipulateurs[array_rand($manipulateurs)];
    $equipement = $equipements[array_rand($equipements)];
    
    $date = date('Y-m-d', strtotime('-' . rand(0, 60) . ' days'));
    $heure = rand(8, 17);
    $minute = [0, 15, 30, 45][rand(0, 3)];
    $heure_debut = sprintf("%02d:%02d:00", $heure, $minute);
    $heure_fin = sprintf("%02d:%02d:00", $heure + 1, $minute);
    $statut = $statuts[array_rand($statuts)];
    
    $pdo->prepare("INSERT INTO rendezvous (patient_id, examen_id, radiologue_id, manipulateur_id, equipement_id, date, heure_debut, heure_fin, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$patient['id'], $examen['id'], $radiologue['id'], $manipulateur['id'], $equipement['id'], $date, $heure_debut, $heure_fin, $statut]);
    $rdv_id = $pdo->lastInsertId();
    $rdv_count++;
    
    // Créer facture pour les rendez-vous terminés
    if ($statut == 'termine') {
        $total_ttc = $examen['tarif'];
        $montant_assurance = rand(0, 1) ? round($total_ttc * 0.7) : 0;
        $pdo->prepare("INSERT INTO factures (patient_id, rendezvous_id, total_ht, total_ttc, montant_assurance, montant_patient, reglee) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$patient['id'], $rdv_id, $total_ttc, $total_ttc, $montant_assurance, $total_ttc - $montant_assurance, rand(0, 1)]);
        
        // Créer compte rendu
        if (rand(0, 100) < 70) {
            $conclusions = [
                "Examen radiologique sans anomalie notable.",
                "Présence d'une image compatible avec... À corréler cliniquement.",
                "Résultats dans les limites de la normale.",
            ];
            $pdo->prepare("INSERT INTO comptes_rendus (rendezvous_id, radiologue_id, indication, technique, resultats, conclusion, signe) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$rdv_id, $radiologue['id'], 'Indication clinique', 'Technique standard', 'Résultats détaillés', $conclusions[array_rand($conclusions)], rand(0, 1)]);
        }
    }
    
    if ($rdv_count % 20 == 0) echo "  $rdv_count rendez-vous créés...\n";
}
echo "✓ $rdv_count rendez-vous créés\n";

// ==============================================
// 5. CRÉATION DES PAIEMENTS
// ==============================================
echo "\nCréation des paiements...\n";
$factures = $pdo->query("SELECT id, total_ttc FROM factures WHERE reglee = 0")->fetchAll();
$modes = ['especes', 'carte', 'virement', 'mobile_money'];
$paiement_count = 0;

foreach ($factures as $f) {
    if (rand(0, 100) < 60) {
        $montant = $f['total_ttc'];
        $pdo->prepare("INSERT INTO paiements (facture_id, montant, mode) VALUES (?, ?, ?)")
            ->execute([$f['id'], $montant, $modes[array_rand($modes)]]);
        $pdo->prepare("UPDATE factures SET reglee = 1 WHERE id = ?")->execute([$f['id']]);
        $paiement_count++;
    }
}
echo "✓ $paiement_count paiements créés\n";

// ==============================================
// 6. RÉCAPITULATIF FINAL
// ==============================================
echo "\n========================================\n";
echo "PEUPLEMENT TERMINÉ AVEC SUCCÈS !\n";
echo "========================================\n";
echo "📊 RÉCAPITULATIF:\n";
echo "👥 Patients: " . $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn() . "\n";
echo "👨‍⚕️ Radiologues: " . $pdo->query("SELECT COUNT(*) FROM radiologues")->fetchColumn() . "\n";
echo "🔧 Manipulateurs: " . $pdo->query("SELECT COUNT(*) FROM manipulateurs")->fetchColumn() . "\n";
echo "📋 Examens: " . $pdo->query("SELECT COUNT(*) FROM examens")->fetchColumn() . "\n";
echo "📅 Rendez-vous: " . $pdo->query("SELECT COUNT(*) FROM rendezvous")->fetchColumn() . "\n";
echo "📄 Comptes rendus: " . $pdo->query("SELECT COUNT(*) FROM comptes_rendus")->fetchColumn() . "\n";
echo "💰 Factures: " . $pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn() . "\n";
echo "💵 Paiements: " . $pdo->query("SELECT COUNT(*) FROM paiements")->fetchColumn() . "\n";
echo "\n🔑 Identifiants de connexion:\n";
echo "   Admin: admin / admin123\n";
echo "   Radiologue: mamadou.diop / radio123\n";
echo "   Manipulateur: papa.sarr / manip123\n";
echo "   Patient: abdoulaye.diallo / patient123\n";
