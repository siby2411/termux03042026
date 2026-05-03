<?php
header('Content-Type: application/json');
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Statistiques mensuelles
$data = [];
for($i=1; $i<=6; $i++) {
    $query = "SELECT COUNT(*) as total FROM adherents WHERE MONTH(date_inscription) = :mois AND YEAR(date_inscription) = YEAR(CURRENT_DATE())";
    $stmt = $db->prepare($query);
    $stmt->execute([':mois' => $i]);
    $data['nouveaux_mois'][] = (int)$stmt->fetchColumn();
}

// Taux de rétention
$query = "SELECT COUNT(*) as actifs, (SELECT COUNT(*) FROM adherents) as total FROM adherents WHERE statut='actif'";
$stmt = $db->query($query);
$retention = $stmt->fetch(PDO::FETCH_ASSOC);
$data['taux_retention'] = round(($retention['actifs'] / $retention['total']) * 100, 1);

// Revenus par discipline
$query = "SELECT d.nom, SUM(p.montant) as total 
          FROM paiements p 
          JOIN inscriptions i ON p.inscription_id = i.id 
          JOIN disciplines d ON i.discipline_id = d.id 
          GROUP BY d.id ORDER BY total DESC LIMIT 5";
$data['revenus_disciplines'] = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>
