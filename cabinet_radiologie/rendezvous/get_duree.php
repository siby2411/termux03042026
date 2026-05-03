<?php
require_once '../includes/db.php';
$id = $_GET['id'] ?? 0;
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT duree_estimee FROM examens WHERE id = ?");
$stmt->execute([$id]);
echo json_encode(['duree' => $stmt->fetchColumn() ?: 0]);
