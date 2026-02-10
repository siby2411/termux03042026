<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Récupérer clients et services
$clients = $conn->query("SELECT * FROM clients ORDER BY nom, prenom")->fetchAll();
$services = $conn->query("SELECT * FROM services ORDER BY nom")->fetchAll();

// Créer une commande
if($_POST && isset($_POST['creer_commande'])) {
    try {
        $conn->beginTransaction();
        
        // Créer la commande
        $stmt = $conn->prepare("INSERT INTO commandes (client_id, date_recuperation_prevue, notes) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['client_id'], $_POST['date_recuperation_prevue'], $_POST['notes']]);
        $commande_id = $conn->lastInsertId();
        
        // Ajouter les articles
        $total_ht = 0;
        if(isset($_POST['service_id'])) {
            foreach($_POST['service_id'] as $key => $service_id) {
                $quantite = $_POST['quantite'][$key];
                $service = $conn->query("SELECT prix FROM services WHERE id = $service_id")->fetch();
                $sous_total = $service['prix'] * $quantite;
                $total_ht += $sous_total;
                
                $stmt = $conn->prepare("INSERT INTO commande_articles (commande_id, service_id, quantite, prix_unitaire, sous_total) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$commande_id, $service_id, $quantite, $service['prix'], $sous_total]);
            }
        }
        
        // Calculer les totaux
        $tva = $total_ht * 0.2; // 20% de TVA
        $total_ttc = $total_ht + $tva;
        
        $stmt = $conn->prepare("UPDATE commandes SET total_ht = ?, tva = ?, total_ttc = ?, reste_a_payer = ? WHERE id = ?");
        $stmt->execute([$total_ht, $tva, $total_ttc, $total_ttc, $commande_id]);
        
        $conn->commit();
        header("Location: commandes.php");
        exit;
        
    } catch(Exception $e) {
        $conn->rollBack();
        echo "Erreur: " . $e->getMessage();
    }
}
?>

<div class="card">
    <h2>Nouvelle commande</h2>
    <form method="POST" id="commandeForm">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Client</label>
                <select name="client_id" required>
                    <option value="">Sélectionner un client</option>
                    <?php foreach($clients as $client): ?>
                    <option value="<?= $client['id'] ?>"><?= $client['prenom'] . ' ' . $client['nom'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date récupération prévue</label>
                <input type="date" name="date_recuperation_prevue" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Services</label>
            <div id="services-container">
                <div class="service-ligne" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 0.5rem; margin-bottom: 0.5rem; align-items: end;">
                    <select name="service_id[]" class="service-select" required>
                        <option value="">Choisir un service</option>
                        <?php foreach($services as $service): ?>
                        <option value="<?= $service['id'] ?>" data-prix="<?= $service['prix'] ?>">
                            <?= $service['nom'] ?> - <?= number_format($service['prix'], 2, ',', ' ') ?> €
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantite[]" value="1" min="1" class="quantite" required>
                    <input type="text" class="prix-unitaire" readonly placeholder="Prix unitaire">
                    <input type="text" class="sous-total" readonly placeholder="Sous-total">
                    <button type="button" class="btn btn-danger supprimer-service" style="padding: 0.5rem;">×</button>
                </div>
            </div>
            <button type="button" id="ajouter-service" class="btn btn-primary">+ Ajouter un service</button>
        </div>
        
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <h3>Total: <span id="total-commande">0,00</span> €</h3>
        </div>
        
        <button type="submit" name="creer_commande" class="btn btn-success">Créer la commande</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter un service
    document.getElementById('ajouter-service').addEventListener('click', function() {
        const container = document.getElementById('services-container');
        const newLigne = container.firstElementChild.cloneNode(true);
        newLigne.querySelectorAll('input').forEach(input => input.value = '');
        newLigne.querySelector('.quantite').value = '1';
        newLigne.querySelector('.service-select').selectedIndex = 0;
        container.appendChild(newLigne);
        attachEventListeners(newLigne);
    });

    // Attacher les événements
    function attachEventListeners(ligne) {
        ligne.querySelector('.supprimer-service').addEventListener('click', function() {
            if(document.querySelectorAll('.service-ligne').length > 1) {
                ligne.remove();
                calculerTotal();
            }
        });

        ligne.querySelector('.service-select').addEventListener('change', function() {
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
        const prix = parseFloat(ligne.querySelector('.service-select').options[ligne.querySelector('.service-select').selectedIndex]?.dataset.prix) || 0;
        const sousTotal = quantite * prix;
        ligne.querySelector('.sous-total').value = sousTotal.toFixed(2) + ' €';
        calculerTotal();
    }

    function calculerTotal() {
        let total = 0;
        document.querySelectorAll('.service-ligne').forEach(ligne => {
            const quantite = parseInt(ligne.querySelector('.quantite').value) || 0;
            const prix = parseFloat(ligne.querySelector('.service-select').options[ligne.querySelector('.service-select').selectedIndex]?.dataset.prix) || 0;
            total += quantite * prix;
        });
        document.getElementById('total-commande').textContent = total.toFixed(2).replace('.', ',');
    }

    // Attacher les événements initiaux
    document.querySelectorAll('.service-ligne').forEach(attachEventListeners);
});
</script>

<?php include 'footer.php'; ?>
