<?php
require_once '../../includes/config.php';

if (!isCaissier()) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo->beginTransaction();
    
    // Générer numéro de facture
    $numero_facture = 'FACT-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Insérer la vente
    $stmt = $pdo->prepare("
        INSERT INTO ventes (numero_facture, client_id, utilisateur_id, montant_total, montant_paye, mode_paiement) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $total = array_sum(array_column($data['articles'], 'sous_total'));
    $client_id = !empty($data['client_id']) ? $data['client_id'] : null;
    
    $stmt->execute([
        $numero_facture,
        $client_id,
        $_SESSION['user_id'],
        $total,
        $data['montant_paye'],
        $data['mode_paiement']
    ]);
    
    $vente_id = $pdo->lastInsertId();
    
    // Insérer les lignes de vente et mettre à jour le stock
    foreach ($data['articles'] as $article) {
        $stmt = $pdo->prepare("
            INSERT INTO ventes_lignes (vente_id, livre_id, quantite, prix_unitaire, sous_total) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$vente_id, $article['id'], $article['quantite'], $article['prix'], $article['sous_total']]);
        
        // Mettre à jour le stock
        $stmt = $pdo->prepare("UPDATE livres SET quantite_stock = quantite_stock - ? WHERE id = ?");
        $stmt->execute([$article['quantite'], $article['id']]);
    }
    
    // Ajouter des points fidélité si client
    if ($client_id) {
        $points = floor($total / 1000); // 1 point par 1000 FCFA
        $stmt = $pdo->prepare("UPDATE clients SET points_fidelite = points_fidelite + ? WHERE id = ?");
        $stmt->execute([$points, $client_id]);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'facture_id' => $vente_id, 'numero' => $numero_facture]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
