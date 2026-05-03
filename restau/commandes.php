<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Récupérer clients et plats
$clients = $conn->query("SELECT * FROM clients ORDER BY nom, prenom")->fetchAll();
$plats = $conn->query("SELECT * FROM plats WHERE disponible = 1 ORDER BY nom")->fetchAll();

// Créer une commande
if($_POST && isset($_POST['creer_commande'])) {
    try {
        $conn->beginTransaction();
        
        // Créer la commande
        $stmt = $conn->prepare("INSERT INTO commandes (client_id, type_commande, instructions, heure_souhaitee, adresse_livraison) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['client_id'], 
            $_POST['type_commande'], 
            $_POST['instructions'],
            $_POST['heure_souhaitee'],
            $_POST['adresse_livraison']
        ]);
        $commande_id = $conn->lastInsertId();
        
        // Ajouter les articles
        $total_ht = 0;
        if(isset($_POST['plat_id'])) {
            foreach($_POST['plat_id'] as $key => $plat_id) {
                $quantite = $_POST['quantite'][$key];
                $instructions = $_POST['instructions_speciales'][$key];
                $plat = $conn->query("SELECT prix FROM plats WHERE id = $plat_id")->fetch();
                $sous_total = $plat['prix'] * $quantite;
                $total_ht += $sous_total;
                
                $stmt = $conn->prepare("INSERT INTO commande_articles (commande_id, plat_id, quantite, prix_unitaire, sous_total, instructions_speciales) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$commande_id, $plat_id, $quantite, $plat['prix'], $sous_total, $instructions]);
            }
        }
        
        // Calculer les totaux
        $tva = $total_ht * 0.1; // 10% de TVA restauration
        $total_ttc = $total_ht + $tva;
        
        $stmt = $conn->prepare("UPDATE commandes SET total_ht = ?, tva = ?, total_ttc = ? WHERE id = ?");
        $stmt->execute([$total_ht, $tva, $total_ttc, $commande_id]);
        
        $conn->commit();
        echo '<div class="alert alert-success">✅ Commande créée avec succès!</div>';
        
    } catch(Exception $e) {
        $conn->rollBack();
        echo '<div class="alert alert-error">❌ Erreur: ' . $e->getMessage() . '</div>';
    }
}

// Changer statut commande
if(isset($_GET['changer_statut'])) {
    $commande_id = intval($_GET['changer_statut']);
    $nouveau_statut = $_GET['vers'];
    
    try {
        $stmt = $conn->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
        $stmt->execute([$nouveau_statut, $commande_id]);
        echo '<div class="alert alert-success">✅ Statut de la commande mis à jour!</div>';
    } catch(Exception $e) {
        echo '<div class="alert alert-error">❌ Erreur: ' . $e->getMessage() . '</div>';
    }
}

