<?php 
require_once 'config.php'; 
include 'header.php';
$db = new Database();
$conn = $db->getConnection();

if (!$conn) die("<div class='alert alert-danger'>Erreur de connexion base de données.</div>");

// 1. Logique d'Insertion (Votre code existant optimisé)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_commande'])) {
    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO commandes (client_id, date_recuperation_prevue, notes, reste_a_payer) VALUES (?, ?, ?, 0)");
        $stmt->execute([$_POST['client_id'], $_POST['date_recuperation_prevue'], $_POST['notes']]);
        $commande_id = $conn->lastInsertId();

        $total_ht = 0;
        if(isset($_POST['service_id'])) {
            foreach($_POST['service_id'] as $key => $service_id) {
                $quantite = (int)$_POST['quantite'][$key];
                $s_stmt = $conn->prepare("SELECT prix FROM services WHERE id = ?");
                $s_stmt->execute([$service_id]);
                $service = $s_stmt->fetch();
                
                $sous_total = $service['prix'] * $quantite;
                $total_ht += $sous_total;

                $stmt_art = $conn->prepare("INSERT INTO commande_articles (commande_id, service_id, quantite, prix_unitaire, sous_total) VALUES (?, ?, ?, ?, ?)");
                $stmt_art->execute([$commande_id, $service_id, $quantite, $service['prix'], $sous_total]);
            }
        }

        $tva = $total_ht * 0.18; // TVA 18% (Sénégal)
        $total_ttc = $total_ht + $tva;

        $upd = $conn->prepare("UPDATE commandes SET total_ht = ?, tva = ?, total_ttc = ?, reste_a_payer = ? WHERE id = ?");
        $upd->execute([$total_ht, $tva, $total_ttc, $total_ttc, $commande_id]);

        $conn->commit();
        echo "<script>window.location.href='commandes.php?success=1';</script>";
    } catch(Exception $e) {
        $conn->rollBack();
        echo "<div class='alert alert-danger'>Erreur: " . $e->getMessage() . "</div>";
    }
}

// 2. Récupération des données pour le formulaire
$clients = $conn->query("SELECT * FROM clients ORDER BY nom ASC")->fetchAll();
$services = $conn->query("SELECT * FROM services ORDER BY nom ASC")->fetchAll();

// 3. Récupération des commandes existantes pour la liste
$liste_commandes = $conn->query("
    SELECT c.*, cl.nom, cl.prenom 
    FROM commandes c 
    JOIN clients cl ON c.client_id = cl.id 
    ORDER BY c.date_commande DESC
")->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm p-4">
                <h4 class="mb-3 text-primary"><i class="bi bi-plus-circle me-2"></i>Nouvelle Commande</h4>
                <form method="POST" id="commandeForm">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= $client['prenom'] . ' ' . $client['nom'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de retrait prévue</label>
                        <input type="date" name="date_recuperation_prevue" class="form-control" required>
                    </div>

                    <div id="services-container" class="mb-3">
                        <label class="form-label d-block">Services</label>
                        <div class="service-ligne row g-2 mb-2">
                            <div class="col-7">
                                <select name="service_id[]" class="form-select service-select" required>
                                    <option value="">Service...</option>
                                    <?php foreach($services as $s): ?>
                                        <option value="<?= $s['id'] ?>" data-prix="<?= $s['prix'] ?>"><?= $s['nom'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-3">
                                <input type="number" name="quantite[]" value="1" min="1" class="form-control quantite">
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-outline-danger w-100 supprimer-service">×</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="ajouter-service" class="btn btn-sm btn-outline-primary mb-3">+ Ajouter service</button>
                    
                    <div class="h4 text-end text-success">Total: <span id="total-commande">0</span> FCFA</div>
                    <button type="submit" name="creer_commande" class="btn btn-primary w-100 btn-lg mt-3">Enregistrer Commande</button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Journal des Commandes</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Client</th><th>Prévu le</th><th>Total</th><th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($liste_commandes as $cmd): ?>
                            <tr>
                                <td><strong><?= $cmd['prenom'].' '.$cmd['nom'] ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($cmd['date_recuperation_prevue'])) ?></td>
                                <td class="fw-bold"><?= number_format($cmd['total_ttc'], 0, ',', ' ') ?></td>
                                <td><span class="badge bg-warning"><?= $cmd['statut'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Votre logique JS ici (identique à votre code mais adaptée aux classes CSS)
document.getElementById('ajouter-service').addEventListener('click', function() {
    const container = document.getElementById('services-container');
    const firstLigne = container.querySelector('.service-ligne');
    const newLigne = firstLigne.cloneNode(true);
    newLigne.querySelector('.service-select').selectedIndex = 0;
    newLigne.querySelector('.quantite').value = 1;
    container.appendChild(newLigne);
});

document.addEventListener('change', function(e) {
    if(e.target.classList.contains('service-select') || e.target.classList.contains('quantite')) {
        let total = 0;
        document.querySelectorAll('.service-ligne').forEach(ligne => {
            const s = ligne.querySelector('.service-select');
            const p = s.options[s.selectedIndex].dataset.prix || 0;
            const q = ligne.querySelector('.quantite').value || 0;
            total += (parseFloat(p) * parseInt(q));
        });
        document.getElementById('total-commande').textContent = total.toLocaleString('fr-FR');
    }
});

document.addEventListener('click', function(e) {
    if(e.target.classList.contains('supprimer-service')) {
        const lignes = document.querySelectorAll('.service-ligne');
        if(lignes.length > 1) e.target.closest('.service-ligne').remove();
    }
});
</script>
<?php include 'footer.php'; ?>
