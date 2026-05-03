<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();

header('Content-Type: application/json');

if ($_GET['action'] === 'create') {
    try {
        $db = Database::getInstance(); // On utilise PDO directement pour la transaction
        $db->beginTransaction();

        // 1. Enregistrer l'entête de l'achat
        $total_ttc = 0;
        foreach($_POST['quantite'] as $i => $qte) {
            $total_ttc += ($qte * $_POST['prix_unitaire'][$i]);
        }

        $stmt = $db->prepare("INSERT INTO achats (fournisseur_id, date_achat, reference_facture, montant_ttc) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['fournisseur_id'],
            $_POST['date_achat'],
            $_POST['reference_facture'],
            $total_ttc
        ]);
        $achat_id = $db->lastInsertId();

        // 2. Enregistrer les lignes et mettre à jour le stock
        foreach($_POST['medicament_id'] as $i => $med_id) {
            $qte = (int)$_POST['quantite'][$i];
            $pu = (float)$_POST['prix_unitaire'][$i];

            // Insertion ligne achat
            $stmtL = $db->prepare("INSERT INTO achats_lignes (achat_id, medicament_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
            $stmtL->execute([$achat_id, $med_id, $qte, $pu]);

            // MISE À JOUR DU STOCK (L'étape clé pour vous Mr Siby)
            $stmtS = $db->prepare("UPDATE medicaments SET stock_actuel = stock_actuel + ? WHERE id = ?");
            $stmtS->execute([$qte, $med_id]);
        }

        $db->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        if(isset($db)) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
