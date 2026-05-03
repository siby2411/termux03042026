<?php
header('Content-Type: application/json');
require_once '../../core/Auth.php';
require_once '../../core/Database.php';

// 1. Sécurité : Vérifier que le caissier est bien connecté
Auth::check();

// Récupération des données envoyées par le POS (JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Le panier est vide ou invalide.']);
    exit;
}

$db = Database::getInstance()->getConnection();

try {
    // Début de la transaction (Tout passe ou rien ne passe)
    $db->beginTransaction();

    // 2. Insertion dans la table VENTES
    // Note : On utilise 'paye' sans accent pour éviter les erreurs 1265
    $sqlVente = "INSERT INTO ventes (date_vente, montant_total, mode_paiement, utilisateur_id, statut) 
                 VALUES (NOW(), ?, ?, ?, 'paye')";
    
    $stmtVente = $db->prepare($sqlVente);
    $stmtVente->execute([
        $data['total'], 
        $data['paiement'], 
        $_SESSION['user_id'] // L'ID du caissier (Fatou, Amadou, etc.)
    ]);
    
    $venteId = $db->lastInsertId();

    // 3. Préparation des requêtes pour les lignes et le stock
    $stmtLigne = $db->prepare("INSERT INTO vente_lignes (vente_id, medicament_id, quantite, prix_unitaire, montant_ligne) VALUES (?, ?, ?, ?, ?)");
    $stmtStock = $db->prepare("UPDATE medicaments SET stock_actuel = stock_actuel - ? WHERE id = ? AND stock_actuel >= ?");

    foreach ($data['items'] as $item) {
        $montantLigne = $item['price'] * $item['qte'];

        // Insertion du détail de la vente
        $stmtLigne->execute([
            $venteId, 
            $item['id'], 
            $item['qte'], 
            $item['price'], 
            $montantLigne
        ]);

        // Mise à jour du stock physique
        // On vérifie que le stock est suffisant au moment de l'UPDATE
        $stmtStock->execute([$item['qte'], $item['id'], $item['qte']]);
        
        if ($stmtStock->rowCount() === 0) {
            throw new Exception("Stock insuffisant pour le produit : " . $item['name']);
        }
    }

    // Si tout est OK, on valide définitivement en base de données
    $db->commit();

    echo json_encode([
        'success' => true, 
        'vente_id' => $venteId,
        'message' => 'Vente enregistrée avec succès'
    ]);

} catch (Exception $e) {
    // En cas d'erreur (ex: rupture de stock), on annule tout l'historique de cette vente
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => "Erreur critique : " . $e->getMessage()
    ]);
}
