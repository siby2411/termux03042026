<?php
// gestion_articles.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

// Gérer les actions CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['ajouter_article'])) {
        $code = secure_input($_POST['code_article']);
        $designation = secure_input($_POST['designation']);
        $categorie = secure_input($_POST['categorie']);
        $unite = secure_input($_POST['unite_mesure']);
        $prix = secure_input($_POST['prix_unitaire']);
        
        $sql = "INSERT INTO articles_stock 
                (code_article, designation, categorie, unite_mesure, prix_unitaire, stock_actuel, date_creation)
                VALUES (:code, :designation, :categorie, :unite, :prix, 0, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':code' => $code,
            ':designation' => $designation,
            ':categorie' => $categorie,
            ':unite' => $unite,
            ':prix' => $prix
        ]);
        
        $_SESSION['success'] = "Article ajouté avec succès!";
    }
    
    if (isset($_POST['mouvement_stock'])) {
        $code_article = secure_input($_POST['code_article']);
        $type = secure_input($_POST['type_mouvement']);
        $quantite = secure_input($_POST['quantite']);
        $motif = secure_input($_POST['motif']);
        
        // Calculer la valeur
        $sql_prix = "SELECT prix_unitaire FROM articles_stock WHERE code_article = :code";
        $stmt = $pdo->prepare($sql_prix);
        $stmt->execute([':code' => $code_article]);
        $article = $stmt->fetch();
        
        $valeur = $quantite * $article['prix_unitaire'];
        
        // Enregistrer le mouvement
        $sql_mvt = "INSERT INTO mouvements_stock 
                   (code_article, date_mouvement, type_mouvement, quantite, prix_unitaire, valeur, motif, created_at)
                   VALUES (:code, NOW(), :type, :quantite, :prix, :valeur, :motif, NOW())";
        
        $stmt = $pdo->prepare($sql_mvt);
        $stmt->execute([
            ':code' => $code_article,
            ':type' => $type,
            ':quantite' => $quantite,
            ':prix' => $article['prix_unitaire'],
            ':valeur' => $valeur,
            ':motif' => $motif
        ]);
        
        // Mettre à jour le stock actuel
        $signe = ($type == 'entree') ? '+' : '-';
        $sql_update = "UPDATE articles_stock 
                      SET stock_actuel = stock_actuel $signe :quantite
                      WHERE code_article = :code";
        
        $stmt = $pdo->prepare($sql_update);
        $stmt->execute([':quantite' => $quantite, ':code' => $code_article]);
        
        $_SESSION['success'] = "Mouvement de stock enregistré!";
    }
}

// Récupérer les articles
$sql_articles = "SELECT a.*, 
                (SELECT SUM(quantite) FROM mouvements_stock 
                 WHERE code_article = a.code_article AND type_mouvement = 'entree') as total_entrees,
                (SELECT SUM(quantite) FROM mouvements_stock 
                 WHERE code_article = a.code_article AND type_mouvement = 'sortie') as total_sorties
                FROM articles_stock a
                ORDER BY a.date_creation DESC";
$articles = $pdo->query($sql_articles)->fetchAll();

// Récupérer les mouvements récents
$sql_mouvements = "SELECT m.*, a.designation 
                  FROM mouvements_stock m
                  JOIN articles_stock a ON m.code_article = a.code_article
                  ORDER BY m.date_mouvement DESC
                  LIMIT 50";
$mouvements = $pdo->query($sql_mouvements)->fetchAll();

// Calculer la valeur totale du stock
$sql_valeur = "SELECT SUM(stock_actuel * prix_unitaire) as valeur_totale
               FROM articles_stock";
