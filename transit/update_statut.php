<?php
require_once 'config.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(isset($data['id']) && isset($data['statut'])) {
        $sql = "UPDATE declarations_douane SET statut = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['statut'], $data['id']]);
        
        // Ajouter au tracking
        $sql2 = "INSERT INTO tracking (declaration_id, etape, description) VALUES (?, ?, ?)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$data['id'], $data['statut'], "Statut mis à jour vers " . $data['statut']]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    }
}
?>
