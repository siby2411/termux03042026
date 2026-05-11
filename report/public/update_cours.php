<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once dirname(__DIR__) . '/config/config.php';

$devise = $_POST['devise'];
$taux = (float)$_POST['taux'];
$pdo->prepare("INSERT INTO DEVISES (code, libelle, taux_fcfa, date_taux) VALUES (?, (SELECT libelle FROM DEVISES WHERE code = ? LIMIT 1), ?, CURDATE()) ON DUPLICATE KEY UPDATE taux_fcfa = ?")
    ->execute([$devise, $devise, $taux, $taux]);
header("Location: operations_etrangeres.php");
