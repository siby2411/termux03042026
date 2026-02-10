-- Insert ventilation affinée (règles par classe / préfixe)
CREATE TABLE IF NOT EXISTS VENTILATION_ITEMS (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  item_code VARCHAR(40) NOT NULL,
  item_label VARCHAR(200) NOT NULL,
  item_type ENUM('BILAN_ACTIF','BILAN_PASSIF','CR_CHARGES','CR_PRODUITS') NOT NULL,
  criteria JSON DEFAULT NULL,
  display_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- nettoyage des AUTO-*
DELETE FROM VENTILATION_ITEMS WHERE item_code LIKE 'AUTO-%';

-- Insertion des 12 postes SYSCOHADA (critères : classes, préfixes, ou plages)
INSERT INTO VENTILATION_ITEMS (item_code, item_label, item_type, criteria, display_order) VALUES
('I1','Immobilisations incorporelles','BILAN_ACTIF',  JSON_OBJECT('classe_from',20,'classe_to',21), 1),
('I2','Immobilisations corporelles','BILAN_ACTIF',    JSON_OBJECT('classe_from',22,'classe_to',24), 2),
('I3','Immobilisations financières','BILAN_ACTIF',    JSON_OBJECT('classe_from',26,'classe_to',27), 3),
('I4','Stocks et en-cours','BILAN_ACTIF',             JSON_OBJECT('classe_from',30,'classe_to',39), 4),
('I5','Créances clients & autres actifs circulants','BILAN_ACTIF', JSON_OBJECT('classe_from',40,'classe_to',49), 5),
('I6','Disponibilités (banque,caisse)','BILAN_ACTIF', JSON_OBJECT('classe_from',50,'classe_to',59), 6),
('P1','Capitaux propres','BILAN_PASSIF',              JSON_OBJECT('classe_from',10,'classe_to',19), 7),
('P2','Provisions & dettes LT','BILAN_PASSIF',        JSON_OBJECT('classe_from',15,'classe_to',16), 8),
('P3','Dettes fournisseurs & CT','BILAN_PASSIF',      JSON_OBJECT('prefixes', JSON_ARRAY('40','401','403','404')), 9),
('P4','Charges à payer / produits constatés d''avance','BILAN_PASSIF', JSON_OBJECT('classe_from',48,'classe_to',48), 10),
('R1','Compte de résultat - Charges','CR_CHARGES',     JSON_OBJECT('classe_from',60,'classe_to',69), 11),
('R2','Compte de résultat - Produits','CR_PRODUITS',   JSON_OBJECT('classe_from',70,'classe_to',79), 12);