$valeur_stock = $pdo->query($sql_valeur)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Articles</title>
    <style>
        .gestion-articles-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-stock {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card-stock {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .articles-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .section-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-articles {
            width: 100%;
            border-collapse: collapse;
        }
        .table-articles th {
            background: #2c3e50;
            color: white;
            padding: 12px;
        }
        .table-articles td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .stock-faible {
            background: #ffe6e6;
        }
        .stock-normal {
            background: #e6ffe6;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .tab-container {
            margin-top: 20px;
        }
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #ddd;
        }
        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        .tab-button.active {
            border-bottom-color: #3498db;
            color: #3498db;
            font-weight: bold;
        }
        .tab-content {
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <div class="gestion-articles-container">
        <div class="page-header">
            <h1><i class="fas fa-boxes"></i> Gestion des Articles</h1>
            <p>Gestion complète du stock et des mouvements d'articles</p>
        </div>
        
        <!-- Statistiques du stock -->
        <div class="stats-stock">
            <div class="stat-card-stock">
                <h3>Valeur totale</h3>
                <p class="montant-grand"><?php echo number_format($valeur_stock, 0, ',', ' '); ?> FCFA</p>
            </div>
            <div class="stat-card-stock">
                <h3>Articles différents</h3>
                <p class="montant-grand"><?php echo count($articles); ?></p>
            </div>
            <div class="stat-card-stock">
                <h3>Mouvements ce mois</h3>
                <p class="montant-grand">
                    <?php 
                    $sql_mois = "SELECT COUNT(*) FROM mouvements_stock 
                                WHERE MONTH(date_mouvement) = MONTH(NOW())";
                    echo $pdo->query($sql_mois)->fetchColumn();
                    ?>
                </p>
            </div>
            <div class="stat-card-stock">
                <h3>Alertes stock</h3>
                <p class="montant-grand">
                    <?php 
                    $sql_alertes = "SELECT COUNT(*) FROM articles_stock 
                                   WHERE stock_actuel < stock_min";
                    echo $pdo->query($sql_alertes)->fetchColumn();
                    ?>
                </p>
            </div>
        </div>
        
        <div class="articles-grid">
            <!-- Liste des articles -->
            <div class="section-card">
                <h3><i class="fas fa-list"></i> Liste des articles</h3>
                <div class="table-responsive">
                    <table class="table-articles">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Désignation</th>
                                <th>Catégorie</th>
                                <th>Stock</th>
                                <th>Prix unitaire</th>
                                <th>Valeur</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): 
                                $stock_classe = $article['stock_actuel'] < $article['stock_min'] ? 
                                    'stock-faible' : 'stock-normal';
                                $valeur = $article['stock_actuel'] * $article['prix_unitaire'];
                            ?>
                            <tr class="<?php echo $stock_classe; ?>">
                                <td><strong><?php echo $article['code_article']; ?></strong></td>
                                <td><?php echo $article['designation']; ?></td>
                                <td><?php echo $article['categorie']; ?></td>
                                <td>
                                    <?php echo $article['stock_actuel']; ?> 
                                    <?php echo $article['unite_mesure']; ?>
                                    <?php if ($article['stock_actuel'] < $article['stock_min']): ?>
                                        <span style="color: red; font-size: 10px;">⚠ MIN</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($article['prix_unitaire'], 0, ',', ' '); ?> FCFA</td>
                                <td><?php echo number_format($valeur, 0, ',', ' '); ?> FCFA</td>
                                <td>
                                    <button onclick="gererMouvement('<?php echo $article['code_article']; ?>')" 
                                            class="btn-sm btn-primary">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <button onclick="editerArticle('<?php echo $article['code_article']; ?>')" 
                                            class="btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="voirHistorique('<?php echo $article['code_article']; ?>')" 
                                            class="btn-sm btn-secondary">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Gestion des mouvements -->
            <div class="section-card">
                <div class="tab-container">
                    <div class="tab-buttons">
                        <button class="tab-button active" onclick="ouvrirTab('ajouter-article')">
                            Ajouter article
                        </button>
                        <button class="tab-button" onclick="ouvrirTab('mouvement-stock')">
                            Mouvement stock
                        </button>
                        <button class="tab-button" onclick="ouvrirTab('inventaire')">
                            Inventaire
                        </button>
                    </div>
                    
                    <!-- Onglet Ajouter article -->
                    <div id="ajouter-article" class="tab-content" style="display: block;">
                        <h4>Nouvel article</h4>
                        <form method="POST">
                            <div class="form-grid">
                                <div>
                                    <label>Code article *</label>
                                    <input type="text" name="code_article" required 
                                           pattern="[A-Z0-9]{3,20}">
                                </div>
                                <div>
                                    <label>Catégorie</label>
                                    <select name="categorie">
                                        <option value="MATERIEL">Matériel</option>
                                        <option value="FOURNITURE">Fourniture</option>
                                        <option value="PRODUIT">Produit</option>
                                        <option value="EMBALLAGE">Emballage</option>
                                    </select>
                                </div>
                                <div class="full-width">
                                    <label>Désignation *</label>
                                    <input type="text" name="designation" required>
                                </div>
                                <div>
                                    <label>Unité de mesure</label>
                                    <select name="unite_mesure">
                                        <option value="PIECE">Pièce</option>
                                        <option value="KILO">Kilo</option>
                                        <option value="LITRE">Litre</option>
                                        <option value="MOTRE">Mètre</option>
                                        <option value="CARTON">Carton</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Prix unitaire (FCFA)</label>
                                    <input type="number" name="prix_unitaire" 
                                           step="0.01" min="0" required>
                                </div>
                                <div>
                                    <label>Stock minimum</label>
                                    <input type="number" name="stock_min" min="0" value="0">
                                </div>
                                <div>
                                    <label>Stock maximum</label>
                                    <input type="number" name="stock_max" min="0" value="100">
                                </div>
                            </div>
                            <button type="submit" name="ajouter_article" 
                                    class="btn btn-success">
                                <i class="fas fa-save"></i> Enregistrer l'article
                            </button>
                        </form>
                    </div>
                    
                    <!-- Onglet Mouvement stock -->
                    <div id="mouvement-stock" class="tab-content" style="display: none;">
                        <h4>Mouvement de stock</h4>
                        <form method="POST">
                            <div class="form-grid">
                                <div>
                                    <label>Article *</label>
                                    <select name="code_article" id="select-article" required>
                                        <option value="">Sélectionner un article</option>
                                        <?php foreach ($articles as $article): ?>
                                        <option value="<?php echo $article['code_article']; ?>">
                                            <?php echo $article['code_article']; ?> - 
                                            <?php echo $article['designation']; ?>
                                            (Stock: <?php echo $article['stock_actuel']; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Type de mouvement *</label>
                                    <select name="type_mouvement" required>
                                        <option value="entree">Entrée en stock</option>
                                        <option value="sortie">Sortie de stock</option>
                                        <option value="inventaire">Ajustement inventaire</option>
                                        <option value="retour">Retour client</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Quantité *</label>
                                    <input type="number" name="quantite" 
                                           step="0.01" min="0.01" required>
                                </div>
                                <div class="full-width">
                                    <label>Motif / Référence</label>
                                    <input type="text" name="motif" 
                                           placeholder="N° bon de livraison, facture...">
                                </div>
                            </div>
                            <button type="submit" name="mouvement_stock" 
                                    class="btn btn-primary">
                                <i class="fas fa-exchange-alt"></i> Enregistrer le mouvement
                            </button>
                        </form>
                    </div>
                    
                    <!-- Onglet Inventaire -->
                    <div id="inventaire" class="tab-content" style="display: none;">
                        <h4>Inventaire physique</h4>
                        <div class="inventaire-interface">
                            <p>Générez une fiche d'inventaire pour comptage physique.</p>
                            <button onclick="genererFicheInventaire()" 
                                    class="btn btn-warning">
                                <i class="fas fa-clipboard-list"></i> Générer fiche inventaire
                            </button>
                            <button onclick="importerComptage()" 
                                    class="btn btn-info">
                                <i class="fas fa-file-upload"></i> Importer comptage
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Mouvements récents -->
                <h4 style="margin-top: 30px;"><i class="fas fa-history"></i> Mouvements récents</h4>
                <div class="mouvements-recents">
                    <?php foreach ($mouvements as $mvt): ?>
                    <div class="mouvement-item">
                        <span class="date"><?php echo date('d/m H:i', strtotime($mvt['date_mouvement'])); ?></span>
                        <span class="article"><?php echo $mvt['designation']; ?></span>
                        <span class="type <?php echo $mvt['type_mouvement']; ?>">
                            <?php echo strtoupper($mvt['type_mouvement']); ?>
                        </span>
                        <span class="quantite"><?php echo $mvt['quantite']; ?></span>
                        <span class="valeur"><?php echo number_format($mvt['valeur'], 0, ',', ' '); ?> FCFA</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function ouvrirTab(tabId) {
        // Masquer tous les onglets
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = 'none';
        });
        
        // Désactiver tous les boutons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        // Afficher l'onglet sélectionné
        document.getElementById(tabId).style.display = 'block';
        event.target.classList.add('active');
    }
    
    function gererMouvement(codeArticle) {
        // Pré-remplir le formulaire de mouvement
        document.getElementById('select-article').value = codeArticle;
        ouvrirTab('mouvement-stock');
        document.getElementById('mouvement-stock').scrollIntoView();
    }
    
    function editerArticle(codeArticle) {
        window.location.href = `edit_article.php?code=${codeArticle}`;
    }
    
    function voirHistorique(codeArticle) {
        window.open(`historique_stock.php?code=${codeArticle}`, '_blank');
    }
    
    function genererFicheInventaire() {
        window.open('fiche_inventaire.php', '_blank');
    }
    </script>
</body>
</html>
