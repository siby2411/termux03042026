<?php
require_once '../../includes/config.php';

$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS urgentCount
    FROM consultations c
    JOIN queue q ON c.patient_id = q.patient_id
    WHERE q.priority = 'senior' AND DATE(c.next_appointment) = ?
");
$stmt->execute([$today]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($result);
