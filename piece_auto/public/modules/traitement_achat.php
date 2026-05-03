<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    try {
        $db->beginTransaction();

        foreach ($_POST['id_piece'] as $index => $id_piece) {
            $qte_neuve = intval($_POST['qte'][$index]);
            $prix_neuf = floatval($_POST['prix_achat'][$index]);

            // 1. Récupérer l'état actuel
            $stmt = $db->prepare("SELECT stock_actuel, cump FROM PIECES WHERE id_piece = ?");
            $stmt->execute([$id_piece]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);

            $stock_ancien = $old['stock_actuel'];
            $cump_ancien = $old['cump'];

            // 2. Calcul du nouveau CUMP
            $nouveau_stock = $stock_ancien + $qte_neuve;
            $nouveau_cump = (($stock_ancien * $cump_ancien) + ($qte_neuve * $prix_neuf)) / $nouveau_stock;

            // 3. Mise à jour PIECES
            $upd = $db->prepare("UPDATE PIECES SET stock_actuel = ?, cump = ?, prix_achat = ? WHERE id_piece = ?");
            $upd->execute([$nouveau_stock, $nouveau_cump, $prix_neuf, $id_piece]);

            // 4. Historique Mouvement
            $mouv = $db->prepare("INSERT INTO MOUVEMENTS_STOCK (id_piece, quantite_impact, type_mouvement, stock_avant_mouvement, stock_apres_mouvement, prix_unitaire) VALUES (?, ?, 'Achat', ?, ?, ?)");
            $mouv->execute([$id_piece, $qte_neuve, $stock_ancien, $nouveau_stock, $prix_neuf]);
        }

        $db->commit();
        header("Location: gestion_pieces.php?msg=Stock_Mis_A_Jour");
    } catch (Exception $e) {
        $db->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}
