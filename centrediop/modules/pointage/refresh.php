<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

// Récupérer les statistiques à jour
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_pointes,
        SUM(CASE WHEN heure_depart IS NULL THEN 1 ELSE 0 END) as en_cours,
        SUM(CASE WHEN heure_depart IS NOT NULL THEN 1 ELSE 0 END) as termines
    FROM pointages
    WHERE date_pointage = CURDATE()
")->fetch();

$personnel_non_pointe = $pdo->query("
    SELECT COUNT(*) as count
    FROM users u
    WHERE u.actif = 1 
    AND u.role IN ('medecin', 'sagefemme', 'caissier', 'pharmacien')
    AND NOT EXISTS (
        SELECT 1 FROM pointages p 
        WHERE p.user_id = u.id AND p.date_pointage = CURDATE()
    )
")->fetchColumn();

header('Content-Type: application/json');
echo json_encode([
    'stats' => $stats,
    'non_pointes' => $personnel_non_pointe,
    'timestamp' => date('H:i:s')
]);
?>
