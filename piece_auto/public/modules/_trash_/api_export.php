<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once dirname(__DIR__) . '/config/config.php';

$action = $_GET['action'] ?? '';
$api_key = $_GET['api_key'] ?? '';

// Clé API simple pour sécuriser (à remplacer par une vraie auth)
$valid_key = 'OMEGA2026_SYSCOHADA';

if ($api_key !== $valid_key) {
    http_response_code(401);
    echo json_encode(['error' => 'API Key invalide']);
    exit();
}

switch($action) {
    case 'balance':
        $data = $pdo->query("
            SELECT compte_debite_id as compte, SUM(montant) as total 
            FROM ECRITURES_COMPTABLES 
            GROUP BY compte_debite_id
        ")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;
        
    case 'resultat':
        $produits = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
        $charges = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
        echo json_encode([
            'status' => 'success',
            'produits' => $produits,
            'charges' => $charges,
            'resultat' => $produits - $charges
        ]);
        break;
        
    case 'dernieres_ecritures':
        $data = $pdo->query("SELECT * FROM ECRITURES_COMPTABLES ORDER BY date_ecriture DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;
        
    default:
        echo json_encode(['error' => 'Action non reconnue']);
}
?>
