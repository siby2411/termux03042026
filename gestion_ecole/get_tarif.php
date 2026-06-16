<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$etudiant_id = intval($_GET['etudiant_id']);
$sql = "SELECT c.montant_scolarite FROM classes c 
        JOIN etudiants e ON e.classe_id = c.id 
        WHERE e.id = $etudiant_id";
$res = $conn->query($sql);
$row = $res->fetch_assoc();

echo json_encode(['montant' => $row['montant_scolarite'] ?? 0]);
?>
