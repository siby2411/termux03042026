#!/bin/bash
cd /root/shared/htdocs/apachewsl2026/gp

echo "🔄 Réinitialisation des tables..."

mysql -u root -p gp_db <<SQL
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE statuts_suivi;
TRUNCATE TABLE notifications_whatsapp;
TRUNCATE TABLE colis;
TRUNCATE TABLE produits;
TRUNCATE TABLE vetements;
TRUNCATE TABLE vols;
TRUNCATE TABLE charges;
TRUNCATE TABLE stats_globales;
TRUNCATE TABLE clients;
TRUNCATE TABLE clients_vetements;
TRUNCATE TABLE prospects_senegalais;

SET FOREIGN_KEY_CHECKS = 1;

-- CLIENTS FRET
INSERT INTO clients (nom, telephone, email, adresse, type) VALUES
('Dieynaba Keita', '+33758686348', 'dieynaba@example.com', '10 Rue de Paris, 75001 Paris', 'both'),
('Moussa Diop', '+221771234567', 'moussa@example.com', '15 Avenue Faidherbe, Dakar', 'expediteur'),
('Fatou Sow', '+33712345678', 'fatou@example.com', '25 Rue de la République, Lyon', 'destinataire'),
('Amadou Ba', '+221776543210', 'amadou@example.com', '8 Boulevard de la Gare, Thiès', 'expediteur'),
('Claire Martin', '+33698765432', 'claire@example.com', '42 Rue Paradis, Marseille', 'destinataire');

UPDATE clients SET code_client = CONCAT('CLT-', LPAD(id, 5, '0'));

-- CLIENTS VÊTEMENTS
INSERT INTO clients_vetements (nom, telephone, email, adresse) VALUES
('Aïssatou Diallo', '+33711223344', 'aissatou@example.com', '15 Rue de la Mode, Paris'),
('Oumar Ndiaye', '+33755667788', 'oumar@example.com', '8 Avenue de la République, Lyon'),
('Khadija Fall', '+33799887766', 'khadija@example.com', '32 Boulevard du Sud, Marseille');

-- PROSPECTS
INSERT INTO prospects_senegalais (civilite, nom, prenom, fonction, association_entreprise, email, telephone, ville, type_contact) VALUES
('M.', 'Sall', 'Maguette', 'Président', 'Grande Mosquée de Paris', 'contact@gmp.fr', '+33123456789', 'Paris', 'association'),
('Mme', 'Ndiaye', 'Awa', 'Gérante', 'Restaurant Le Ndar', 'awa.ndiaye@restaurant.com', '+33758686348', 'Paris', 'restaurant');

-- PRODUITS ÉPICERIE
INSERT INTO produits (nom, description, prix, stock, image) VALUES
('Huile de palme rouge (1L)', 'Huile naturelle 100% pure', 8.50, 25, 'uploads/huile_palme.jpg'),
('Crevettes séchées (200g)', 'Crevettes artisanales séchées', 12.90, 15, 'uploads/crevettes.jpg'),
('Soupe kandia (Gombo)', 'Mélange d\'épices traditionnelles', 5.50, 40, 'uploads/kandia.jpg'),
('Miel de Casamance (250g)', 'Miel sauvage récolté en forêt', 11.50, 20, 'uploads/miel.jpg');

-- VÊTEMENTS
INSERT INTO vetements (nom, description, categorie, prix, tailles, couleurs, stock, image) VALUES
('Boubou Sénégalais', 'Boubou traditionnel en tissu bazin', 'Homme', 89.00, 'M,L,XL', 'Bleu,Marron,Noir', 12, 'uploads/vetements/boubou1.jpg'),
('Robe Kaftan', 'Kaftan en soie pour cérémonies', 'Femme', 120.00, 'S,M,L', 'Or,Rose,Bordeaux', 8, 'uploads/vetements/kaftan1.jpg'),
('Grand Boubou Femme', 'Ensemble pagne + boubou', 'Femme', 95.00, 'M,L,XL', 'Multicolore', 15, 'uploads/vetements/grand_boubou.jpg');

-- VOLS
INSERT INTO vols (numero_vol, depart_ville, arrivee_ville, date_depart, date_arrivee_estimee, statut) VALUES
('SN202', 'Paris', 'Dakar', '2026-04-28 08:00:00', '2026-04-28 14:30:00', 'planifie'),
('AF823', 'Dakar', 'Paris', '2026-05-02 10:30:00', '2026-05-02 16:00:00', 'planifie');

-- COLIS (le trigger génère les numéros)
INSERT INTO colis (client_expediteur_id, client_destinataire_id, vol_id, description, poids_kg, statut, destinataire_nom, destinataire_telephone, destinataire_adresse, lieu_depart, lieu_arrivee, frais_expedition, frais_douane, montant_encaisse) VALUES
(2, 3, 1, 'Ordinateur portable Dell', 2.5, 'depart', 'Fatou Sow', '+33712345678', '25 Rue de la République, Lyon', 'Dakar', 'Paris', 45.00, 15.00, 60.00),
(2, 1, 2, 'Vêtements traditionnels', 5.0, 'transit', 'Dieynaba Keita', '+33758686348', '10 Rue de Paris, Paris', 'Dakar', 'Paris', 65.00, 20.00, 85.00),
(4, 5, 1, 'Matériel électronique', 8.2, 'enregistre', 'Claire Martin', '+33698765432', '42 Rue Paradis, Marseille', 'Thiès', 'Lyon', 45.00, 18.00, 63.00);

-- CHARGES
INSERT INTO charges (libelle, montant, date_charge, categorie, notes) VALUES
('Carburant - Vol SN202', 150.00, '2026-04-20', 'carburant', 'Plein du vol'),
('Salaires équipe Dakar', 800.00, '2026-04-15', 'personnel', 'Salaires avril'),
('Location entrepôt Paris', 400.00, '2026-04-01', 'location', 'Loyer mensuel'),
('Frais douane colis groupé', 200.00, '2026-04-05', 'douane', 'Dédouanement');

-- STATUTS SUIVI
INSERT INTO statuts_suivi (colis_id, statut, localisation) VALUES
(1, 'enregistre', 'Dakar Sénégal'),
(1, 'depart', 'Aéroport Dakar'),
(2, 'enregistre', 'Dakar Sénégal'),
(3, 'enregistre', 'Thiès Sénégal');

-- STATISTIQUES GLOBALES
INSERT INTO stats_globales (date_stat, total_colis_expedies, total_colis_paris_dakar, total_colis_dakar_paris, total_charges, total_encaissements, benefice_net)
VALUES (
    CURDATE(),
    (SELECT COUNT(*) FROM colis),
    (SELECT COUNT(*) FROM colis WHERE lieu_depart = 'Paris'),
    (SELECT COUNT(*) FROM colis WHERE lieu_arrivee = 'Paris'),
    (SELECT SUM(montant) FROM charges),
    (SELECT SUM(montant_encaisse) FROM colis),
    ((SELECT SUM(montant_encaisse) FROM colis) - (SELECT SUM(montant) FROM charges))
);

SQL

echo ""
echo "✅ Réinitialisation terminée !"
echo ""
mysql -u root -p gp_db -e "SELECT 'Clients' AS Type, COUNT(*) AS Total FROM clients UNION SELECT 'Colis', COUNT(*) FROM colis UNION SELECT 'Charges', COUNT(*) FROM charges;"
