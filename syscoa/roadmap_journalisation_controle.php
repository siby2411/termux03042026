


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-blue: #1a365d;
            --secondary-blue: #2d3748;
            --accent-blue: #3182ce;
            --validation-green: #38a169;
            --warning-orange: #dd6b20;
            --error-red: #e53e3e;
            --light-gray: #f7fafc;
            --border-color: #e2e8f0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--secondary-blue);
            background-color: var(--light-gray);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .module-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .principes-box {
            background: white;
            border-left: 5px solid var(--validation-green);
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Consolas', monospace;
            overflow-x: auto;
            margin: 20px 0;
        }
        
        .methodology-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .method-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-top: 4px solid var(--accent-blue);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="module-header">
            <h1>Module 2 : Tenue du Journal et Journalisation</h1>
            <p>Processus de saisie, contrôle et validation des écritures comptables selon les normes OHADA</p>
        </div>
        
        <div class="principes-box">
            <h3>🔍 Principes Comptables Appliqués :</h3>
            <ul>
                <li><strong>Principe de la partie double</strong> : Pour chaque écriture, le total débit = total crédit</li>
                <li><strong>Principe de justification</strong> : Toute écriture doit être appuyée par une pièce justificative</li>
                <li><strong>Principe de non-compensation</strong> : Interdiction de compenser dettes et créances</li>
                <li><strong>Principe d'intangibilité du bilan d'ouverture</strong> : Article 10 du Règlement OHADA</li>
            </ul>
        </div>
        
        <div class="code-block">
            <h4>📝 Trigger de Contrôle d'Équilibre Débit/Crédit :</h4>
<pre>
DELIMITER //
CREATE TRIGGER trg_check_balance_ecriture BEFORE INSERT ON ecritures
FOR EACH ROW
BEGIN
    DECLARE total_debit DECIMAL(15,2);
    DECLARE total_credit DECIMAL(15,2);
    
    -- Calcul des totaux pour la pièce
    SELECT SUM(CASE WHEN sens = 'D' THEN montant ELSE 0 END),
           SUM(CASE WHEN sens = 'C' THEN montant ELSE 0 END)
    INTO total_debit, total_credit
    FROM ecritures
    WHERE numero_piece = NEW.numero_piece;
    
    -- Ajout de la nouvelle ligne
    SET total_debit = total_debit + IF(NEW.sens = 'D', NEW.montant, 0);
    SET total_credit = total_credit + IF(NEW.sens = 'C', NEW.montant, 0);
    
    -- Vérification de l'équilibre (tolérance de 0.01)
    IF ABS(total_debit - total_credit) > 0.01 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Écriture déséquilibrée. Différence détectée.';
    END IF;
    
    -- Vérification de la date dans l'exercice
    IF NEW.date_ecriture NOT BETWEEN 
        (SELECT date_debut FROM exercice WHERE cloture = FALSE) 
        AND 
        (SELECT date_fin FROM exercice WHERE cloture = FALSE) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Date hors exercice ouvert';
    END IF;
END //
DELIMITER ;
</pre>
        </div>
        
        <h3>📊 Workflow de Validation des Écritures :</h3>
        <div class="methodology-grid">
            <div class="method-card">
                <h4>Étape 1 : Saisie Initiale</h4>
                <p>Contrôle automatique des numéros de compte et des libellés</p>
                <ul>
                    <li>Validation des comptes actifs</li>
                    <li>Vérification des soldes disponibles</li>
                    <li>Contrôle des pièces justificatives</li>
                </ul>
            </div>
            
            <div class="method-card">
                <h4>Étape 2 : Contrôle Interne</h4>
                <p>Validation hiérarchique selon le workflow défini</p>
                <ul>
                    <li>Vérification par le responsable comptable</li>
                    <li>Approbation budgétaire si nécessaire</li>
                    <li>Contrôle des engagements</li>
                </ul>
            </div>
            
            <div class="method-card">
                <h4>Étape 3 : Validation Finale</h4>
                <p>Pointage et lettrage automatique</p>
                <ul>
                    <li>Rapprochement bancaire</li>
                    <li>Lettrage clients/fournisseurs</li>
                    <li>Génération du journal</li>
                </ul>
            </div>
        </div>
        
        <div class="principes-box" style="border-left-color: var(--warning-orange);">
            <h3>⚠️ Points de Vigilance :</h3>
            <p><strong>Référencement des pièces :</strong> Chaque écriture doit avoir un numéro de pièce unique selon la séquence préétablie (table <code>numerotation_automatique</code>)</p>
            <p><strong>Date de valeur :</strong> Différenciation entre date de comptabilisation et date de valeur pour les opérations bancaires</p>
            <p><strong>Contrôle des délais :</strong> Les écritures doivent être comptabilisées dans les 30 jours suivant l'opération (Art. 15 du Règlement)</p>
        </div>
        
        <div class="code-block">
            <h4>🔄 Procédure de Lettrage Automatique :</h4>
<pre>
DELIMITER //
CREATE PROCEDURE sp_lettrage_automatique(IN p_type_tiers VARCHAR(20), IN p_date_debut DATE, IN p_date_fin DATE)
BEGIN
    DECLARE v_finished INTEGER DEFAULT 0;
    DECLARE v_id_tiers INT;
    DECLARE v_solde DECIMAL(15,2);
    DECLARE cur_tiers CURSOR FOR 
        SELECT id_tiers, solde 
        FROM tiers 
        WHERE type_tiers = p_type_tiers 
        AND solde != 0;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finished = 1;
    
    OPEN cur_tiers;
    
    lettrage_loop: LOOP
        FETCH cur_tiers INTO v_id_tiers, v_solde;
        IF v_finished = 1 THEN 
            LEAVE lettrage_loop;
        END IF;
        
        -- Logique de lettrage FIFO (First In, First Out)
        INSERT INTO lettrage_automatique (id_tiers, date_lettrage, montant_lettre)
        SELECT v_id_tiers, NOW(), 
               CASE 
                   WHEN v_solde > 0 THEN 
                       (SELECT SUM(montant) 
                        FROM ecritures 
                        WHERE id_tiers = v_id_tiers 
                        AND sens = 'C' 
                        AND lettree = FALSE 
                        AND date_ecriture BETWEEN p_date_debut AND p_date_fin
                        ORDER BY date_ecriture ASC)
                   ELSE 
                       (SELECT SUM(montant) 
                        FROM ecritures 
                        WHERE id_tiers = v_id_tiers 
                        AND sens = 'D' 
                        AND lettree = FALSE 
                        AND date_ecriture BETWEEN p_date_debut AND p_date_fin
                        ORDER BY date_ecriture ASC)
               END;
        
        -- Mise à jour du statut des écritures lettrées
        UPDATE ecritures 
        SET lettree = TRUE, 
            date_lettrage = NOW(),
            reference_lettrage = (SELECT MAX(id_lettrage) FROM lettrage_automatique)
        WHERE id_tiers = v_id_tiers 
        AND lettree = FALSE 
        AND date_ecriture BETWEEN p_date_debut AND p_date_fin;
        
    END LOOP;
    
    CLOSE cur_tiers;
    
    -- Journalisation
    INSERT INTO sig_historique (module, action, utilisateur, date_action)
    VALUES ('LETTRAGE', CONCAT('Lettrage auto ', p_type_tiers, ' du ', p_date_debut, ' au ', p_date_fin), USER(), NOW());
END //
DELIMITER ;
</pre>
        </div>
    </div>
</body>
</html>


