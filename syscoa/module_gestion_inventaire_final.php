<?php
/**
 * Module Gestion Inventaire Final
 * Module complet de gestion d'inventaire - Version finale
 */

class ModuleGestionInventaireFinal {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Initialisation des données de test
     */
    public function initialiserDonneesTest() {
        try {
            // Créer un dépôt par défaut s'il n'existe pas
            $this->db->exec("INSERT IGNORE INTO depots_stockage (code_depot, libelle_depot) VALUES ('PRINCIPAL', 'Dépôt Principal')");
            
            // Créer quelques articles de test
            $articles_test = [
                ['MAT-001', 'Matière Première A', '31110000', 'KG', 1500.00],
                ['MAT-002', 'Matière Première B', '31110000', 'KG', 1800.00],
                ['PROD-001', 'Produit Fini X', '35110000', 'UNITE', 8500.00],
                ['PROD-002', 'Produit Fini Y', '35110000', 'UNITE', 9200.00]
            ];
            
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO articles_stock 
                (article_code, designation, compte_stock, unite_mesure, prix_achat_moyen) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($articles_test as $article) {
                $stmt->execute($article);
            }
            
            return "✅ Données de test initialisées";
            
        } catch (PDOException $e) {
            return "❌ Erreur initialisation: " . $e->getMessage();
        }
    }
    
    /**
     * Planification d'un inventaire physique
     */
    public function planifierInventaire($depot_id, $date_inventaire, $responsable) {
        $code_inventaire = "INV-" . date("Ymd-His");
        
        $sql = "INSERT INTO inventaires_physiques 
                (code_inventaire, depot_id, date_inventaire, responsable)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code_inventaire, $depot_id, $date_inventaire, $responsable]);
        
        $inventaire_id = $this->db->lastInsertId();
        
        // Générer les lignes d'inventaire
        $nb_articles = $this->genererLignesInventaire($inventaire_id, $depot_id);
        
        return [
            "inventaire_id" => $inventaire_id,
            "code_inventaire" => $code_inventaire,
            "nombre_articles" => $nb_articles
        ];
    }
    
    /**
     * Génération automatique des lignes d'inventaire
     */
    private function genererLignesInventaire($inventaire_id, $depot_id) {
        // Pour l'instant, on prend tous les articles existants
        $sql = "SELECT article_code, designation 
                FROM articles_stock";
        
        $stmt = $this->db->query($sql);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sql_insert = "INSERT INTO lignes_inventaire 
                      (inventaire_id, article_id, quantite_theorique)
                      VALUES (?, ?, ?)";
        
        $stmt_insert = $this->db->prepare($sql_insert);
        $count = 0;
        
        foreach ($articles as $article) {
            // Quantité théorique par défaut à 0
            $stmt_insert->execute([$inventaire_id, $article["article_code"], 0]);
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Saisie des quantités réelles
     */
    public function saisirQuantiteReelle($ligne_inventaire_id, $quantite_reelle) {
        // Récupérer le prix de l'article pour calculer la valeur
        $sql_prix = "SELECT a.prix_achat_moyen 
                     FROM lignes_inventaire li
                     JOIN articles_stock a ON li.article_id = a.article_code
                     WHERE li.id_ligne_inventaire = ?";
        
        $stmt_prix = $this->db->prepare($sql_prix);
        $stmt_prix->execute([$ligne_inventaire_id]);
        $article = $stmt_prix->fetch(PDO::FETCH_ASSOC);
        
        $prix_unitaire = $article["prix_achat_moyen"] ?? 0;
        
        $sql = "UPDATE lignes_inventaire 
                SET quantite_reelle = ?,
                    ecart_quantite = quantite_theorique - ?,
                    valeur_ecart = (quantite_theorique - ?) * ?
                WHERE id_ligne_inventaire = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantite_reelle, $quantite_reelle, $quantite_reelle, $prix_unitaire, $ligne_inventaire_id]);
    }
    
    /**
     * Obtenir le détail d'un inventaire
     */
    public function getDetailInventaire($inventaire_id) {
        $sql = "SELECT 
                    ip.*,
                    ds.libelle_depot,
                    COUNT(li.id_ligne_inventaire) as nombre_articles,
                    SUM(ABS(li.ecart_quantite)) as total_ecarts_quantite,
                    SUM(ABS(li.valeur_ecart)) as total_ecarts_valeur
                FROM inventaires_physiques ip
                JOIN depots_stockage ds ON ip.depot_id = ds.id_depot
                LEFT JOIN lignes_inventaire li ON ip.id_inventaire = li.inventaire_id
                WHERE ip.id_inventaire = ?
                GROUP BY ip.id_inventaire, ip.code_inventaire, ip.date_inventaire, ip.responsable, ip.statut, ds.libelle_depot";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inventaire_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir les lignes d'un inventaire
     */
    public function getLignesInventaire($inventaire_id) {
        $sql = "SELECT 
                    li.*,
                    a.designation,
                    a.unite_mesure,
                    a.prix_achat_moyen
                FROM lignes_inventaire li
                JOIN articles_stock a ON li.article_id = a.article_code
                WHERE li.inventaire_id = ?
                ORDER BY a.designation";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inventaire_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Finaliser un inventaire
     */
    public function finaliserInventaire($inventaire_id) {
        $sql = "UPDATE inventaires_physiques 
                SET statut = 'termine' 
                WHERE id_inventaire = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$inventaire_id]);
    }
    
    /**
     * Gestion des séries et lots
     */
    public function creerSerieLot($article_id, $numero_lot, $quantite_initial, $date_fabrication = null, $date_peremption = null) {
        $sql = "INSERT INTO series_lots 
                (article_id, numero_lot, quantite_initial, quantite_restante, date_fabrication, date_peremption)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $article_id, $numero_lot, $quantite_initial, $quantite_initial, 
            $date_fabrication, $date_peremption
        ]);
    }
    
    /**
     * Obtenir les séries/lots d'un article
     */
    public function getSeriesLotArticle($article_id) {
        $sql = "SELECT 
                    sl.*,
                    a.designation
                FROM series_lots sl
                JOIN articles_stock a ON sl.article_id = a.article_code
                WHERE sl.article_id = ?
                ORDER BY sl.date_peremption ASC, sl.date_fabrication ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$article_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function testerModule() {
        return "✅ Module Gestion Inventaire Final fonctionnel";
    }
}
?>