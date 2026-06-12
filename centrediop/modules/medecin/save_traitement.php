<?php
require_once '../../config/database.php';
$db = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO traitements (patient_id, medicament, posologie, date_prescription) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_POST['patient_id'], $_POST['medicament'], $_POST['posologie']]);
    header('Location: /modules/medecin/consultation.php?id=' . $_POST['patient_id'] . '&msg=prescrit');
}
?>
