<?php
require_once 'config/database.php';

$pdo = getPDO();

echo "🌱 PEUPLEMENT DE LA BASE DE DONNÉES AVEC DONNÉES DE TEST\n";
echo "===================================================\n\n";

// Ajouter des actes médicaux supplémentaires
echo "Ajout d'actes médicaux...\n";

$nouveaux_actes = [
    ['CONS-CARD', 'Consultation cardiologie', 'consultation', 15000, 10000, 8],
    ['CONS-OPHT', 'Consultation ophtalmologie', 'consultation', 12000, 8000, 9],
    ['CONS-DIAB', 'Consultation diabétologie', 'consultation', 13000, 9000, 10],
    ['CONS-NEURO', 'Consultation neurologie', 'consultation', 18000, 12000, 11],
    ['CONS-DERM', 'Consultation dermatologie', 'consultation', 11000, 7000, 12],
    ['ECHO-CARD', 'Échographie cardiaque', 'examen', 0, 35000, 8],
    ['ECG', 'Électrocardiogramme', 'examen', 0, 15000, 8],
    ['FOND-OEIL', 'Fond d\'oeil', 'examen', 0, 12000, 9],
    ['TONO', 'Tonométrie', 'examen', 0, 8000, 9],
    ['HEMOGLOBINE', 'Hémoglobine glyquée', 'examen', 0, 10000, 10],
    ['EEG', 'Électroencéphalogramme', 'examen', 0, 25000, 11],
    ['BIOPSIE', 'Biopsie cutanée', 'examen', 0, 20000, 12],
    ['VACCIN-CARD', 'Vaccin anti-grippal', 'vaccination', 0, 5000, null],
    ['VACCIN-COVID', 'Vaccin COVID-19', 'vaccination', 0, 0, null],
    ['SOIN-PLAIE', 'Pansement plaie complexe', 'soin', 0, 15000, null],
    ['SUTURE', 'Suture plaie', 'chirurgie', 0, 25000, null]
];

