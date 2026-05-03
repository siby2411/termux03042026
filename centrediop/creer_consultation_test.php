<?php
require_once 'config/database.php';

$pdo = getPDO();

// Récupérer l'ID du patient Mariama Gueye
$stmt = $pdo->prepare("SELECT id FROM patients WHERE prenom = 'Mariama' AND nom = 'Gueye'");
$stmt->execute();
$patient = $stmt->fetch();

if (!$patient) {
    die("Patient non trouvé");
}

// Récupérer l'ID du médecin
$stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'medecin' LIMIT 1");
$stmt->execute();
$medecin = $stmt->fetch();

if (!$medecin) {
    die("Aucun médecin trouvé");
}

try {
    $pdo->beginTransaction();
    
    // Créer une nouvelle consultation
    $numero = 'CONS-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare("
        INSERT INTO consultations (numero_consultation, patient_id, medecin_id, service_id, date_consultation, motif_consultation, diagnostic, statut)
        VALUES (?, ?, ?, 1, NOW(), 'Consultation de contrôle', 'Patient asymptomatique', 'terminee')
    ");
    $stmt->execute([$numero, $patient['id'], $medecin['id']]);
    
    $consultation_id = $pdo->lastInsertId();
    
    // Ajouter des actes
    $actes = [1, 5]; // Consultation générale + Pansement simple
    $total = 0;
    
    foreach ($actes as $acte_id) {
        $stmt = $pdo->prepare("SELECT prix_consultation, prix_traitement FROM actes_medicaux WHERE id = ?");
        $stmt->execute([$acte_id]);
        $acte = $stmt->fetch();
        
        $prix = $acte['prix_consultation'] ?: $acte['prix_traitement'];
        
        $stmt = $pdo->prepare("
            INSERT INTO consultation_actes (consultation_id, acte_id, prix_applique)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$consultation_id, $acte_id, $prix]);
        
        $total += $prix;
    }
    
    $pdo->commit();
    
    echo "✅ Nouvelle consultation créée avec succès!\n";
    echo "   Consultation #$consultation_id\n";
    echo "   Patient: Mariama Gueye\n";
    echo "   Montant total: " . number_format($total, 0, ',', ' ') . " FCFA\n";
    echo "   Date: " . date('d/m/Y H:i:s') . "\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