// Récupérer les commandes
$query = $conn->query("
    SELECT c.*, cl.nom, cl.prenom, cl.telephone,
           COUNT(ca.id) as nb_articles,
           GROUP_CONCAT(DISTINCT p.nom SEPARATOR ', ') as plats_noms
    FROM commandes c 
    LEFT JOIN clients cl ON c.client_id = cl.id 
    LEFT JOIN commande_articles ca ON c.id = ca.commande_id
    LEFT JOIN plats p ON ca.plat_id = p.id
    GROUP BY c.id
    ORDER BY c.date_commande DESC
");
$commandes = $query->fetchAll();
?>

<div class="card">
    <h2>📦 Nouvelle Commande</h2>
    <form method="POST" id="commandeForm">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Client</label>
                <select name="client_id" required>
                    <option value="">Sélectionner un client</option>
                    <?php foreach($clients as $client): ?>
                    <option value="<?= $client['id'] ?>"><?= $client['prenom'] . ' ' . $client['nom'] ?> - <?= $client['telephone'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Type de commande</label>
                <select name="type_commande" required>
                    <option value="sur_place">🍽️ Sur place</option>
                    <option value="a_emporter">🥡 À emporter</option>
                    <option value="livraison">🚗 Livraison</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Heure souhaitée</label>
                <input type="time" name="heure_souhaitee" value="<?= date('H:i') ?>">
            </div>
            <div class="form-group" id="adresse-livraison" style="display: none;">
                <label>Adresse de livraison</label>
                <textarea name="adresse_livraison" rows="2" placeholder="Adresse complète..."></textarea>
            </div>
        </div>
        
        <div class="form-group">
            <label>Plats commandés</label>
            <div id="plats-container">
                <div class="plat-ligne" style="display: grid; grid-template-columns: 2fr 1fr 1fr 2fr auto; gap: 0.5rem; margin-bottom: 0.5rem; align-items: end;">
                    <select name="plat_id[]" class="plat-select" required>
                        <option value="">Choisir un plat</option>
                        <?php foreach($plats as $plat): ?>
                        <option value="<?= $plat['id'] ?>" data-prix="<?= $plat['prix'] ?>">
                            <?= $plat['nom'] ?> - <?= number_format($plat['prix'], 2, ',', ' ') ?> €
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantite[]" value="1" min="1" class="quantite" required>
                    <input type="text" class="prix-unitaire" readonly placeholder="Prix unitaire">
                    <input type="text" name="instructions_speciales[]" placeholder="Instructions spéciales">
                    <button type="button" class="btn btn-danger supprimer-plat" style="padding: 0.5rem;">×</button>
                </div>
            </div>
            <button type="button" id="ajouter-plat" class="btn btn-primary">+ Ajouter un plat</button>
        </div>
        
        <div class="form-group">
            <label>Instructions générales</label>
            <textarea name="instructions" rows="3" placeholder="Instructions pour la commande..."></textarea>
        </div>
        
        <div class="form-group">
            <h3>Total: <span id="total-commande">0,00</span> €</h3>
        </div>
        
        <button type="submit" name="creer_commande" class="btn btn-success">✅ Créer la commande</button>
    </form>
</div>

<div class="card">
    <h2>📋 Liste des Commandes</h2>
    
    <!-- Filtres -->
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
        <div>
            <input type="text" id="searchInput" placeholder="🔍 Rechercher..." style="width: 100%;">
        </div>
        <div>
            <select id="statutFilter" style="width: 100%;">
                <option value="">Tous statuts</option>
                <option value="en_attente">En attente</option>
                <option value="en_preparation">En préparation</option>
                <option value="pret">Prêt</option>
                <option value="livraison">Livraison</option>
                <option value="termine">Terminé</option>
            </select>
        </div>
        <div>
            <select id="typeFilter" style="width: 100%;">
                <option value="">Tous types</option>
                <option value="sur_place">Sur place</option>
                <option value="a_emporter">À emporter</option>
                <option value="livraison">Livraison</option>
            </select>
        </div>
        <div>
            <button onclick="appliquerFiltres()" class="btn btn-primary">Filtrer</button>
        </div>
    </div>

    <div class="table-container">
        <table id="tableCommandes">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Plats</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($commandes as $commande): ?>
                <tr class="commande-row" 
                    data-statut="<?= $commande['statut'] ?>"
                    data-type="<?= $commande['type_commande'] ?>"
                    data-client="<?= htmlspecialchars(strtolower($commande['prenom'] . ' ' . $commande['nom'])) ?>">
                    <td><strong>#<?= $commande['id'] ?></strong></td>
                    <td>
                        <?= $commande['prenom'] . ' ' . $commande['nom'] ?>
                        <?php if($commande['telephone']): ?>
                            <br><small><?= $commande['telephone'] ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            <?= match($commande['type_commande']) {
                                'sur_place' => '🍽️ Sur place',
                                'a_emporter' => '🥡 À emporter', 
                                'livraison' => '🚗 Livraison'
                            } ?>
                        </span>
                    </td>
                    <td>
                        <small><?= $commande['plats_noms'] ? substr($commande['plats_noms'], 0, 50) . (strlen($commande['plats_noms']) > 50 ? '...' : '') : 'Aucun plat' ?></small>
                        <br><small><?= $commande['nb_articles'] ?> article(s)</small>
                    </td>
                    <td><strong><?= number_format($commande['total_ttc'], 2, ',', ' ') ?> €</strong></td>
                    <td>
                        <span class="badge status-<?= $commande['statut'] ?>">
                            <?= str_replace('_', ' ', $commande['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <?= date('H:i', strtotime($commande['date_commande'])) ?><br>
                        <small><?= date('d/m', strtotime($commande['date_commande'])) ?></small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                            <?php if($commande['statut'] == 'en_attente'): ?>
                                <a href="commandes.php?changer_statut=<?= $commande['id'] ?>&vers=en_preparation" class="btn btn-success btn-sm">👨‍🍳 Préparer</a>
                            <?php elseif($commande['statut'] == 'en_preparation'): ?>
                                <a href="commandes.php?changer_statut=<?= $commande['id'] ?>&vers=pret" class="btn btn-warning btn-sm">✅ Prêt</a>
                            <?php elseif($commande['statut'] == 'pret'): ?>
                                <a href="commandes.php?changer_statut=<?= $commande['id'] ?>&vers=termine" class="btn btn-primary btn-sm">📦 Servir</a>
                            <?php endif; ?>
                            <a href="factures.php?commande_id=<?= $commande['id'] ?>" class="btn btn-info btn-sm">🧾 Facture</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Gestion du type de commande
document.querySelector('select[name="type_commande"]').addEventListener('change', function() {
    const adresseDiv = document.getElementById('adresse-livraison');
    adresseDiv.style.display = this.value === 'livraison' ? 'block' : 'none';
});

// Ajouter un plat
document.getElementById('ajouter-plat').addEventListener('click', function() {
    const container = document.getElementById('plats-container');
    const newLigne = container.firstElementChild.cloneNode(true);
    newLigne.querySelectorAll('input').forEach(input => {
        if(input.type !== 'number') input.value = '';
    });
    newLigne.querySelector('.quantite').value = '1';
    newLigne.querySelector('.plat-select').selectedIndex = 0;
    container.appendChild(newLigne);
    attachEventListeners(newLigne);
});

function attachEventListeners(ligne) {
    ligne.querySelector('.supprimer-plat').addEventListener('click', function() {
        if(document.querySelectorAll('.plat-ligne').length > 1) {
            ligne.remove();
            calculerTotal();
        }
    });

    ligne.querySelector('.plat-select').addEventListener('change', function() {
        const prix = this.options[this.selectedIndex]?.dataset.prix || 0;
        ligne.querySelector('.prix-unitaire').value = parseFloat(prix).toFixed(2) + ' €';
        calculerSousTotal(ligne);
    });

    ligne.querySelector('.quantite').addEventListener('input', function() {
        calculerSousTotal(ligne);
    });
}

function calculerSousTotal(ligne) {
    const quantite = parseInt(ligne.querySelector('.quantite').value) || 0;
    const prix = parseFloat(ligne.querySelector('.plat-select').options[ligne.querySelector('.plat-select').selectedIndex]?.dataset.prix) || 0;
    const sousTotal = quantite * prix;
    calculerTotal();
}

function calculerTotal() {
    let total = 0;
    document.querySelectorAll('.plat-ligne').forEach(ligne => {
        const quantite = parseInt(ligne.querySelector('.quantite').value) || 0;
        const prix = parseFloat(ligne.querySelector('.plat-select').options[ligne.querySelector('.plat-select').selectedIndex]?.dataset.prix) || 0;
        total += quantite * prix;
    });
    document.getElementById('total-commande').textContent = total.toFixed(2).replace('.', ',');
}

// Filtrage des commandes
function appliquerFiltres() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statut = document.getElementById('statutFilter').value;
    const type = document.getElementById('typeFilter').value;
    
    const rows = document.querySelectorAll('.commande-row');
    
    rows.forEach(row => {
        const client = row.getAttribute('data-client');
        const rowStatut = row.getAttribute('data-statut');
        const rowType = row.getAttribute('data-type');
        
        const matchSearch = client.includes(searchTerm);
        const matchStatut = !statut || rowStatut === statut;
        const matchType = !type || rowType === type;
        
        if (matchSearch && matchStatut && matchType) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Attacher les événements initiaux
document.querySelectorAll('.plat-ligne').forEach(attachEventListeners);
document.getElementById('searchInput').addEventListener('input', appliquerFiltres);
document.getElementById('statutFilter').addEventListener('change', appliquerFiltres);
document.getElementById('typeFilter').addEventListener('change', appliquerFiltres);
</script>

<style>
.plat-ligne {
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

.table-container {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .plat-ligne {
        grid-template-columns: 1fr !important;
        gap: 0.5rem;
    }
    
    .table-container {
        font-size: 0.9rem;
    }
}
</style>

<?php include 'footer.php'; ?>
