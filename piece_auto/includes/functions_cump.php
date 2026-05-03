<?php
/**
 * Calcule et met à jour le CUMP après une réception fournisseur
 * Algorithme : (Stock Actuel * CUMP Ancien + Qté Reçue * Prix Achat) / Nouveau Stock
 */
function recalculerCUMP($db, $id_piece, $qte_recue, $prix_achat_unitaire) {
    // 1. Récupérer l'état actuel de la pièce
    $stmt = $db->prepare("SELECT stock_actuel, cump FROM PIECES WHERE id_piece = :id");
    $stmt->execute([':id' => $id_piece]);
    $piece = $stmt->fetch(PDO::FETCH_ASSOC);

    $stock_avant = $piece['stock_actuel'] ?? 0;
    $cump_avant = $piece['cump'] ?? 0;

    // 2. Calcul du nouveau CUMP
    $nouveau_stock = $stock_avant + $qte_recue;
    if ($nouveau_stock > 0) {
        $nouveau_cump = (($stock_avant * $cump_avant) + ($qte_recue * $prix_achat_unitaire)) / $nouveau_stock;
    } else {
        $nouveau_cump = $prix_achat_unitaire;
    }

    // 3. Mise à jour de la fiche pièce
    $upd = $db->prepare("UPDATE PIECES SET cump = :cump, stock_actuel = :stock WHERE id_piece = :id");
    $upd->execute([':cump' => $nouveau_cump, ':stock' => $nouveau_stock, ':id' => $id_piece]);

    // 4. Historisation du mouvement pour audit
    $log = $db->prepare("INSERT INTO HISTORIQUE_CUMP (id_piece, ancien_cump, nouveau_cump, qte_mouvement, prix_achat_mouvement) VALUES (?, ?, ?, ?, ?)");
    $log->execute([$id_piece, $cump_avant, $nouveau_cump, $qte_recue, $prix_achat_unitaire]);
    
    return $nouveau_cump;
}
?>
