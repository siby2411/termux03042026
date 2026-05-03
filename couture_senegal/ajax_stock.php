<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stock_id'])) {
    $id = $_POST['stock_id'];
    $metres = $_POST['quantite_sortie'];
    
    // Vérifier si le stock est suffisant
    $st = $pdo->prepare("SELECT quantite_restante, designation FROM stocks WHERE id = ?");
    $st->execute([$id]);
    $tissu = $st->fetch();

    if ($tissu['quantite_restante'] >= $metres) {
        $update = $pdo->prepare("UPDATE stocks SET quantite_restante = quantite_restante - ? WHERE id = ?");
        $update->execute([$metres, $id]);
        echo json_encode(['status' => 'success', 'msg' => $metres . "m retirés de " . $tissu['designation']]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => "Stock insuffisant !"]);
    }
    exit;
}
