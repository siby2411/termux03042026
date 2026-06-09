<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_piece'])) {
    try {
        $db->beginTransaction();
        // Insérer votre logique SQL ici (celle que vous aviez)
        $db->commit();
        header("Location: gestion_ventes.php?status=success");
    } catch (Exception $e) {
        $db->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}
?>
