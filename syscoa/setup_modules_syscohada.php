<?php
/**
 * Script de création des modules SYSCOHADA manquants
 * Conforme au Plan Comptable OHADA
 */

class SetupModulesSyscohada {
    private $db;
    
    public function __construct() {
        require_once 'config.php';
        $this->db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function creerModulesComplets() {
        try {
            echo "🚀 CRÉATION DES MODULES SYSCOHADA COMPLETS\n";
            echo "==========================================\n";
            
            $this->creerModuleRapprochementBancaire();
            $this->creerModuleImmobilisations();
            $this->creerModuleStocksInventaire();
            $this->creerModuleRegularisationPassifs();
            $this->creerModuleComptabiliteAnalytique();
            $this->creerModuleControleInterne();
            
            echo "✅ TOUS LES MODULES SYSCOHADA ONT ÉTÉ CRÉÉS AVEC SUCCÈS!\n";
            
        } catch (PDOException $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * MODULE 1: RAPPROCHEMENT BANCAIRE
     */
    private function creerModuleRapprochementBancaire() {
        $sql = "
        -- Table des relevés bancaires
        CREATE TABLE IF NOT EXISTS releves_bancaires (
            id_releve INT PRIMARY KEY AUTO_INCREMENT,
            banque_id INT NOT NULL,
            compte_bancaire VARCHAR(50) NOT NULL,
            date_releve DATE NOT NULL,
            solde_releve DECIMAL(15,2) NOT NULL,
            fichier_releve VARCHAR(255),
            statut ENUM('saisi', 'rapproche', 'cloture') DEFAULT 'saisi',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (banque_id) REFERENCES tiers(id_tiers)
        );
        
        -- Table des opérations bancaires
        CREATE TABLE IF NOT EXISTS operations_bancaires (
            id_operation INT PRIMARY KEY AUTO_INCREMENT,
            releve_id INT NOT NULL,
            date_operation DATE NOT NULL,
            libelle_operation VARCHAR(255) NOT NULL,
            montant DECIMAL(15,2) NOT NULL,
            type_operation ENUM('debit', 'credit') NOT NULL,
            reference VARCHAR(100),
            ecriture_id INT,
            statut_rapprochement ENUM('non_rapproche', 'rapproche', 'conteste') DEFAULT 'non_rapproche',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (releve_id) REFERENCES releves_bancaires(id_releve),
            FOREIGN KEY (ecriture_id) REFERENCES ecritures(id_ecriture)
        );
        
        -- Table de rapprochement
        CREATE TABLE IF NOT EXISTS rapprochements_bancaires (
            id_rapprochement INT PRIMARY KEY AUTO_INCREMENT,
            compte_general_id INT NOT NULL, -- Compte 52 Banques
            date_debut DATE NOT NULL,
            date_fin DATE NOT NULL,
            solde_comptable DECIMAL(15,2),
            solde_bancaire DECIMAL(15,2),
            ecarts_non_expliques DECIMAL(15,2),
            statut ENUM('en_cours', 'termine', 'valide') DEFAULT 'en_cours',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (compte_general_id) REFERENCES comptes_ohada(id_compte)
        );
        ";
        
        $this->executerScriptSQL($sql);
        echo "✓ Module Rapprochement Bancaire créé\n";
    }
    
    /**
     * MODULE 2: GESTION DES IMMOBILISATIONS
     */
    private function creerModuleImmobilisations() {
        $sql = "
        -- Table des familles d'immobilisations
        CREATE TABLE IF NOT EXISTS familles_immobilisations (
            id_famille INT PRIMARY KEY AUTO_INCREMENT,
            code_famille VARCHAR(10) NOT NULL UNIQUE,
            libelle_famille VARCHAR(100) NOT NULL,
            compte_immobilisation INT NOT NULL, -- Compte 2xxx
            compte_amortissement INT NOT NULL, -- Compte 28xx
            compte_dotation INT NOT NULL, -- Compte 68xx
            duree_amortissement INT,
            taux_amortissement DECIMAL(5,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (compte_immobilisation) REFERENCES comptes_ohada(id_compte),
            FOREIGN KEY (compte_amortissement) REFERENCES comptes_ohada(id_compte),
            FOREIGN KEY (compte_dotation) REFERENCES comptes_ohada(id_compte)
        );
        
        -- Table des immobilisations (améliorée)
        CREATE TABLE IF NOT EXISTS immobilisations (
            id_immobilisation INT PRIMARY KEY AUTO_INCREMENT,
            code_immobilisation VARCHAR(20) UNIQUE NOT NULL,
            famille_id INT NOT NULL,
            designation VARCHAR(255) NOT NULL,
            date_acquisition DATE NOT NULL,
            date_mise_service DATE NOT NULL,
            valeur_acquisition DECIMAL(15,2) NOT NULL,
            mode_acquisition ENUM('achat', 'fabrication_interne', 'donation') DEFAULT 'achat',
            fournisseur_id INT,
            localisation VARCHAR(100),
            responsable VARCHAR(100),
            etat ENUM('neuf', 'bon', 'moyen', 'mauvais', 'hors_usage') DEFAULT 'neuf',
            methode_amortissement ENUM('lineaire', 'degressif') DEFAULT 'lineaire',
            taux_amortissement DECIMAL(5,2) NOT NULL,
            duree_amortissement INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (famille_id) REFERENCES familles_immobilisations(id_famille),
            FOREIGN KEY (fournisseur_id) REFERENCES tiers(id_tiers)
        );
        
        -- Table des plans d'amortissement
        CREATE TABLE IF NOT EXISTS plan_amortissement (
            id_plan INT PRIMARY KEY AUTO_INCREMENT,
            immobilisation_id INT NOT NULL,
            annee INT NOT NULL,
            dotation_annuelle DECIMAL(15,2),
            cumul_amortissement DECIMAL(15,2),
            vnc DECIMAL(15,2),
            date_calcul DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (immobilisation_id) REFERENCES immobilisations(id_immobilisation)
        );
        
        -- Table des cessions d'immobilisations
        CREATE TABLE IF NOT EXISTS cessions_immobilisations (
            id_cession INT PRIMARY KEY AUTO_INCREMENT,
            immobilisation_id INT NOT NULL,
            date_cession DATE NOT NULL,
            prix_cession DECIMAL(15,2),
            plus_value DECIMAL(15,2),
            moins_value DECIMAL(15,2),
            compte_produit_cession INT, -- Compte 75xx
            compte_charge_cession INT, -- Compte 65xx
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (immobilisation_id) REFERENCES immobilisations(id_immobilisation)
        );
        ";
        
        $this->executerScriptSQL($sql);
        echo "✓ Module Immobilisations créé\n";
    }
    
    /**
     * MODULE 3: GESTION DES STOCKS & INVENTAIRE
     */
    private function creerModuleStocksInventaire() {
        $sql = "
        -- Table des catégories de stocks
        CREATE TABLE IF NOT EXISTS categories_stock (
            id_categorie INT PRIMARY KEY AUTO_INCREMENT,
            code_categorie VARCHAR(10) NOT NULL UNIQUE,
            libelle_categorie VARCHAR(100) NOT NULL,
            compte_stock INT NOT NULL, -- Compte 3xxx
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (compte_stock) REFERENCES comptes_ohada(id_compte)
        );
        
        -- Table des articles de stock (améliorée)
        CREATE TABLE IF NOT EXISTS articles_stock (
            id_article INT PRIMARY KEY AUTO_INCREMENT,
            code_article VARCHAR(20) UNIQUE NOT NULL,
            categorie_id INT NOT NULL,
            designation VARCHAR(255) NOT NULL,
            unite_mesure VARCHAR(20) DEFAULT 'UNITE',
            prix_achat DECIMAL(15,2),
            prix_vente DECIMAL(15,2),
            stock_minimum DECIMAL(10,2) DEFAULT 0,
            stock_maximum DECIMAL(10,2),
            stock_alerte DECIMAL(10,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (categorie_id) REFERENCES categories_stock(id_categorie)
        );
        
        -- Table des inventaires physiques
        CREATE TABLE IF NOT EXISTS inventaires_physiques (
            id_inventaire INT PRIMARY KEY AUTO_INCREMENT,
            code_inventaire VARCHAR(20) UNIQUE NOT NULL,
            date_inventaire DATE NOT NULL,
            responsable VARCHAR(100) NOT NULL,
            statut ENUM('en_cours', 'termine', 'valide') DEFAULT 'en_cours',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Table des lignes d'inventaire
        CREATE TABLE IF NOT EXISTS lignes_inventaire (
            id_ligne INT PRIMARY KEY AUTO_INCREMENT,
            inventaire_id INT NOT NULL,
            article_id INT NOT NULL,
            quantite_theorique DECIMAL(10,2),
            quantite_reelle DECIMAL(10,2),
            ecart DECIMAL(10,2),
            valeur_ecart DECIMAL(15,2),
            motif_ecart TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (inventaire_id) REFERENCES inventaires_physiques(id_inventaire),
            FOREIGN KEY (article_id) REFERENCES articles_stock(id_article)
        );
        
        -- Table des mouvements de stock (améliorée)
        CREATE TABLE IF NOT EXISTS mouvements_stock (
            id_mouvement INT PRIMARY KEY AUTO_INCREMENT,
            article_id INT NOT NULL,
            type_mouvement ENUM('entree', 'sortie', 'transfert', 'inventaire') NOT NULL,
            quantite DECIMAL(10,2) NOT NULL,
            prix_unitaire DECIMAL(15,2),
            valeur_mouvement DECIMAL(15,2),
            date_mouvement DATE NOT NULL,
            reference VARCHAR(100),
            depot_origine INT,
            depot_destination INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (article_id) REFERENCES articles_stock(id_article)
        );
        ";
        
        $this->executerScriptSQL($sql);
        echo "✓ Module Stocks & Inventaire créé\n";
    }
    
    /**
     * MODULE 4: RÉGULARISATION DES PASSIFS
     */
    private function creerModuleRegularisationPassifs() {
        $sql = "
        -- Table des dettes à long terme
        CREATE TABLE IF NOT EXISTS dettes_long_terme (
            id_dette INT PRIMARY KEY AUTO_INCREMENT,
            compte_dette INT NOT NULL, -- Compte 16xx
            libelle_dette VARCHAR(255) NOT NULL,
            montant_initial DECIMAL(15,2) NOT NULL,
            taux_interet DECIMAL(5,2),
            date_contrat DATE,
            date_echeance DATE,
            creancier_id INT,
            statut ENUM('en_cours', 'rembourse', 'reschedule') DEFAULT 'en_cours',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (compte_dette) REFERENCES comptes_ohada(id_compte),
            FOREIGN KEY (creancier_id) REFERENCES tiers(id_tiers)
        );
        
        -- Table des échéanciers de dettes
        CREATE TABLE IF NOT EXISTS echeanciers_dettes (
            id_echeance INT PRIMARY KEY AUTO_INCREMENT,
            dette_id INT NOT NULL,
            date_echeance DATE NOT NULL,
            montant_echeance DECIMAL(15,2) NOT NULL,
            capital DECIMAL(15,2),
            interet DECIMAL(15,2),
            statut ENUM('due', 'payee', 'impayee') DEFAULT 'due',
            date_paiement DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (dette_id) REFERENCES dettes_long_terme(id_dette)
        );
        
        -- Table des provisions pour risques et charges
        CREATE TABLE IF NOT EXISTS provisions_risques_charges (
            id_provision INT PRIMARY KEY AUTO_INCREMENT,
            compte_provision INT NOT NULL, -- Compte 15xx
            libelle_provision VARCHAR(255) NOT NULL,
            montant_provision DECIMAL(15,2) NOT NULL,
            motif_provision TEXT,
            date_constatation DATE,
            date_revision DATE,
            probabilite ENUM('certaine', 'probable', 'improbable') DEFAULT 'probable',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (compte_provision) REFERENCES comptes_ohada(id_compte)
        );
        
        -- Table des engagements hors bilan
        CREATE TABLE IF NOT EXISTS engagements_hors_bilan (
            id_engagement INT PRIMARY KEY AUTO_INCREMENT,
            type_engagement ENUM('caution', 'garantie', 'avenant') NOT NULL,
            libelle_engagement VARCHAR(255) NOT NULL,
            montant_engagement DECIMAL(15,2) NOT NULL,
            beneficiaire VARCHAR(100),
            date_debut DATE,
            date_fin DATE,
            statut ENUM('actif', 'expire', 'leve') DEFAULT 'actif',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";
        
        $this->executerScriptSQL($sql);
        echo "✓ Module Régularisation Passifs créé\n";
    }
    
    /**
     * MODULE 5: COMPTABILITÉ ANALYTIQUE
     */
    private function creerModuleComptabiliteAnalytique() {
        $sql = "
        -- Table des centres analytiques
        CREATE TABLE IF NOT EXISTS centres_analytiques (
            id_centre INT PRIMARY KEY AUTO_INCREMENT,
            code_centre VARCHAR(10) NOT NULL UNIQUE,
            libelle_centre VARCHAR(100) NOT NULL,
            type_centre ENUM('fonctionnel', 'section', 'activite') NOT NULL,
            centre_parent INT,
            niveau INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (centre_parent) REFERENCES centres_analytiques(id_centre)
        );
        
        -- Table des clés de répartition
        CREATE TABLE IF NOT EXISTS cles_repartition (
            id_cle INT PRIMARY KEY AUTO_INCREMENT,
            libelle_cle VARCHAR(100) NOT NULL,
            type_cle ENUM('pourcentage', 'unite_oeuvre', 'fixe') NOT NULL,
            valeur_cle DECIMAL(10,4),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Table des imputations analytiques
        CREATE TABLE IF NOT EXISTS imputations_analytiques (
            id_imputation INT PRIMARY KEY AUTO_INCREMENT,
            ecriture_id INT NOT NULL,
            centre_id INT NOT NULL,
            montant DECIMAL(15,2) NOT NULL,
            pourcentage DECIMAL(5,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ecriture_id) REFERENCES ecritures(id_ecriture),
            FOREIGN KEY (centre_id) REFERENCES centres_analytiques(id_centre)
        );
        
        -- Table des budgets analytiques
        CREATE TABLE IF NOT EXISTS budgets_analytiques (
            id_budget INT PRIMARY KEY AUTO_INCREMENT,
            centre_id INT NOT NULL,
            annee INT NOT NULL,
            compte_ohada_id INT NOT NULL,
            montant_budget DECIMAL(15,2) NOT NULL,
            periode ENUM('annuel', 'trimestriel', 'mensuel') DEFAULT 'annuel',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (centre_id) REFERENCES centres_analytiques(id_centre),
            FOREIGN KEY (compte_ohada_id) REFERENCES comptes_ohada(id_compte)
        );
        ";
        
        $this->executerScriptSQL($sql);
        echo "✓ Module Comptabilité Analytique créé\n";
    }
    
    /**
     * MODULE 6: CONTRÔLE INTERNE & AUDIT
     */
    private function creerModuleControleInterne() {
        $sql = "
        -- Table des procédures de contrôle
        CREATE TABLE IF NOT EXISTS procedures_controle (
            id_procedure INT PRIMARY KEY AUTO_INCREMENT,
            code_procedure VARCHAR(10) NOT NULL UNIQUE,
            libelle_procedure VARCHAR(255) NOT NULL,
            processus_concerne VARCHAR(100),
            frequence ENUM('quotidien', 'hebdomadaire', 'mensuel', 'annuel') NOT NULL,
            responsable VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        -- Table des points de contrôle
        CREATE TABLE IF NOT EXISTS points_controle (
            id_point INT PRIMARY KEY AUTO_INCREMENT,
            procedure_id INT NOT NULL,
            libelle_point VARCHAR(255) NOT NULL,
            type_controle ENUM('preventif', 'detectif', 'correctif') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (procedure_id) REFERENCES procedures_controle(id_procedure)
        );
        
        -- Table des réalisations de contrôle
        CREATE TABLE IF NOT EXISTS realisations_controle (
            id_realisation INT PRIMARY KEY AUTO_INCREMENT,
            point_id INT NOT NULL,
            date_realisation DATE NOT NULL,
            resultat ENUM('conforme', 'non_conforme', 'a_verifier') NOT NULL,
            commentaire TEXT,
            preuve VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (point_id) REFERENCES points_controle(id_point)
        );
        
        -- Table des anomalies détectées
        CREATE TABLE IF NOT EXISTS anomalies_detectees (
            id_anomalie INT PRIMARY KEY AUTO_INCREMENT,
            realisation_id INT NOT NULL,
            description_anomalie TEXT NOT NULL,
            gravite ENUM('mineure', 'majeure', 'critique') NOT NULL,
            action_corrective TEXT,
            date_correction_prevue DATE,
            statut_correction ENUM('en_attente', 'en_cours', 'termine') DEFAULT 'en_attente',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (realisation_id) REFERENCES realisations_controle(id_realisation)
        );
        ";
        
        $this->executerScriptSQL($sql);
        echo "✓ Module Contrôle Interne & Audit créé\n";
    }
    
    private function executerScriptSQL($sql) {
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $this->db->exec($statement);
            }
        }
    }
}

// Exécution du script
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $setup = new SetupModulesSyscohada();
    $setup->creerModulesComplets();
}
?>
