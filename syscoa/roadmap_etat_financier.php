



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --financial-blue: #1e3a8a;
            --balance-green: #065f46;
            --result-orange: #9a3412;
            --cashflow-purple: #5b21b6;
        }
        
        .financial-statement {
            border: 2px solid var(--financial-blue);
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            background: linear-gradient(to right, #f0f9ff, #e0f2fe);
        }
        
        .balance-sheet {
            border-color: var(--balance-green);
            background: linear-gradient(to right, #f0fdf4, #dcfce7);
        }
        
        .profit-loss {
            border-color: var(--result-orange);
            background: linear-gradient(to right, #fff7ed, #fed7aa);
        }
        
        .calculation-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        
        .formula-box {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3b82f6;
            font-family: 'Consolas', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="module-header" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
            <h1>Module 3 : États Financiers selon SYSCOHADA</h1>
            <p>Production des documents légaux : Bilan, Compte de Résultat, Annexes, Tableaux de Financement</p>
        </div>
        
        <div class="financial-statement balance-sheet">
            <h3>📄 Bilan Actif/Passif - Méthodologie de Construction</h3>
            
            <div class="calculation-grid">
                <div>
                    <h4>Actif Immobilisé (Classe 2)</h4>
                    <div class="formula-box">
                        ACTIF IMMOBILISÉ = Bruts - Amortissements - Dépréciations<br>
                        = SUM(comptes 21 à 28) - SUM(comptes 29)
                    </div>
                </div>
                
                <div>
                    <h4>Formule SQL :</h4>
<pre>
SELECT 
    classe,
    SUM(CASE WHEN LEFT(numero, 1) IN ('2') 
        AND RIGHT(numero, 2) != '29' 
        THEN solde_debut + mouvement_debit - mouvement_credit 
        ELSE 0 END) as actif_immobilise
FROM soldes_comptes
WHERE exercice = 2024
GROUP BY classe;
</pre>
                </div>
            </div>
        </div>
        
        <div class="code-block">
            <h4>🧮 Procédure de Génération du Bilan :</h4>
<pre>
DELIMITER //
CREATE PROCEDURE sp_generer_bilan(IN p_exercice INT, IN p_comparatif BOOLEAN)
BEGIN
    -- Structure conforme au formulaire SYSCOHADA
    DROP TEMPORARY TABLE IF EXISTS temp_bilan;
    
    CREATE TEMPORARY TABLE temp_bilan (
        rubrique VARCHAR(200),
        code VARCHAR(10),
        montant_n DECIMAL(15,2),
        montant_n1 DECIMAL(15,2),
        variation DECIMAL(15,2),
        pourcentage_actif DOUBLE
    );
    
    -- ACTIF IMMOBILISÉ
    INSERT INTO temp_bilan (rubrique, code, montant_n)
    SELECT 'I - ACTIF IMMOBILISÉ', '20/29',
           SUM(solde_debut + mouvement_debit - mouvement_credit)
    FROM soldes_comptes s
    JOIN comptes_ohada c ON s.numero_compte = c.numero_compte
    WHERE s.exercice = p_exercice
    AND LEFT(s.numero_compte, 1) = '2'
    AND c.nature = 'A';
    
    -- ACTIF CIRCULANT (HORS TRÉSORERIE)
    INSERT INTO temp_bilan (rubrique, code, montant_n)
    SELECT 'II - ACTIF CIRCULANT (HORS TRÉSORERIE)', '30/37',
           SUM(solde_debut + mouvement_debit - mouvement_credit)
    FROM soldes_comptes s
    JOIN comptes_ohada c ON s.numero_compte = c.numero_compte
    WHERE s.exercice = p_exercice
    AND LEFT(s.numero_compte, 1) = '3'
    AND c.nature = 'A';
    
    -- TRÉSORERIE-ACTIF
    INSERT INTO temp_bilan (rubrique, code, montant_n)
    SELECT 'III - TRÉSORERIE-ACTIF', '50/53',
           SUM(solde_debut + mouvement_debit - mouvement_credit)
    FROM soldes_comptes s
    JOIN comptes_ohada c ON s.numero_compte = c.numero_compte
    WHERE s.exercice = p_exercice
    AND LEFT(s.numero_compte, 2) BETWEEN '50' AND '53'
    AND c.nature = 'A';
    
    -- Calcul des totaux et pourcentages
    UPDATE temp_bilan t
    JOIN (
        SELECT SUM(montant_n) as total_actif
        FROM temp_bilan
        WHERE code IN ('20/29', '30/37', '50/53')
    ) total ON 1=1
    SET t.pourcentage_actif = ROUND((t.montant_n / total.total_actif) * 100, 2);
    
    -- Insertion dans la table définitive
    INSERT INTO bilans (exercice, date_generation, type_bilan, donnees)
    SELECT p_exercice, NOW(), 'BILAN_SYSCOHADA',
           JSON_OBJECT(
               'actif_immobilise', (SELECT montant_n FROM temp_bilan WHERE code = '20/29'),
               'actif_circulant', (SELECT montant_n FROM temp_bilan WHERE code = '30/37'),
               'tresorerie_actif', (SELECT montant_n FROM temp_bilan WHERE code = '50/53'),
               'total_actif', (SELECT SUM(montant_n) FROM temp_bilan WHERE code IN ('20/29', '30/37', '50/53')),
               'pourcentages', JSON_OBJECT(
                   'immobilisations', (SELECT pourcentage_actif FROM temp_bilan WHERE code = '20/29'),
                   'circulant', (SELECT pourcentage_actif FROM temp_bilan WHERE code = '30/37'),
                   'tresorerie', (SELECT pourcentage_actif FROM temp_bilan WHERE code = '50/53')
               )
           );
    
    -- Historique
    INSERT INTO sig_historique (module, action, utilisateur, date_action)
    VALUES ('ETATS_FINANCIERS', CONCAT('Génération bilan exercice ', p_exercice), USER(), NOW());
    
    SELECT * FROM temp_bilan;
END //
DELIMITER ;
</pre>
        </div>
    </div>
</body>
</html>





