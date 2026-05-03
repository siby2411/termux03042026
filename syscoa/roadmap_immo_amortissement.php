
<!DOCTYPE html>
<html lang="fr">
<head>
    <style>
        .immobilisation-module {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .amortissement-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 25px 0;
        }
        
        .amortissement-card {
            background: #faf5ff;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #8b5cf6;
        }
        
        .calculation-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        
        .calculation-table th {
            background: #7c3aed;
            color: white;
            padding: 10px;
        }
        
        .calculation-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: right;
        }
        
        .tax-reference {
            background: #fef3c7;
            border-left: 4px solid #d97706;
            padding: 15px;
            margin: 15px 0;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="immobilisation-module">
            <h1>Module 5 : Immobilisations et Amortissements</h1>
            <p>Gestion du cycle de vie des actifs - Comptes 21 à 28 - Amortissements linéaires et dégressifs</p>
        </div>
        
        <div class="tax-reference">
            <h4>🔖 Références Fiscales :</h4>
            <p><strong>Art. 12 CGI :</strong> Amortissement linéaire - Taux selon nature du bien</p>
            <p><strong>Art. 13 CGI :</strong> Amortissement dégressif - Biens neufs, durée ≥ 3 ans</p>
            <p><strong>Art. 15 CGI :</strong> Majoration exceptionnelle d'amortissement - Investissements productifs</p>
        </div>
        
        <div class="amortissement-grid">
            <div class="amortissement-card">
                <h4>Amortissement Linéaire</h4>
                <div class="formula-box">
                    Annuité = Base amortissable × Taux<br>
                    Taux = 100 / Durée (en années)<br>
                    Base = Valeur d'origine - Valeur résiduelle
                </div>
                <p><strong>Application :</strong> Bâtiments, matériel de bureau, véhicules administratifs</p>
            </div>
            
            <div class="amortissement-card">
                <h4>Amortissement Dégressif</h4>
                <div class="formula-box">
                    Annuité = VCNC × Taux × Coefficient<br>
                    VCNC = Valeur comptable nette début année<br>
                    Coefficient = 1.25 (base) à 2.5 (exceptionnel)<br>
                    Minimum : annuité linéaire sur durée restante
                </div>
                <p><strong>Application :</strong> Machines industrielles, matériel informatique, équipements de production</p>
            </div>
        </div>
        
        <div class="code-block">
            <h4>📊 Procédure de Calcul d'Amortissement :</h4>
<pre>
DELIMITER //
CREATE PROCEDURE sp_calcul_amortissements(IN p_exercice INT, IN p_methode VARCHAR(20))
BEGIN
    DECLARE v_finished INTEGER DEFAULT 0;
    DECLARE v_id_immobilisation INT;
    DECLARE v_valeur_origine DECIMAL(15,2);
    DECLARE v_valeur_residuelle DECIMAL(15,2);
    DECLARE v_date_mise_service DATE;
    DECLARE v_duree_amortissement INT;
    DECLARE v_taux_amortissement DECIMAL(5,2);
    DECLARE v_compte_immobilisation VARCHAR(6);
    DECLARE v_compte_amortissement VARCHAR(6);
    DECLARE v_valeur_comptable DECIMAL(15,2);
    DECLARE v_age_actif INT;
    DECLARE v_annuite DECIMAL(15,2);
    DECLARE v_total_amorti DECIMAL(15,2);
    
    DECLARE cur_immobilisations CURSOR FOR 
        SELECT i.id_immobilisation, i.valeur_origine, i.valeur_residuelle,
               i.date_mise_service, i.duree_amortissement, i.compte_immobilisation,
               i.compte_amortissement, c.taux_amortissement
        FROM immobilisations i
        JOIN classes_ohada c ON i.classe_immobilisation = c.code_classe
        WHERE i.actif = TRUE
        AND YEAR(i.date_mise_service) <= p_exercice
        AND (i.date_cession IS NULL OR YEAR(i.date_cession) > p_exercice);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finished = 1;
    
    -- Table temporaire pour les calculs
    DROP TEMPORARY TABLE IF EXISTS temp_amortissements;
    CREATE TEMPORARY TABLE temp_amortissements (
        id_immobilisation INT,
        exercice INT,
        annuite DECIMAL(15,2),
        cumul_amortissement DECIMAL(15,2),
        vnc DECIMAL(15,2),
        methode VARCHAR(20)
    );
    
    OPEN cur_immobilisations;
    
    calcul_loop: LOOP
        FETCH cur_immobilisations INTO v_id_immobilisation, v_valeur_origine, 
            v_valeur_residuelle, v_date_mise_service, v_duree_amortissement,
            v_compte_immobilisation, v_compte_amortissement, v_taux_amortissement;
        
        IF v_finished = 1 THEN 
            LEAVE calcul_loop;
        END IF;
        
        -- Calcul de l'âge de l'actif
        SET v_age_actif = p_exercice - YEAR(v_date_mise_service);
        
        -- Calcul du total déjà amorti
        SELECT COALESCE(SUM(annuite), 0) INTO v_total_amorti
        FROM plan_amortissement 
        WHERE id_immobilisation = v_id_immobilisation
        AND exercice < p_exercice;
        
        -- Valeur comptable nette début année
        SET v_valeur_comptable = v_valeur_origine - v_total_amorti;
        
        -- Calcul de l'annuité selon la méthode
        IF p_methode = 'LINEAIRE' THEN
            -- Amortissement linéaire
            SET v_annuite = (v_valeur_origine - v_valeur_residuelle) * (v_taux_amortissement / 100);
            
            -- Dernière année : ajustement pour atteindre la valeur résiduelle
            IF v_age_actif = v_duree_amortissement - 1 THEN
                SET v_annuite = v_valeur_comptable - v_valeur_residuelle;
            END IF;
            
        ELSEIF p_methode = 'DEGRESSIF' THEN
            -- Amortissement dégressif (coefficient 1.25)
            SET v_annuite = v_valeur_comptable * (v_taux_amortissement / 100) * 1.25;
            
            -- Vérification du minimum linéaire sur durée restante
            IF v_age_actif > 0 THEN
                DECLARE v_annuite_lineaire DECIMAL(15,2);
                DECLARE v_duree_restante INT;
                
                SET v_duree_restante = v_duree_amortissement - v_age_actif;
                SET v_annuite_lineaire = (v_valeur_comptable - v_valeur_residuelle) / v_duree_restante;
                
                -- Application du minimum
                IF v_annuite < v_annuite_lineaire THEN
                    SET v_annuite = v_annuite_lineaire;
                    SET p_methode = 'LINEAIRE_FORCE';
                END IF;
            END IF;
        END IF;
        
        -- Limite : ne pas amortir en dessous de la valeur résiduelle
        IF (v_total_amorti + v_annuite) > (v_valeur_origine - v_valeur_residuelle) THEN
            SET v_annuite = (v_valeur_origine - v_valeur_residuelle) - v_total_amorti;
        END IF;
        
        -- Insertion dans la table temporaire
        INSERT INTO temp_amortissements 
        VALUES (v_id_immobilisation, p_exercice, v_annuite, 
                v_total_amorti + v_annuite, 
                v_valeur_origine - (v_total_amorti + v_annuite),
                p_methode);
        
        -- Génération automatique de l'écriture comptable
        INSERT INTO ecritures (numero_piece, date_ecriture, compte, libelle, sens, montant)
        VALUES (CONCAT('AMRT-', v_id_immobilisation, '-', p_exercice),
                CONCAT(p_exercice, '-12-31'),
                '681', -- Dotations aux amortissements
                CONCAT('Amortissement ', p_methode, ' - Immob. ', v_id_immobilisation),
                'D',
                v_annuite);
                
        INSERT INTO ecritures (numero_piece, date_ecriture, compte, libelle, sens, montant)
        VALUES (CONCAT('AMRT-', v_id_immobilisation, '-', p_exercice),
                CONCAT(p_exercice, '-12-31'),
                v_compte_amortissement, -- Compte d'amortissement (28xx)
                CONCAT('Contrepartie amortissement immob. ', v_id_immobilisation),
                'C',
                v_annuite);
                
    END LOOP;
    
    CLOSE cur_immobilisations;
    
    -- Insertion dans le plan d'amortissement
    INSERT INTO plan_amortissement (id_immobilisation, exercice, annuite, cumul_amortissements, vnc, methode)
    SELECT id_immobilisation, exercice, annuite, cumul_amortissement, vnc, methode
    FROM temp_amortissements;
    
    -- Historique
    INSERT INTO sig_historique (module, action, utilisateur, date_action)
    VALUES ('AMORTISSEMENTS', CONCAT('Calcul amortissements ', p_methode, ' exercice ', p_exercice), USER(), NOW());
    
    -- Affichage du tableau d'amortissement
    SELECT * FROM temp_amortissements ORDER BY id_immobilisation;
    
END //
DELIMITER ;
</pre>
        </div>
        
        <h3>📈 Tableau d'Amortissement Type :</h3>
        <table class="calculation-table">
            <thead>
                <tr>
                    <th>Année</th>
                    <th>Valeur début</th>
                    <th>Annuité</th>
                    <th>Cumul amorti</th>
                    <th>VNC fin</th>
                    <th>Taux</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>N</td>
                    <td>1.000.000</td>
                    <td>200.000</td>
                    <td>200.000</td>
                    <td>800.000</td>
                    <td>20%</td>
                </tr>
                <tr>
                    <td>N+1</td>
                    <td>800.000</td>
                    <td>200.000</td>
                    <td>400.000</td>
                    <td>600.000</td>
                    <td>20%</td>
                </tr>
                <tr style="background-color: #f0f9ff;">
                    <td>N+2</td>
                    <td>600.000</td>
                    <td>200.000</td>
                    <td>600.000</td>
                    <td>400.000</td>
                    <td>20%</td>
                </tr>
                <tr>
                    <td>N+3</td>
                    <td>400.000</td>
                    <td>200.000</td>
                    <td>800.000</td>
                    <td>200.000</td>
                    <td>20%</td>
                </tr>
                <tr>
                    <td>N+4</td>
                    <td>200.000</td>
                    <td>200.000</td>
                    <td>1.000.000</td>
                    <td>0</td>
                    <td>20%</td>
                </tr>
            </tbody>
        </table>
        
        <div class="principes-box" style="border-left-color: #7c3aed;">
            <h3>⚠️ Points Cruciaux Immobilisations :</h3>
            <p><strong>Seuil de capitalisation :</strong> Tout bien dont la valeur d'origine ≥ 100.000 FCFA doit être immobilisé (Art. 21-1 OHADA)</p>
            <p><strong>Comptabilisation des frais accessoires :</strong> Droits de douane, transport, installation, honoraires</p>
            <p><strong>Dépréciation :</strong> Test annuel de dépréciation si valeur de marché < valeur comptable</p>
            <p><strong>Cessions :</strong> Calcul automatique de la plus/moins-value = Prix de cession - VNC à la date de cession</p>
        </div>
    </div>
</body>
</html>



