<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

function generateMedicalRecord($pdo, $consultationId) {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name AS patient_name, p.age, p.gender, s.name AS service_name, d.name AS doctor_name
        FROM consultations c
        JOIN patients p ON c.patient_id = p.id
        JOIN services s ON c.service_id = s.id
        JOIN doctors d ON c.doctor_id = d.id
        WHERE c.id = ?
    ");
    $stmt->execute([$consultationId]);
    $consultation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$consultation) {
        return false;
    }

    // Générer un identifiant unique pour le dossier médical
    $recordId = 'DOS-' . strtoupper(uniqid());

    // Insérer le dossier médical
    $stmt = $pdo->prepare("
        INSERT INTO medical_records
        (record_id, consultation_id, patient_id, doctor_id, service_id, diagnosis, treatment, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([
        $recordId,
        $consultation['id'],
        $consultation['patient_id'],
        $consultation['doctor_id'],
        $consultation['service_id'],
        $consultation['diagnosis'],
        $consultation['treatment']
    ]);
}

// Exemple d'utilisation après une consultation
if (isset($_GET['consultation_id'])) {
    $consultationId = $_GET['consultation_id'];
    if (generateMedicalRecord($pdo, $consultationId)) {
        echo "Dossier médical généré avec succès.";
    } else {
        echo "Erreur lors de la génération du dossier médical.";
    }
}
