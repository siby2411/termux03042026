<?php
require_once '../../config/database.php';
$db = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantite_ajoutee'], $_POST['id'])) {
    $qte = (int)$_POST['quantite_ajoutee'];
    $id = (int)$_POST['id'];
    
    if ($qte > 0) {
        $stmt = $db->prepare("UPDATE stock SET quantite = quantite + ? WHERE id = ?");
        $stmt->execute([$qte, $id]);
        header('Location: /modules/pharmacie/index.php?status=success');
        exit;
    }
}
header('Location: /modules/pharmacie/index.php?status=error');
