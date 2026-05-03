<?php
header('Content-Type: application/json');
require_once 'config.php';

// API pour mise à jour statut déclaration
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(isset($data['action'])) {
        switch($data['action']) {
            case 'update_statut':
                $sql = "UPDATE declarations_douane SET statut = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$data['statut'], $data['id']]);
                
                // Ajouter au tracking
                $sql2 = "INSERT INTO tracking (declaration_id, etape, description, user_id) 
                         VALUES (?, ?, ?, ?)";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([$data['id'], $data['statut'], "Statut mis à jour", $_SESSION['user_id']]);
                
                echo json_encode(['success' => true]);
                break;
                
            case 'get_stats':
                $stats = [];
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM declarations_douane");
                $stats['declarations'] = $stmt->fetch()['total'];
                
                $stmt = $pdo->query("SELECT SUM(montant_ttc) as ca FROM factures 
                                    WHERE MONTH(date_emission) = MONTH(CURRENT_DATE())");
                $stats['ca_mois'] = $stmt->fetch()['ca'] ?? 0;
                
                echo json_encode($stats);
                break;
        }
    }
}
?>
