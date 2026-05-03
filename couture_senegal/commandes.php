<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// ── TRAITEMENTS POST ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    if ($d['form_action'] === 'create') {
        $num = "CMD-" . date('Ymd') . "-" . rand(10, 99);
        $reste = $d['total_ttc'] - $d['acompte_verse'];
        
        $stmt = $pdo->prepare("INSERT INTO commandes (numero_commande, client_id, date_commande, date_livraison, priorite, notes, total_ttc, acompte_verse, reste_a_payer, statut) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$num, $d['client_id'], $d['date_commande'], $d['date_livraison'], $d['priorite'], $d['notes'], $d['total_ttc'], $d['acompte_verse'], $reste, 'confirmée']);
        
        $cmd_id = $pdo->lastInsertId();
        
        // Création automatique de la facture liée
        $num_fac = str_replace('CMD', 'FAC', $num);
        $stmtFac = $pdo->prepare("INSERT INTO factures (numero_facture, commande_id, client_id, date_facture, montant_ttc, montant_paye, reste, statut) VALUES (?,?,?,?,?,?,?,?)");
        $stmtFac->execute([$num_fac, $cmd_id, $d['client_id'], $d['date_commande'], $d['total_ttc'], $d['acompte_verse'], $reste, ($reste <= 0 ? 'payée' : 'payée_partiel')]);

        setFlash('success', "Commande $num créée avec succès !");
        header('Location: commandes.php'); exit;
    }
}

// ── DONNÉES ───────────────────────────────────────────
$commandes = $pdo->query("SELECT c.*, cl.nom, cl.prenom FROM commandes c JOIN clients cl ON c.client_id = cl.id ORDER BY c.date_commande DESC LIMIT 20")->fetchAll();
$clients = $pdo->query("SELECT id, nom, prenom FROM clients WHERE statut='actif' ORDER BY nom")->fetchAll();
$modeles = $pdo->query("SELECT id, nom, prix_base FROM modeles WHERE actif=1 ORDER BY nom")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark"><i class="bi bi-scissors me-2 text-primary"></i>Carnet de Commandes</h4>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCmd">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle Commande
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small fw-bold">
                <tr>
                    <th class="ps-4">N° Commande</th>
                    <th>Client</th>
                    <th>Livraison Prévue</th>
                    <th>Total</th>
                    <th>Acompte</th>
                    <th>Statut</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $c): ?>
                <tr>
                    <td class="ps-4"><span class="fw-bold"><?= $c['numero_commande'] ?></span></td>
                    <td><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
                    <td>
                        <?php 
                        $delay = (strtotime($c['date_livraison']) - time()) / 86400;
                        $class = ($delay < 3 && $c['statut'] != 'livrée') ? 'text-danger fw-bold' : '';
                        ?>
                        <span class="<?= $class ?>"><?= date('d/m/Y', strtotime($c['date_livraison'])) ?></span>
                    </td>
                    <td class="fw-bold"><?= number_format($c['total_ttc'], 0, ',', ' ') ?></td>
                    <td class="text-success small"><?= number_format($c['acompte_verse'], 0, ',', ' ') ?></td>
                    <td>
                        <span class="badge rounded-pill bg-<?= $c['statut'] == 'terminée' ? 'success' : ($c['statut'] == 'en_cours' ? 'warning text-dark' : 'info') ?>">
                            <?= ucfirst(str_replace('_', ' ', $c['statut'])) ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="factures.php?q=<?= $c['numero_commande'] ?>" class="btn btn-sm btn-light border" title="Voir Facture"><i class="bi bi-file-earmark-text"></i></a>
                        <button class="btn btn-sm btn-light border text-primary"><i class="bi bi-eye"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalCmd" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content border-0 shadow-lg">
      <input type="hidden" name="form_action" value="create">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold">Nouvelle Prise de Mesure / Commande</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 bg-white rounded shadow-sm">
                    <label class="form-label small fw-bold">Client *</label>
                    <select name="client_id" class="form-select mb-3" required>
                        <option value="">-- Sélectionner le client --</option>
                        <?php foreach($clients as $cl): ?>
                            <option value="<?= $cl['id'] ?>"><?= $cl['nom'] ?> <?= $cl['prenom'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label class="form-label small fw-bold">Modèle de référence</label>
                    <select id="selectModele" class="form-select" onchange="updatePrix()">
                        <option value="0" data-prix="0">-- Modèle sur mesure --</option>
                        <?php foreach($modeles as $mo): ?>
                            <option value="<?= $mo['id'] ?>" data-prix="<?= $mo['prix_base'] ?>"><?= $mo['nom'] ?> (<?= number_format($mo['prix_base'],0) ?> F)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 bg-white rounded shadow-sm">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Date Commande</label>
                            <input type="date" name="date_commande" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-danger">Date Livraison</label>
                            <input type="date" name="date_livraison" class="form-control border-danger" required>
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label small fw-bold">Priorité</label>
                            <select name="priorite" class="form-select">
                                <option value="normale">Normale</option>
                                <option value="urgente">Urgente (Sénégalaise !)</option>
                                <option value="vip">Client VIP / Tabaski</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="p-3 bg-dark text-white rounded shadow-sm">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label class="small opacity-75">Prix Total (FCFA)</label>
                            <input type="number" name="total_ttc" id="total_ttc" class="form-control form-control-lg bg-transparent text-white border-primary" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small opacity-75">Acompte versé</label>
                            <input type="number" name="acompte_verse" class="form-control form-control-lg bg-transparent text-success border-success" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="small opacity-75">Tissu fourni ?</label>
                            <select class="form-select bg-transparent text-white border-secondary">
                                <option>Oui, par le client</option>
                                <option>Non, tissu de l'atelier</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label small fw-bold">Détails de la coupe & Notes</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Ex: Col officier, broderie fil d'or, pantalon sans poches arrière..."></textarea>
            </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary px-5 fw-bold">Créer la commande & Facture</button>
      </div>
    </form>
  </div>
</div>

<script>
function updatePrix() {
    const select = document.getElementById('selectModele');
    const prix = select.options[select.selectedIndex].getAttribute('data-prix');
    if(prix > 0) {
        document.getElementById('total_ttc').value = prix;
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
