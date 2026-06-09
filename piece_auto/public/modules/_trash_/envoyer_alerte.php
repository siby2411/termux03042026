<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Non authentifié']); exit; }

require_once dirname(__DIR__) . '/config/config.php';

$id = (int)$_GET['id'];

// Récupérer l'échéance
$stmt = $pdo->prepare("SELECT e.*, t.telephone, t.email FROM ECHEANCIERS e LEFT JOIN TIERS t ON e.tiers_id = t.id WHERE e.id = ?");
$stmt->execute([$id]);
$echeance = $stmt->fetch();

if(!$echeance) {
    echo json_encode(['error' => 'Échéance non trouvée']);
    exit;
}

// Simulation d'envoi d'alerte (à connecter à une vraie API SMS)
$message = "ALERTE OMEGA ERP : Échéance du " . date('d/m/Y', strtotime($echeance['date_echeance'])) . 
           " - Montant : " . number_format($echeance['montant'], 0, ',', ' ') . " FCFA - " . $echeance['libelle'];

// Enregistrement de l'alerte
$stmt2 = $pdo->prepare("INSERT INTO ALERTES_ECHEANCES (echeance_id, type_alerte, destinataire, message, date_envoi, statut) VALUES (?, 'NOTIFICATION', ?, ?, NOW(), 'ENVOYE')");
$stmt2->execute([$id, $_SESSION['email'], $message]);

// Mise à jour du compteur de relances
$update = $pdo->prepare("UPDATE ECHEANCIERS SET nb_relances = nb_relances + 1, date_relance = CURDATE(), statut = 'RELANCE' WHERE id = ?");
$update->execute([$id]);

echo json_encode(['success' => true, 'message' => 'Alerte envoyée à ' . ($echeance['email'] ?? $_SESSION['email'])]);
?>
