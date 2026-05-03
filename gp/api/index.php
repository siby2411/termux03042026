<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
require_once '../db_connect.php';
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$path = trim($path, '/');
$segments = explode('/', $path);
$response = [];
try {
    switch ($segments[0]) {
        case 'colis':
            if ($method === 'GET' && isset($segments[1])) {
                $numero = $segments[1];
                $stmt = $pdo->prepare("SELECT * FROM colis WHERE numero_suivi = ?");
                $stmt->execute([$numero]);
                $colis = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($colis) {
                    $stmt2 = $pdo->prepare("SELECT * FROM statuts_suivi WHERE colis_id = ? ORDER BY date_heure DESC");
                    $stmt2->execute([$colis['id']]);
                    $colis['historique'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                    $response = $colis;
                } else { http_response_code(404); $response = ['error' => 'Colis non trouvé']; }
            } elseif ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $num = uniqid('COL-');
                $stmt = $pdo->prepare("INSERT INTO colis (numero_suivi, client_expediteur_id, client_destinataire_id, description, poids_kg) VALUES (?,?,?,?,?)");
                $stmt->execute([$num, $data['expediteur_id'], $data['destinataire_id'], $data['description'], $data['poids_kg']]);
                $response = ['success' => true, 'numero_suivi' => $num];
            } else { http_response_code(405); $response = ['error' => 'Méthode non autorisée']; }
            break;
        case 'clients':
            if ($method === 'GET') {
                $type = $_GET['type'] ?? null;
                $sql = "SELECT id, nom, telephone, email, type FROM clients";
                if ($type) $sql .= " WHERE type = ? OR type = 'both'";
                $stmt = $pdo->prepare($sql);
                if ($type) $stmt->execute([$type]); else $stmt->execute();
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else { http_response_code(405); $response = ['error' => 'Méthode non autorisée']; }
            break;
        default: http_response_code(404); $response = ['error' => 'Endpoint non trouvé'];
    }
} catch (Exception $e) { http_response_code(500); $response = ['error' => $e->getMessage()]; }
echo json_encode($response, JSON_UNESCAPED_UNICODE);
