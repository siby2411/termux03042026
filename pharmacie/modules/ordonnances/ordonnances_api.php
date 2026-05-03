<?php
header('Content-Type: application/json');
require_once '../../core/Database.php';
require_once '../../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_GET['action'] === 'create') {
    try {
        // Préparation des données (on gère les champs vides)
        $num = $data['numero_ordonnance'] ?? 'ORD-' . time();
        $nature = $data['nature'] ?? 'ordinaire';
        $medecin_id = !empty($data['medecin_id']) ? $data['medecin_id'] : null;
        $client_id = !empty($data['client_id']) ? $data['client_id'] : null;
        $conseils = $data['conseils_pharmacien'] ?? '';

        Database::execute(
            "INSERT INTO ordonnances (numero_ordonnance, nature, medecin_id, client_id, conseils_pharmacien, statut) 
             VALUES (?, ?, ?, ?, ?, 'en_attente')",
            [$num, $nature, $medecin_id, $client_id, $conseils]
        );

        echo json_encode(['success' => true, 'message' => 'Ordonnance enregistrée avec succès']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
}
