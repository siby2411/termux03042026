<?php
header('Content-Type: application/json');
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$code = isset($_GET['code']) ? $conn->real_escape_string($_GET['code']) : '';

// Requête mise à jour pour récupérer les deux montants
$sql = "SELECT c.montant_scolarite, c.montant_inscription
        FROM classes c
        JOIN etudiants e ON e.classe_id = c.id
        WHERE e.code_etudiant = '$code' LIMIT 1";

$res = $conn->query($sql);
$data = $res->fetch_assoc();

// Retourner un JSON contenant les deux montants, avec des valeurs par défaut à 0 si vide
echo json_encode($data ? $data : ["montant_scolarite" => 0, "montant_inscription" => 0]);
?>
