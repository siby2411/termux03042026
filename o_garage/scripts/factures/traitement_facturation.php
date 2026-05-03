<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->beginTransaction();
    try {
        $total_facture = 0;
        $id_client = $_POST['id_client'];

        // 1. Boucle sur les pièces vendues
        if(isset($_POST['pieces'])) {
            foreach($_POST['pieces'] as $key => $id_piece) {
                $qte = $_POST['qtes'][$key];
                
                // Récupérer le prix
                $p = $db->prepare("SELECT prix_vente FROM pieces_detachees WHERE id_piece = ?");
                $p->execute([$id_piece]);
                $prix = $p->fetchColumn();
                
                $total_facture += ($prix * $qte);

                // Déduire du stock
                $upd = $db->prepare("UPDATE pieces_detachees SET quantite_stock = quantite_stock - ? WHERE id_piece = ?");
                $upd->execute([$qte, $id_piece]);
            }
        }

        // 2. Enregistrer la facture
        $stmt = $db->prepare("INSERT INTO factures_pieces (id_client, total_vente, date_facture) VALUES (?, ?, NOW())");
        $stmt->execute([$id_client, $total_facture]);

        $db->commit();
        header('Location: liste_factures.php?success=1');
    } catch (Exception $e) {
        $db->rollBack();
        echo "Erreur : " . $e->getMessage();
    }
}
