<?php
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$q = $_GET['q'] ?? '';
if(strlen($q) >= 2) {
    $query = "SELECT e.id_eleve, CONCAT(e.nom_eleve, ' ', e.prenom_eleve) as nom_complet, 
                     p.telephone, b.immatriculation
              FROM eleves e
              JOIN parents p ON e.id_parent = p.id_parent
              LEFT JOIN affectations a ON e.id_eleve = a.id_eleve
              LEFT JOIN bus b ON a.id_bus = b.id_bus
              WHERE e.nom_eleve LIKE ? OR e.prenom_eleve LIKE ? OR b.immatriculation LIKE ?
              LIMIT 10";
    $stmt = $db->prepare($query);
    $param = "%$q%";
    $stmt->execute([$param, $param, $param]);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='search-result p-2 border-bottom' onclick='selectEleve({$row['id_eleve']}, \"{$row['nom_complet']}\", \"{$row['telephone']}\")'>";
        echo "<strong>{$row['nom_complet']}</strong><br>";
        echo "<small>Tél: {$row['telephone']} | Bus: {$row['immatriculation']}</small>";
        echo "</div>";
    }
}
?>
