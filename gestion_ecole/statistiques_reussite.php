<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

function getStatsReussite() {
    global $conn;
    $sql = "SELECT c.nom_class, 
            COUNT(e.id) as total_eleves,
            SUM(CASE WHEN b.statut_final = 'Admis' THEN 1 ELSE 0 END) as admis
            FROM classes c
            LEFT JOIN etudiants e ON c.id = e.classe_id
            LEFT JOIN bulletins b ON e.code_etudiant = b.code_etudiant
            GROUP BY c.id";
    return $conn->query($sql);
}
?>
