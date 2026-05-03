<?php
include_once 'includes/functions.php';
include 'includes/db.php';
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $pdo->beginTransaction();

        // 1. Récupérer les infos du devis
        $stmt = $pdo->prepare("SELECT d.*, c.nom as client_nom FROM devis d JOIN clients c ON d.client_id = c.id WHERE d.id = ? AND d.statut = 'en_attente'");
        $stmt->execute([$id]);
        $devis = $stmt->fetch();

        if ($devis) {
            // 2. Vérifier stock du produit
            $p = $pdo->prepare("SELECT stock_actuel, designation FROM produits WHERE id = ? FOR UPDATE");
            $p->execute([$devis['produit_id']]);
            $prod = $p->fetch();

            if ($prod['stock_actuel'] >= $devis['quantite']) {
                // 3. Créer la commande
                $ins = $pdo->prepare("INSERT INTO commandes (client_nom, total_ht, etat, date_commande) VALUES (?, ?, 'validee', NOW())");
                $ins->execute([$devis['client_nom'], $devis['total_ht']]);

                // 4. Déduire stock et Log
                $newStock = $prod['stock_actuel'] - $devis['quantite'];
                $pdo->prepare("UPDATE produits SET stock_actuel = ? WHERE id = ?")->execute([$newStock, $devis['produit_id']]);
                verifierAlerteStock($pdo, $devis['produit_id']);
                $pdo->prepare("INSERT INTO stock_logs (produit_id, quantite, type) VALUES (?, ?, 'sortie')")->execute([$devis['produit_id'], $devis['quantite']]);

                // 5. Marquer devis comme accepté
                $pdo->prepare("UPDATE devis SET statut = 'accepte' WHERE id = ?")->execute([$id]);

                logAction($pdo, 'CONVERSION_DEVIS', 'Devis #' . $id . ' transformé en commande');
                $pdo->commit();
                header("Location: liste_devis.php?success=Devis_transformé_en_commande");
            } else {
                $pdo->rollBack();
                header("Location: liste_devis.php?error=Stock_insuffisant_pour_".$prod['designation']);
            }
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}
