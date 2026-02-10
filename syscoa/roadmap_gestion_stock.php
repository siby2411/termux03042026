

<!DOCTYPE html>
<html lang="fr">
<head>
    <style>
        .stock-module {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .valuation-method {
            background: white;
            border: 2px solid #0d9488;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
        }
        
        .method-comparison {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 25px 0;
        }
        
        .method-card {
            background: #f0fdfa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-top: 4px solid #0d9488;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="stock-module">
            <h1>Module 4 : Gestion des Stocks - Valorisation et Contrôle</h1>
            <p>Gestion conforme aux normes OHADA - Classes 31 à 37</p>
        </div>
        
        <div class="valuation-method">
            <h3>📦 Méthodes de Valorisation des Stocks</h3>
            <p>Selon l'article 31-1 du Règlement OHADA, les stocks sont évalués au coût d'acquisition ou de production</p>
            
            <div class="method-comparison">
                <div class="method-card">
                    <h4>CMUP (Coût Moyen Unitaires Pondéré)</h4>
                    <p>Méthode recommandée pour la continuité</p>
                    <div class="formula-box">
                        CMUP = (Valeur stock initial + Valeur entrées) / (Qté initiale + Qté entrées)
                    </div>
                </div>
                
                <div class="method-card">
                    <h4>FIFO (First In, First Out)</h4>
                    <p>Premier entré, premier sorti</p>
                    <div class="formula-box">
                        Valorisation = Prix des lots les plus anciens
                    </div>
                </div>
                
                <div class="method-card">
                    <h4>LIFO (Last In, First Out)</h4>
                    <p>Dernier entré, premier sorti<br>
                    <span style="color: #dc2626;">Non autorisé par les normes OHADA</span></p>
                </div>
            </div>
        </div>
        
        <div class="code-block">
            <h4>🧮 Trigger de Valorisation CMUP :</h4>
<pre>
DELIMITER //
CREATE TRIGGER trg_calcul_cmup AFTER INSERT ON mouvements_stock
FOR EACH ROW
BEGIN
    DECLARE v_stock_initial DECIMAL(15,2);
    DECLARE v_quantite_initial DECIMAL(15,3);
    DECLARE v_cmup DECIMAL(15,2);
    DECLARE v_valeur_sortie DECIMAL(15,2);
    
    -- Calcul du stock avant mouvement
    SELECT quantite, valeur INTO v_quantite_initial, v_stock_initial
    FROM articles_stock 
    WHERE id_article = NEW.id_article;
    
    -- Pour les entrées (code mouvement 'E' ou 'R')
    IF NEW.type_mouvement IN ('E', 'R') THEN
        -- Calcul du nouveau CMUP
        SET v_cmup = (v_stock_initial + (NEW.quantite * NEW.prix_unitaire)) 
                   / (v_quantite_initial + NEW.quantite);
        
        -- Mise à jour de l'article
        UPDATE articles_stock 
        SET quantite = quantite + NEW.quantite,
            valeur = valeur + (NEW.quantite * NEW.prix_unitaire),
            prix_moyen_pondere = v_cmup,
            date_dernier_mouvement = NEW.date_mouvement
        WHERE id_article = NEW.id_article;
        
        -- Comptabilisation automatique
        INSERT INTO ecritures (numero_piece, date_ecriture, compte, libelle, sens, montant)
        VALUES (CONCAT('STK-', NEW.reference), NEW.date_mouvement,
                CASE 
                    WHEN NEW.type_mouvement = 'E' THEN '601'
                    WHEN NEW.type_mouvement = 'R' THEN '611'
                END,
                CONCAT('Stock ', NEW.id_article, ' - ', NEW.quantite, ' unités'),
                'D',
                NEW.quantite * NEW.prix_unitaire);
                
        INSERT INTO ecritures (numero_piece, date_ecriture, compte, libelle, sens, montant)
        VALUES (CONCAT('STK-', NEW.reference), NEW.date_mouvement,
                '31', -- Compte de stocks
                CONCAT('Contrepartie stock ', NEW.id_article),
                'C',
                NEW.quantite * NEW.prix_unitaire);
                
    -- Pour les sorties (code mouvement 'S' ou 'C')
    ELSEIF NEW.type_mouvement IN ('S', 'C') THEN
        -- Valorisation au CMUP
        SELECT prix_moyen_pondere INTO v_cmup
        FROM articles_stock
        WHERE id_article = NEW.id_article;
        
        SET v_valeur_sortie = NEW.quantite * v_cmup;
        
        -- Mise à jour du stock
        UPDATE articles_stock 
        SET quantite = quantite - NEW.quantite,
            valeur = valeur - v_valeur_sortie,
            date_dernier_mouvement = NEW.date_mouvement
        WHERE id_article = NEW.id_article;
        
        -- Écriture comptable de sortie
        INSERT INTO ecritures (numero_piece, date_ecriture, compte, libelle, sens, montant)
        VALUES (CONCAT('STK-', NEW.reference), NEW.date_mouvement,
                '603', -- Variation de stock
                CONCAT('Sortie stock ', NEW.id_article),
                'D',
                v_valeur_sortie);
                
        INSERT INTO ecritures (numero_piece, date_ecriture, compte, libelle, sens, montant)
        VALUES (CONCAT('STK-', NEW.reference), NEW.date_mouvement,
                '31',
                CONCAT('Contrepartie sortie ', NEW.id_article),
                'C',
                v_valeur_sortie);
    END IF;
    
    -- Mise à jour du coût des produits vendus
    IF NEW.type_mouvement = 'S' THEN
        UPDATE calculs_ebe 
        SET cout_produits_vendus = cout_produits_vendus + v_valeur_sortie
        WHERE exercice = YEAR(NEW.date_mouvement);
    END IF;
END //
DELIMITER ;
</pre>
        </div>
        
        <div class="principes-box" style="border-left-color: #0d9488;">
            <h3>📊 Points de Contrôle Inventaire :</h3>
            <p><strong>Écart d'inventaire :</strong> Tout écart > 2% doit être justifié et provisionné</p>
            <p><strong>Dépréciation des stocks :</strong> Application de la règle du "Lower of Cost or Market"</p>
            <p><strong>Rotation des stocks :</strong> Calcul automatique du ratio de rotation = Coût des marchandises vendues / Stock moyen</p>
            
            <div class="formula-box">
                Taux de rotation = (Coût des ventes / ((Stock début + Stock fin)/2))<br>
                Délai moyen de stockage = (365 / Taux de rotation) jours
            </div>
        </div>
    </div>
</body>
</html>


