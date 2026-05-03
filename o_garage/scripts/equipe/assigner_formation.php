<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_p = $_POST['id_p'];
    $id_f = $_POST['id_f'];

    $stmt = $db->prepare("INSERT INTO plan_renovation (id_personnel, id_formation) VALUES (?, ?)");
    $stmt->execute([$id_p, $id_f]);

    // Rediriger vers le dashboard de supervision avec un message
    header('Location: /scripts/interventions/dashboard_ingenieur.php?training=assigned');
}
