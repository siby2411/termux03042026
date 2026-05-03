<?php
// Créer le dossier qrcodes s'il n'existe pas
if (!is_dir(__DIR__ . '/qrcodes')) {
    mkdir(__DIR__ . '/qrcodes', 0777, true);
}

// Tentative d'inclusion de phpqrcode (si existant et fonctionnel)
$use_phpqrcode = false;
if (file_exists(__DIR__ . '/phpqrcode/qrlib.php')) {
    // Désactiver les erreurs pour éviter les warnings
    error_reporting(E_ERROR);
    require_once __DIR__ . '/phpqrcode/qrlib.php';
    if (function_exists('QRcode::png') || class_exists('QRcode')) {
        $use_phpqrcode = true;
    }
    error_reporting(E_ALL);
}

/**
 * Génère un QR code pour un colis
 */
function generer_qr_colis($numero_suivi) {
    global $use_phpqrcode;
    
    $url_suivi = "http://127.0.0.1:8000/suivi.php?numero=" . urlencode($numero_suivi);
    $chemin_qr = __DIR__ . "/qrcodes/colis_$numero_suivi.png";
    $chemin_web = "qrcodes/colis_$numero_suivi.png";
    
    if (file_exists($chemin_qr) && filesize($chemin_qr) > 0) {
        return $chemin_web;
    }
    
    // Méthode 1 : utiliser phpqrcode
    if ($use_phpqrcode && class_exists('QRcode')) {
        try {
            QRcode::png($url_suivi, $chemin_qr, QR_ECLEVEL_L, 10);
            if (file_exists($chemin_qr)) {
                return $chemin_web;
            }
        } catch (Exception $e) {
            // Fallback
        }
    }
    
    // Méthode 2 : API externe gratuite (fallback)
    $api_url = "https://quickchart.io/qr?text=" . urlencode($url_suivi) . "&size=200&margin=2";
    $qr_image = @file_get_contents($api_url);
    if ($qr_image !== false) {
        file_put_contents($chemin_qr, $qr_image);
        return $chemin_web;
    }
    
    // Méthode 3 : API de secours
    $api_url2 = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url_suivi);
    $qr_image2 = @file_get_contents($api_url2);
    if ($qr_image2 !== false) {
        file_put_contents($chemin_qr, $qr_image2);
        return $chemin_web;
    }
    
    return '';
}

/**
 * Génère un QR code simple pour un colis
 */
function generer_qr_simple($numero_suivi) {
    global $use_phpqrcode;
    
    $url_suivi = "http://127.0.0.1:8000/suivi.php?numero=" . urlencode($numero_suivi);
    $chemin_qr = __DIR__ . "/qrcodes/simple_$numero_suivi.png";
    $chemin_web = "qrcodes/simple_$numero_suivi.png";
    
    if (file_exists($chemin_qr) && filesize($chemin_qr) > 0) {
        return $chemin_web;
    }
    
    if ($use_phpqrcode && class_exists('QRcode')) {
        try {
            QRcode::png($url_suivi, $chemin_qr, QR_ECLEVEL_L, 6);
            if (file_exists($chemin_qr)) {
                return $chemin_web;
            }
        } catch (Exception $e) {}
    }
    
    $api_url = "https://quickchart.io/qr?text=" . urlencode($url_suivi) . "&size=150&margin=1";
    $qr_image = @file_get_contents($api_url);
    if ($qr_image !== false) {
        file_put_contents($chemin_qr, $qr_image);
        return $chemin_web;
    }
    
    return '';
}

/**
 * Génère un QR code pour un client
 */
function generer_qr_client($client_id, $telephone) {
    $url_contact = "http://127.0.0.1:8000/client.php?id=$client_id";
    $chemin_qr = __DIR__ . "/qrcodes/client_$client_id.png";
    $chemin_web = "qrcodes/client_$client_id.png";
    
    if (file_exists($chemin_qr) && filesize($chemin_qr) > 0) {
        return $chemin_web;
    }
    
    $api_url = "https://quickchart.io/qr?text=" . urlencode($url_contact) . "&size=150&margin=1";
    $qr_image = @file_get_contents($api_url);
    if ($qr_image !== false) {
        file_put_contents($chemin_qr, $qr_image);
        return $chemin_web;
    }
    
    return '';
}

// Si appel direct
if (isset($_GET['colis'])) {
    $numero = $_GET['colis'];
    $qr_path = generer_qr_colis($numero);
    if ($qr_path && file_exists(__DIR__ . '/' . $qr_path)) {
        header('Content-Type: image/png');
        readfile(__DIR__ . '/' . $qr_path);
        exit;
    }
    http_response_code(404);
    echo "QR Code non trouvé";
    exit;
}

if (isset($_GET['simple']) && isset($_GET['colis'])) {
    $numero = $_GET['colis'];
    $qr_path = generer_qr_simple($numero);
    if ($qr_path && file_exists(__DIR__ . '/' . $qr_path)) {
        header('Content-Type: image/png');
        readfile(__DIR__ . '/' . $qr_path);
        exit;
    }
    http_response_code(404);
    echo "QR Code non trouvé";
    exit;
}
?>
