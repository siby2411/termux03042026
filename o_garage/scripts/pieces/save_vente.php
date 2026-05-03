<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pieces'])) {
    try {
        $db->beginTransaction();

        // 1. Création de l'entête de vente (Facture globale)
        $stmtVente = $db->prepare("INSERT INTO ventes (date_vente, total_ht) VALUES (NOW(), 0)");
        $stmtVente->execute();
        $idVente = $db->lastInsertId();

        $totalGlobal = 0;

        // 2. Boucle sur chaque pièce envoyée dans le panier
        foreach ($_POST['pieces'] as $index => $idPiece) {
            $quantite = (int)$_POST['qtes'][$index];
            
            // Récupérer le prix actuel de la pièce
            $stP = $db->prepare("SELECT prix_vente, nom_piece FROM pieces_detachees WHERE id_piece = ?");
            $stP->execute([$idPiece]);
            $piece = $stP->fetch();

            if ($piece) {
                $sousTotal = $piece['prix_vente'] * $quantite;
                $totalGlobal += $sousTotal;

                // Ajouter à la table de détails des ventes
                $stDetail = $db->prepare("INSERT INTO vente_details (id_vente, id_piece, quantite, prix_unitaire, sous_total) VALUES (?, ?, ?, ?, ?)");
                $stDetail->execute([$idVente, $idPiece, $quantite, $piece['prix_vente'], $sousTotal]);

                // 3. Mise à jour du stock (Décrémentation)
                $stStock = $db->prepare("UPDATE pieces_detachees SET stock_actuel = stock_actuel - ? WHERE id_piece = ?");
                $stStock->execute([$quantite, $idPiece]);
            }
        }

        // 4. Mise à jour du total final sur la facture
        $stFinal = $db->prepare("UPDATE ventes SET total_ht = ? WHERE id_vente = ?");
        $stFinal->execute([$totalGlobal, $idVente]);

        $db->commit();
        
        // Redirection vers la facture avec succès
        header("Location: ../../index.php?status=success&msg=Vente_enregistree&id=" . $idVente);

    } catch (Exception $e) {
        $db->rollBack();
        die("Erreur lors de la validation : " . $e->getMessage());
    }
}
