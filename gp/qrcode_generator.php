<?php
require_once 'db_connect.php';
require_once 'generer_qr.php';

function generateColisQRCode($colis_id, $force_regenerate = false) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT numero_suivi, description, statut, poids_kg FROM colis WHERE id = ?");
    $stmt->execute([$colis_id]);
    $colis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$colis) {
        return ['success' => false, 'error' => 'Colis non trouvé'];
    }
    
    $qr_path = generer_qr_colis($colis['numero_suivi']);
    
    return [
        'success' => true,
        'filepath' => $qr_path,
        'filename' => 'colis_' . $colis['numero_suivi'] . '.png',
        'colis' => $colis
    ];
}

function generateSimpleQRCode($numero_suivi) {
    return generer_qr_simple($numero_suivi);
}

if (isset($_GET['colis_id'])) {
    $colis_id = (int)$_GET['colis_id'];
    $simple = isset($_GET['simple']);
    
    if ($simple) {
        $stmt = $pdo->prepare("SELECT numero_suivi FROM colis WHERE id = ?");
        $stmt->execute([$colis_id]);
        $colis = $stmt->fetch();
        if ($colis) {
            $qr_path = generer_qr_simple($colis['numero_suivi']);
            if ($qr_path && file_exists(__DIR__ . '/' . $qr_path)) {
                header('Content-Type: image/png');
                readfile(__DIR__ . '/' . $qr_path);
                exit;
            }
        }
    } else {
        $result = generateColisQRCode($colis_id);
        if ($result['success'] && $result['filepath'] && file_exists(__DIR__ . '/' . $result['filepath'])) {
            header('Content-Type: image/png');
            readfile(__DIR__ . '/' . $result['filepath']);
            exit;
        }
    }
    http_response_code(404);
    echo "QR Code non trouvé";
    exit;
}
?>
