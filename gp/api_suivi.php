<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$numero = $_GET['numero'] ?? '';
if (empty($numero)) {
    echo json_encode(['error' => 'Numéro requis']);
    exit;
}
$stmt = $pdo->prepare("SELECT c.*, e.nom as expediteur, d.nom as destinataire FROM colis c LEFT JOIN clients e ON c.client_expediteur_id = e.id LEFT JOIN clients d ON c.client_destinataire_id = d.id WHERE c.numero_suivi = ?");
$stmt->execute([$numero]);
$colis = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$colis) {
    echo json_encode(['error' => 'Colis non trouvé']);
    exit;
}
$stmt2 = $pdo->prepare("SELECT * FROM statuts_suivi WHERE colis_id = ? ORDER BY date_heure DESC");
$stmt2->execute([$colis['id']]);
$colis['historique'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($colis);
?>
