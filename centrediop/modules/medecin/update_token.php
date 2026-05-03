<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$patient_id = $_POST['patient_id'] ?? 0;

if (!$patient_id) {
    echo json_encode(['success' => false, 'error' => 'ID patient requis']);
    exit();
}

$result = updateTokenPatient($patient_id);
echo json_encode(['success' => $result]);
