<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

// Optimiser les tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$optimized = 0;

foreach ($tables as $table) {
    $pdo->exec("OPTIMIZE TABLE $table");
    $optimized++;
}

// Ajouter des index pour améliorer les performances
try {
    $pdo->exec("CREATE INDEX idx_consultations_date ON consultations(date_consultation)");
    $pdo->exec("CREATE INDEX idx_file_attente_statut ON file_attente(statut)");
    $pdo->exec("CREATE INDEX idx_paiements_date ON paiements(date_paiement)");
    $pdo->exec("CREATE INDEX idx_rendez_vous_date ON rendez_vous(date_rdv)");
} catch (Exception $e) {
    // Les index existent peut-être déjà
}

echo json_encode(['success' => true, 'optimized' => $optimized]);
?>
