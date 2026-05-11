<?php
require_once dirname(__DIR__) . '/config/config.php';
$devise = $_GET['devise'];
$stmt = $pdo->prepare("SELECT taux_fcfa FROM DEVISES WHERE code = ? ORDER BY date_taux DESC LIMIT 1");
$stmt->execute([$devise]);
echo json_encode(['taux' => $stmt->fetchColumn()]);
