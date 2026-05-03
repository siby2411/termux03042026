<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    
    $id_piece = $_POST['id_piece'];
    $qte = intval($_POST['qte']);
    $etat = $_POST['etat'];
    $motif = $_POST['motif'];

    try {
        $db->beginTransaction();

        // 1. Enregistrer le retour
        $ins = $db->prepare("INSERT INTO RETOURS (id_piece, quantite_retournee, motif, etat_retour) VALUES (?, ?, ?, ?)");
        $ins->execute([$id_piece, $qte, $motif, $etat]);

        // 2. Si réintégré, on remonte le stock et on crée un mouvement
        if ($etat === 'Réintégré') {
            // Récupérer stock actuel
            $st = $db->prepare("SELECT stock_actuel FROM PIECES WHERE id_piece = ?");
            $st->execute([$id_piece]);
            $stock_avant = $st->fetchColumn();
            $stock_apres = $stock_avant + $qte;

            // Update PIECES
            $upd = $db->prepare("UPDATE PIECES SET stock_actuel = ? WHERE id_piece = ?");
            $upd->execute([$stock_apres, $id_piece]);

            // Historique mouvement
            $mouv = $db->prepare("INSERT INTO MOUVEMENTS_STOCK (id_piece, quantite_impact, type_mouvement, stock_avant_mouvement, stock_apres_mouvement) VALUES (?, ?, 'Retour', ?, ?)");
            $mouv->execute([$id_piece, $qte, $stock_avant, $stock_apres]);
        }

        $db->commit();
        header("Location: gestion_retours.php?success=1");
    } catch (Exception $e) {
        $db->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}
