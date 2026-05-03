

<?php
/**
 * Fichier : process_dotation_amortissement.php
 * Rôle : Calcule la dotation annuelle/mensuelle pour toutes les immobilisations
 * et génère les écritures comptables (D 68 / C 28).
 * Rigueur : Amortissement Linéaire selon le SYSCOHADA.
 */
require 'config.php';

header('Content-Type: application/json');

try {
    $pdo = connectDB($host, $dbName, $username, $password);
    $pdo->beginTransaction();

    $id_exercice_fk = $_POST['id_exercice_fk'] ?? null;
    $date_dotation = $_POST['date_dotation'] ?? date('Y-m-d');
    
    if (empty($id_exercice_fk)) {
        throw new Exception("Veuillez sélectionner l'exercice comptable.");
    }

    // 1. Sélectionner les immobilisations éligibles
    // Statut 'EN_COURS_AMORTISSEMENT' ET qui n'ont pas encore été dotées à cette date
    $stmt_immos = $pdo->prepare("SELECT i.*, 
                                 (SELECT COALESCE(SUM(montant_dotation), 0) FROM plan_amortissement WHERE id_immobilisation_fk = i.id_immobilisation) as cumul_precedent 
                                 FROM immobilisations i
                                 WHERE i.statut = 'EN_COURS_AMORTISSEMENT'
                                 AND i.date_acquisition <= ?");
    $stmt_immos->execute([$date_dotation]);
    $immos = $stmt_immos->fetchAll(PDO::FETCH_ASSOC);

    $total_dotation_generale = 0;
    
    foreach ($immos as $immo) {
        $vo = (float)$immo['valeur_origine'];
        $taux = (float)$immo['taux_amortissement'] / 100;
        $cumul_precedent = (float)$immo['cumul_precedent'];
        
        // Calcul de la dotation annuelle (base linéaire)
        $dotation_annuelle = $vo * $taux;
        $dotation_mensuelle = $dotation_annuelle / 12;
        
        // --- Simplification : on suppose ici une dotation mensuelle pour le module ---
        $montant_dotation = $dotation_mensuelle; 
        $new_cumul = $cumul_precedent + $montant_dotation;
        $new_vnc = $vo - $new_cumul;
        
        // Règle SYSCOHADA : La dotation ne doit pas dépasser la VO
        if ($new_cumul > $vo) {
            $montant_dotation = $vo - $cumul_precedent;
            $new_cumul = $vo;
            $new_vnc = 0;
            // Mettre à jour le statut si l'amortissement est terminé
            $stmt_update_statut = $pdo->prepare("UPDATE immobilisations SET statut = 'AMORTI_TOTALEMENT' WHERE id_immobilisation = ?");
            $stmt_update_statut->execute([$immo['id_immobilisation']]);
        }
        
        if ($montant_dotation > 0) {
            // 2. Génération de l'Écriture Comptable (D 68 / C 28)
            $libelle = "Dotation amortissement " . $immo['designation'] . " (Mois " . date('m/Y', strtotime($date_dotation)) . ")";
            $compte_charge = $immo['compte_immobilisation'] === '21100' ? '68120' : '68110'; // Incorporelle ou Corporelle
            
            // a) Débit du compte de charge (Classe 68)
            $stmt_debit = $pdo->prepare("INSERT INTO journal_comptable (code_journal, date_ecriture, numero_compte, libelle_ecriture, montant_debit, montant_credit, id_exercice_fk) 
                                         VALUES ('AMR', ?, ?, ?, ?, 0, ?)");
            $stmt_debit->execute([$date_dotation, $compte_charge, $libelle, $montant_dotation, $id_exercice_fk]);
            $ecriture_ref = $pdo->lastInsertId();

            // b) Crédit du compte d'amortissement (Classe 28)
            $stmt_credit = $pdo->prepare("INSERT INTO journal_comptable (code_journal, date_ecriture, numero_compte, libelle_ecriture, montant_debit, montant_credit, id_exercice_fk) 
                                          VALUES ('AMR', ?, ?, ?, 0, ?, ?)");
            $stmt_credit->execute([$date_dotation, $immo['compte_amortissement'], $libelle, $montant_dotation, $id_exercice_fk]);

            // 3. Enregistrement dans le Plan d'Amortissement
            $stmt_plan = $pdo->prepare("INSERT INTO plan_amortissement (id_immobilisation_fk, date_dotation, montant_dotation, cumul_amortissement, vnc_fin_periode, ecriture_comptable_ref) 
                                         VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_plan->execute([$immo['id_immobilisation'], $date_dotation, $montant_dotation, $new_cumul, $new_vnc, $ecriture_ref]);
            
            $total_dotation_generale += $montant_dotation;
        }
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success', 
        'message' => "Dotations générées avec succès pour " . count($immos) . " immobilisations. Montant total débité (68) : " . number_format($total_dotation_generale, 2, ',', ' ') . " XOF.",
        'total_dotation' => $total_dotation_generale
    ]);

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la génération des dotations : ' . $e->getMessage()]);
}
?>






