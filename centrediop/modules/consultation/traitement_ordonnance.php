<?php
require_once '../../config/database.php';
$db = getPDO();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $db->prepare("INSERT INTO traitements (consultation_id, prescription, date_prescription) VALUES (?, ?, NOW())");
    $stmt->execute([$_POST['consultation_id'], $_POST['ordonnance']]);
    header("Location: liste.php");
}
