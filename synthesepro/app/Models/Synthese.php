<?php
// app/Models/Synthese.php
require_once __DIR__ . '/Db.php';

class Synthese {
    private $pdo;

    public function __construct() {
        $db = Db::getInstance();
        $this->pdo = $db->getConnection();
    }

    /**
     * Étape 1 : Calcul du Grand Livre (Mouvements) et des Soldes (Balance)
     * Regroupe toutes les écritures pour générer la Balance des 4 colonnes (Mouvements & Soldes).
     * @param int $societeId ID de la société.
     * @param int $exercice Année de l'exercice.
     * @return bool
     */
    public function genererBalance(int $societeId, int $exercice): bool {
        
        // 1. Début de la transaction
        $this->pdo->beginTransaction();
        
        try {
            // A. Agrégation des écritures pour calculer les mouvements Débit/Crédit (Grand Livre)
            // On utilise une requête SQL complexe et efficace pour regrouper toutes les écritures
            // et les mettre à jour (ou les insérer) dans la table SYNTHESES_BALANCE.
            $sql_mouvements = "
                INSERT INTO SYNTHESES_BALANCE 
                    (societe_id, exercice, compte_id, mouvement_debit, mouvement_credit)
                SELECT
                    :societe_id AS societe_id,
                    :exercice AS exercice,
                    T.compte_id,
                    SUM(CASE WHEN T.type_mouvement = 'D' THEN T.montant ELSE 0 END) AS total_debit,
                    SUM(CASE WHEN T.type_mouvement = 'C' THEN T.montant ELSE 0 END) AS total_credit
                FROM (
                    -- UNION des comptes débités
                    SELECT 
                        compte_debite_id AS compte_id, 
                        montant, 
                        'D' AS type_mouvement 
                    FROM ECRITURES_COMPTABLES
                    WHERE societe_id = :societe_id AND YEAR(date_operation) = :exercice
                    UNION ALL
                    -- UNION des comptes crédités
                    SELECT 
                        compte_credite_id AS compte_id, 
                        montant, 
                        'C' AS type_mouvement 
                    FROM ECRITURES_COMPTABLES
                    WHERE societe_id = :societe_id AND YEAR(date_operation) = :exercice
                ) AS T
                GROUP BY T.compte_id
                
                -- Clause ON DUPLICATE KEY UPDATE : si le compte existe déjà, met à jour (utile pour les modifications)
                ON DUPLICATE KEY UPDATE 
                    mouvement_debit = VALUES(mouvement_debit),
                    mouvement_credit = VALUES(mouvement_credit);
            ";

            $stmt = $this->pdo->prepare($sql_mouvements);
            $stmt->bindParam(':societe_id', $societeId);
            $stmt->bindParam(':exercice', $exercice);
            $stmt->execute();

            // B. Calcul des Soldes Débiteurs et Créditeurs
            // Mise à jour de la table SYNTHESES_BALANCE avec les soldes calculés : Mouvements - Mouvements opposés
            $sql_soldes = "
                UPDATE SYNTHESES_BALANCE
                SET 
                    solde_debiteur = CASE 
                        WHEN mouvement_debit > mouvement_credit THEN mouvement_debit - mouvement_credit 
                        ELSE 0 
                    END,
                    solde_crediteur = CASE 
                        WHEN mouvement_credit > mouvement_debit THEN mouvement_credit - mouvement_debit 
                        ELSE 0 
                    END
                WHERE societe_id = :societe_id AND exercice = :exercice;
            ";

            $stmt_soldes = $this->pdo->prepare($sql_soldes);
            $stmt_soldes->bindParam(':societe_id', $societeId);
            $stmt_soldes->bindParam(':exercice', $exercice);
            $stmt_soldes->execute();

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // Journaliser l'erreur pour le débogage
            error_log("Erreur de génération de la Balance : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Étape 2 : Récupération et Réarrangement de la Balance
     * Récupère la Balance complète des 6 colonnes (Mouvements et Soldes + Numéro de compte/Intitulé).
     * @param int $societeId
     * @param int $exercice
     * @return array
     */
    public function getBalanceFinale(int $societeId, int $exercice): array {
        $sql = "
            SELECT 
                PC.compte_id,
                PC.intitule_compte,
                SB.mouvement_debit,
                SB.mouvement_credit,
                SB.solde_debiteur,
                SB.solde_crediteur,
                PC.nature_resultat -- Essentiel pour le réarrangement (Phase B - Étape 3)
            FROM SYNTHESES_BALANCE SB
            JOIN PLAN_COMPTABLE_UEMOA PC ON SB.compte_id = PC.compte_id
            WHERE SB.societe_id = :societe_id AND SB.exercice = :exercice
            ORDER BY PC.compte_id ASC;
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':societe_id', $societeId);
        $stmt->bindParam(':exercice', $exercice);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Étape 3 : Ventilation pour le Compte de Résultat et le Bilan
     * Réarrange les soldes de la Balance pour les états financiers finaux.
     * @param array $balanceData Résultat de getBalanceFinale().
     * @return array Synthèse des totaux.
     */
    public function ventilerEtatsFinanciers(array $balanceData): array {
        $synthese = [
            'resultat_exploitation' => ['produits' => 0, 'charges' => 0],
            'resultat_financier' => ['produits' => 0, 'charges' => 0],
            'resultat_hao' => ['produits' => 0, 'charges' => 0],
            'bilan' => ['actif' => 0, 'passif' => 0],
        ];

        foreach ($balanceData as $row) {
            // Le solde du compte est soit débiteur, soit créditeur, jamais les deux.
            $solde = $row['solde_debiteur'] + $row['solde_crediteur'];
            
            switch ($row['nature_resultat']) {
                case 'EXP': // Classes 6 et 7 d'exploitation
                    if ($row['solde_debiteur'] > 0) $synthese['resultat_exploitation']['charges'] += $solde;
                    if ($row['solde_crediteur'] > 0) $synthese['resultat_exploitation']['produits'] += $solde;
                    break;
                case 'FIN': // Classes 6 et 7 financières
                    if ($row['solde_debiteur'] > 0) $synthese['resultat_financier']['charges'] += $solde;
                    if ($row['solde_crediteur'] > 0) $synthese['resultat_financier']['produits'] += $solde;
                    break;
                case 'HAO': // Classes 6 et 7 Hors AO
                    if ($row['solde_debiteur'] > 0) $synthese['resultat_hao']['charges'] += $solde;
                    if ($row['solde_crediteur'] > 0) $synthese['resultat_hao']['produits'] += $solde;
                    break;
                case 'BIL': // Classes 1 à 5 (Bilan)
                    // Note: Le réarrangement du Bilan est plus complexe que cette simple agrégation.
                    // Pour cet exercice, nous aggrégeons les soldes pour vérifier l'équilibre.
                    if ($row['solde_debiteur'] > 0) $synthese['bilan']['actif'] += $solde; // Actif (2, 3, 4 D, 5)
                    if ($row['solde_crediteur'] > 0) $synthese['bilan']['passif'] += $solde; // Passif (1, 4 C)
                    break;
            }
        }
        
        // Calcul du Résultat Net Final (à ajouter au Bilan)
        $resultatNet = (
            $synthese['resultat_exploitation']['produits'] - $synthese['resultat_exploitation']['charges']
        ) + (
            $synthese['resultat_financier']['produits'] - $synthese['resultat_financier']['charges']
        ) + (
            $synthese['resultat_hao']['produits'] - $synthese['resultat_hao']['charges']
        );
        
        $synthese['resultat_net_final'] = $resultatNet;
        
        return $synthese;
    }
}





/**
 * Prépare les données pour l'affichage du Compte de Résultat (CR).
 * Regroupe les soldes des Classes 6 et 7 par nature.
 * @return array Données structurées du CR.
 */
public function getCompteResultatData(int $societeId, int $exercice): array {
    // Note : Le CR OHADA est très détaillé (Marge brute, Valeur ajoutée, etc.)
    // Nous utiliserons une requête simplifiée pour illustrer la classification EXP/FIN/HAO.
    $sql = "
        SELECT 
            PC.compte_id,
            PC.intitule_compte,
            PC.nature_resultat,
            SB.solde_debiteur AS charges,
            SB.solde_crediteur AS produits
        FROM SYNTHESES_BALANCE SB
        JOIN PLAN_COMPTABLE_UEMOA PC ON SB.compte_id = PC.compte_id
        WHERE SB.societe_id = :societe_id 
          AND SB.exercice = :exercice
          AND PC.classe IN (6, 7)
        ORDER BY PC.compte_id ASC;
    ";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':societe_id', $societeId);
    $stmt->bindParam(':exercice', $exercice);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Structure OHADA simplifiée
    $cr = [
        'Exploitation' => ['Charges' => [], 'Produits' => []],
        'Financier' => ['Charges' => [], 'Produits' => []],
        'Hors_AO' => ['Charges' => [], 'Produits' => []],
        'Total_Charges' => 0,
        'Total_Produits' => 0
    ];

    foreach ($data as $row) {
        $nature = $row['nature_resultat'];
        
        if ($row['charges'] > 0 && $nature !== 'BIL') {
            $cr[ucfirst(strtolower($nature))]['Charges'][] = $row;
            $cr['Total_Charges'] += $row['charges'];
        }
        if ($row['produits'] > 0 && $nature !== 'BIL') {
            $cr[ucfirst(strtolower($nature))]['Produits'][] = $row;
            $cr['Total_Produits'] += $row['produits'];
        }
    }
    
    $cr['Resultat_Net'] = $cr['Total_Produits'] - $cr['Total_Charges'];
    
    return $cr;
}



/**
 * Prépare les données pour l'affichage du Bilan.
 * Regroupe les soldes des Classes 1 à 5 en Actif et Passif.
 * @return array Données structurées du Bilan.
 */
public function getBilanData(int $societeId, int $exercice, float $resultatNet): array {
    $sql = "
        SELECT 
            PC.compte_id,
            PC.intitule_compte,
            SB.solde_debiteur,
            SB.solde_crediteur,
            PC.classe -- Utilisation de la classe pour le regroupement Actif/Passif
        FROM SYNTHESES_BALANCE SB
        JOIN PLAN_COMPTABLE_UEMOA PC ON SB.compte_id = PC.compte_id
        WHERE SB.societe_id = :societe_id 
          AND SB.exercice = :exercice
          AND PC.classe BETWEEN 1 AND 5 -- Comptes de Bilan
        ORDER BY PC.compte_id ASC;
    ";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':societe_id', $societeId);
    $stmt->bindParam(':exercice', $exercice);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bilan = [
        'Actif' => ['Immobilise' => 0, 'Circulant' => 0, 'Tresorerie' => 0, 'Details' => []],
        'Passif' => ['Capitaux_Propres' => 0, 'Dettes_LT' => 0, 'Dettes_CT' => 0, 'Details' => []],
        'Total_Actif' => 0,
        'Total_Passif_Avant_Resultat' => 0
    ];

    foreach ($data as $row) {
        // Logique de classification OHADA simplifiée (basée sur les classes)
        
        if ($row['solde_debiteur'] > 0) { // ACTIF
            $montant = $row['solde_debiteur'];
            $bilan['Total_Actif'] += $montant;
            $type = '';
            
            if ($row['classe'] == 2) { $type = 'Immobilise'; $bilan['Actif']['Immobilise'] += $montant; }
            elseif ($row['classe'] == 3 || $row['classe'] == 4) { $type = 'Circulant'; $bilan['Actif']['Circulant'] += $montant; }
            elseif ($row['classe'] == 5) { $type = 'Tresorerie'; $bilan['Actif']['Tresorerie'] += $montant; }
            
            $bilan['Actif']['Details'][] = ['compte' => $row['compte_id'], 'intitule' => $row['intitule_compte'], 'montant' => $montant, 'type' => $type];
            
        } elseif ($row['solde_crediteur'] > 0) { // PASSIF
            $montant = $row['solde_crediteur'];
            $bilan['Total_Passif_Avant_Resultat'] += $montant;
            $type = '';

            if ($row['classe'] == 1) { $type = 'Capitaux_Propres'; $bilan['Passif']['Capitaux_Propres'] += $montant; }
            elseif ($row['classe'] == 4) { $type = 'Dettes_CT'; $bilan['Passif']['Dettes_CT'] += $montant; }
            
            $bilan['Passif']['Details'][] = ['compte' => $row['compte_id'], 'intitule' => $row['intitule_compte'], 'montant' => $montant, 'type' => $type];
        }
    }

    // Intégration du Résultat Net
    $bilan['Passif']['Resultat_Net'] = $resultatNet;
    $bilan['Total_Passif_Apres_Resultat'] = $bilan['Total_Passif_Avant_Resultat'] + $resultatNet;
    
    return $bilan;
}



