<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['nom'])) {
        echo json_encode(['success' => false, 'message' => 'Nom obligatoire']);
        exit;
    }

    try {
        Database::query(
            "INSERT INTO fournisseurs (nom, telephone, email, adresse, actif) VALUES (?, ?, ?, ?, 1)",
            [$data['nom'], $data['telephone'], $data['email'], $data['adresse']]
        );
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
