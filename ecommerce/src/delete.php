<?php
$pdo = new PDO("mysql:host=localhost;dbname=ecommerce;charset=utf8", "root", "");
$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM produits WHERE id=?");
$stmt->execute([$id]);
header('Location: list.php');
exit;
?>