$stmt = $pdo->prepare("
    INSERT INTO actes_medicaux (code_acte, libelle, categorie, prix_consultation, prix_traitement, service_id)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        libelle = VALUES(libelle),
        prix_consultation = VALUES(prix_consultation),
        prix_traitement = VALUES(prix_traitement)
");

$count_actes = 0;
foreach ($nouveaux_actes as $a) {
    $stmt->execute($a);
    $count_actes++;
}
echo "✅ $count_actes actes ajoutés/mis à jour\n";

// Ajouter des patients supplémentaires
echo "\nAjout de patients supplémentaires...\n";

$nouveaux_patients = [
    ['Modou', 'Diop', '1985-03-15', 'M', '77 111 22 33', 'Dakar'],
    ['Aminata', 'Fall', '1990-07-22', 'F', '78 222 33 44', 'Pikine'],
    ['Ousmane', 'Sow', '1978-11-05', 'M', '76 333 44 55', 'Guediawaye'],
    ['Fatou', 'Ndiaye', '1982-09-18', 'F', '70 444 55 66', 'Rufisque'],
    ['Bamba', 'Ba', '1965-05-30', 'M', '77 555 66 77', 'Thiès'],
    ['Mariama', 'Gueye', '1995-02-10', 'F', '78 666 77 88', 'Dakar'],
    ['Cheikh', 'Diouf', '1972-12-25', 'M', '76 777 88 99', 'Pikine'],
    ['Ndeye', 'Thiam', '1988-08-08', 'F', '70 888 99 00', 'Guediawaye'],
    ['Papa', 'Kane', '1960-04-12', 'M', '77 999 00 11', 'Rufisque'],
    ['Rokhaya', 'Cissé', '1992-06-28', 'F', '78 101 12 13', 'Thiès']
];

$stmt_patient = $pdo->prepare("
    INSERT INTO patients (numero_patient, prenom, nom, date_naissance, sexe, telephone, adresse)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$count_patients = 0;
foreach ($nouveaux_patients as $p) {
    $numero = 'PAT-' . date('Ymd') . '-' . str_pad($count_patients + 6, 6, '0', STR_PAD_LEFT);
    try {
        $stmt_patient->execute([$numero, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5]]);
        $patient_id = $pdo->lastInsertId();
        
        // Créer dossier médical
        $pdo->prepare("INSERT INTO dossiers_medicaux (patient_id) VALUES (?)")->execute([$patient_id]);
        $count_patients++;
        echo "  ✅ Patient ajouté: {$p[0]} {$p[1]}\n";
    } catch (Exception $e) {
        echo "  ⚠️ Patient déjà existant: {$p[0]} {$p[1]}\n";
    }
}

// Ajouter des consultations avec paiements
echo "\nAjout de consultations et paiements...\n";

$patients = $pdo->query("SELECT id FROM patients")->fetchAll();
$medecins = $pdo->query("SELECT id FROM users WHERE role = 'medecin'")->fetchAll();
$actes_list = $pdo->query("SELECT id, prix_consultation, prix_traitement FROM actes_medicaux")->fetchAll();

$modes_paiement = ['especes', 'carte', 'cheque', 'mobile_money'];
$count_consultations = 0;
$count_paiements = 0;

// Créer des consultations pour les 7 derniers jours
for ($i = 0; $i < 50; $i++) {
    $jour = date('Y-m-d', strtotime("-" . rand(0, 7) . " days"));
    $heure = rand(8, 18) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
    
    $patient = $patients[array_rand($patients)];
    $medecin = $medecins[array_rand($medecins)];
    $acte = $actes_list[array_rand($actes_list)];
    $service_id = rand(1, 12);
    
    $numero_consult = 'CONS-' . date('Ymd') . '-' . str_pad($i+1, 4, '0', STR_PAD_LEFT);
    
    try {
        $pdo->beginTransaction();
        
        // Insérer consultation
        $stmt = $pdo->prepare("
            INSERT INTO consultations (numero_consultation, patient_id, medecin_id, service_id, date_consultation, motif_consultation, diagnostic, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'terminee')
        ");
        $motifs = ['Douleur thoracique', 'Fièvre', 'Maux de tête', 'Toux persistante', 'Suivi diabète', 'Contrôle annuel'];
        $diagnostics = ['Grippe', 'HTA', 'Diabète type 2', 'Infection urinaire', 'Paludisme', 'Rhinite allergique'];
        
        $stmt->execute([
            $numero_consult,
            $patient['id'],
            $medecin['id'],
            $service_id,
            "$jour $heure",
            $motifs[array_rand($motifs)],
            $diagnostics[array_rand($diagnostics)]
        ]);
        
        $consultation_id = $pdo->lastInsertId();
        
        // Ajouter acte à la consultation
        $prix = $acte['prix_consultation'] ?: $acte['prix_traitement'];
        $stmt = $pdo->prepare("
            INSERT INTO consultation_actes (consultation_id, acte_id, prix_applique)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$consultation_id, $acte['id'], $prix]);
        
        // Créer facture et paiement
        $numero_facture = 'FACT-' . date('Ymd') . '-' . str_pad($i+1, 4, '0', STR_PAD_LEFT);
        $montant = $prix;
        
        $stmt = $pdo->prepare("
            INSERT INTO paiements (numero_facture, patient_id, consultation_id, caissier_id, montant_total, montant_paye, statut, mode_paiement)
            VALUES (?, ?, ?, 2, ?, ?, 'paye', ?)
        ");
        $stmt->execute([
            $numero_facture,
            $patient['id'],
            $consultation_id,
            $montant,
            $montant,
            $modes_paiement[array_rand($modes_paiement)]
        ]);
        
        $pdo->commit();
        $count_consultations++;
        $count_paiements++;
        
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

echo "✅ $count_consultations consultations ajoutées\n";
echo "✅ $count_paiements paiements enregistrés\n";

// Mettre à jour les statistiques
echo "\n📊 RÉCAPITULATIF FINAL:\n";
$total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$total_consultations = $pdo->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
$total_paiements = $pdo->query("SELECT COUNT(*) FROM paiements")->fetchColumn();
$total_recettes = $pdo->query("SELECT SUM(montant_total) FROM paiements")->fetchColumn();

echo "Patients: $total_patients\n";
echo "Consultations: $total_consultations\n";
echo "Paiements: $total_paiements\n";
echo "Recettes totales: " . number_format($total_recettes, 0, ',', ' ') . " FCFA\n";
?>
