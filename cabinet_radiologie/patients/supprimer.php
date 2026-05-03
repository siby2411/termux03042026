<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
$stmt->execute([$id]);
$user_id = $stmt->fetchColumn();
try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM patients WHERE id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
    $pdo->commit();
} catch (PDOException $e) { $pdo->rollBack(); }
header('Location: liste.php');
exit;
