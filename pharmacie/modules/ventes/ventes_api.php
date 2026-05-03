<?php
header('Content-Type: application/json');
require_once '../../core/Database.php';
require_once '../../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_GET['action'] === 'create') {
    try {
        Database::beginTransaction();

        // 1. Générer la référence (V-2026-XXXX)
        $ref = "V-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -4));

        // 2. Calculer les totaux
        $total_ht = 0;
        foreach ($data['lignes'] as $l) {
            $total_ht += $l['quantite'] * $l['prix_unitaire'];
        }
        $remise_val = ($total_ht * ($data['remise_pct'] ?? 0)) / 100;
        $net_a_payer = $total_ht - $remise_val;

        // 3. Insérer la vente
        Database::execute(
            "INSERT INTO ventes (reference, client_id, utilisateur_id, mode_paiement, montant_ht, montant_ttc, remise_pct, remise_montant, net_a_payer, statut) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'validee')",
            [
                $ref, $data['client_id'] ?: null, 1, $data['mode_paiement'], 
                $total_ht, $total_ht, $data['remise_pct'], $remise_val, $net_a_payer
            ]
        );
        $vente_id = Database::lastId();

        // 4. Insérer les lignes et déduire le stock
        foreach ($data['lignes'] as $l) {
            $montant_ligne = $l['quantite'] * $l['prix_unitaire'];
            Database::execute(
                "INSERT INTO vente_lignes (vente_id, medicament_id, quantite, prix_unitaire, montant_ligne) 
                 VALUES (?, ?, ?, ?, ?)",
                [$vente_id, $l['medicament_id'], $l['quantite'], $l['prix_unitaire'], $montant_ligne]
            );

            // Déduction du stock
            Database::execute(
                "UPDATE medicaments SET stock_actuel = stock_actuel - ? WHERE id = ?",
                [$l['quantite'], $l['medicament_id']]
            );
        }

        Database::commit();
        echo json_encode(['success' => true, 'reference' => $ref, 'id' => $vente_id]);

    } catch (Exception $e) {
        Database::rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
