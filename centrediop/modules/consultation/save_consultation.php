<?php
require_once '../../includes/db.php';
$stmt = $conn->prepare("INSERT INTO consultations (patient_id, date_consultation, statut) VALUES (?, NOW(), 'En_cours')");
$stmt->bind_param("i", $_POST['patient_id']);
$stmt->execute();
header("Location: index.php");
?>
