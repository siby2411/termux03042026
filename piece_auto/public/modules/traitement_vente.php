<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_piece'])) {
    $database = new Database();
    $db = $database->getConnection();

    try {
        $db->beginTransaction();

        $total_final = floatval($_POST['total_final']);
        $total_cout_cump = 0;

        // 1. Création de la commande de vente
        $stmtVente = $db->prepare("INSERT INTO COMMANDE_VENTE (id_client, date_vente, total_commande, cout_total_cump, marge_brute) VALUES (?, NOW(), ?, 0, 0)");
        $stmtVente->execute([$_POST['id_client'], $total_final]);
        $id_vente = $db->lastInsertId();

        // 2. Préparation des requêtes pour les lignes et le stock
        $stmtLigne = $db->prepare("INSERT INTO LIGNE_VENTE (id_commande_vente, id_piece, quantite, prix_vente_unitaire, cout_total_ligne) VALUES (?, ?, ?, ?, ?)");
        $stmtGetPiece = $db->prepare("SELECT stock_actuel, cump FROM PIECES WHERE id_piece = ?");
        $stmtUpdateStock = $db->prepare("UPDATE PIECES SET stock_actuel = stock_actuel - ? WHERE id_piece = ?");
        $stmtMouv = $db->prepare("INSERT INTO MOUVEMENTS_STOCK (id_piece, quantite_impact, type_mouvement, stock_avant_mouvement, stock_apres_mouvement, prix_unitaire, source_id) VALUES (?, ?, 'Vente', ?, ?, ?, ?)");

        // 3. Boucle sur les articles du panier
        foreach ($_POST['id_piece'] as $index => $id_piece) {
            $qte_vendue = intval($_POST['qte'][$index]);
            $prix_u_vendu = floatval($_POST['prix'][$index]);

            // Récupérer les infos actuelles de la pièce (Stock et CUMP)
            $stmtGetPiece->execute([$id_piece]);
            $piece = $stmtGetPiece->fetch(PDO::FETCH_ASSOC);
            $stock_avant = $piece['stock_actuel'];
            $cump_unitaire = $piece['cump'] ?: 0;
            
            $cout_ligne_cump = $cump_unitaire * $qte_vendue;
            $total_cout_cump += $cout_ligne_cump;
            $stock_apres = $stock_avant - $qte_vendue;

            // A. Insérer la ligne de vente
            $stmtLigne->execute([$id_vente, $id_piece, $qte_vendue, $prix_u_vendu, ($prix_u_vendu * $qte_vendue)]);

            // B. Mettre à jour le stock physique
            $stmtUpdateStock->execute([$qte_vendue, $id_piece]);

            // C. Enregistrer le mouvement de stock pour l'historique
            $stmtMouv->execute([$id_piece, -$qte_vendue, $stock_avant, $stock_apres, $prix_u_vendu, $id_vente]);
        }

        // 4. Mise à jour de la marge réelle sur la facture
        $marge_brute = $total_final - $total_cout_cump;
        $stmtUpdateVente = $db->prepare("UPDATE COMMANDE_VENTE SET cout_total_cump = ?, marge_brute = ? WHERE id_commande_vente = ?");
        $stmtUpdateVente->execute([$total_cout_cump, $marge_brute, $id_vente]);

        $db->commit();
        header("Location: generate_invoice.php?id=" . $id_vente);
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        die("Erreur Fatale : " . $e->getMessage());
    }
}
