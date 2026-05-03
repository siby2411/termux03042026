<?php
require_once '../../includes/classes/Database.php';
$dbObj = new Database();
$pdo = $dbObj->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_fiche = $_POST['id_fiche'];
        $id_prest = $_POST['id_prestation'];
        $remise = $_POST['remise'] ?? 0;

        // 1. Récupérer le tarif MO
        $st = $pdo->prepare("SELECT tarif_mo FROM prestations WHERE id_prestation = ?");
        $st->execute([$id_prest]);
        $tarif_mo = $st->fetchColumn();

        // 2. Calculer le total des pièces utilisées sur cette fiche
        $st_p = $pdo->prepare("SELECT SUM(prix_total) FROM pieces_utilisees WHERE id_fiche = ?");
        $st_p->execute([$id_fiche]);
        $total_pieces = $st_p->fetchColumn() ?? 0;

        $montant_final = ($tarif_mo + $total_pieces) - $remise;

        // 3. Insérer la facture
        $ins = $pdo->prepare("INSERT INTO factures_reparation (id_fiche, id_prestation, montant_mo, montant_pieces, montant_total, date_facture) VALUES (?, ?, ?, ?, ?, NOW())");
        $ins->execute([$id_fiche, $id_prest, $tarif_mo, $total_pieces, $montant_final]);

        // 4. Marquer la fiche comme facturée
        $upd = $pdo->prepare("UPDATE fiches_intervention SET statut = 'Facturé' WHERE id_fiche = ?");
        $upd->execute([$id_fiche]);

        header("Location: ../../index.php?status=success&msg=facture_generee");
        exit();

    } catch (Exception $e) { die($e->getMessage()); }
}
