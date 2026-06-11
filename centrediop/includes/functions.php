<?php
/**
 * Fonctions utilitaires pour l'application
 */

// Alias pour getPDO (si besoin)
if (!function_exists('getPDO')) {
    function getPDO() {
        static $pdo = null;
        if ($pdo === null) {
            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            $pdo = $database->getConnection();
        }
        return $pdo;
    }
}

// Fonction pour nettoyer les entrées
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour générer un token unique
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Fonction pour formater une date
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

// Fonction pour formater un montant
function formatMoney($amount) {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

// Fonction pour vérifier si un utilisateur a un rôle
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fonction pour afficher un message flash
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Fonction pour générer un numéro de patient
function generatePatientNumber() {
    $year = date('Y');
    $month = date('m');
    $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return "PAT-{$year}{$month}-{$random}";
}

// Fonction pour générer un numéro de facture
function generateInvoiceNumber() {
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    return "FAC-{$year}{$month}{$day}-{$random}";
}

// Fonction pour calculer l'âge à partir de la date de naissance
function calculateAge($birthDate) {
    if (empty($birthDate)) return 'N/A';
    $birth = new DateTime($birthDate);
    $today = new DateTime('today');
    $age = $birth->diff($today)->y;
    return $age . ' ans';
}

// Fonction pour obtenir le nom du mois
function getMonthName($month) {
    $months = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];
    return $months[(int)$month] ?? $month;
}

// Fonction pour logger les actions
function logAction($action, $details = '') {
    if (!function_exists('getPDO')) return;
    
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Silently fail
    }
}
?>
