 
<?php require_once 'includes/header.php'; ?>
                         <div class="row g-4">        <div class="col-12">
        <div class="card bg-dark border-0 shadow-lg text-white p-4" style="border-left: 5px solid var(--om-orange) !important;">                                  <form action="index.php" method="GET" class="row g-2">
                <div class="col-md-9">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-warning border-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Scanner une pièce, un nom ou une plaque (ex: FIL, DK-22...)" value="<?= $_GET['q'] ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">SCANNER SYSTÈME</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-3 col-md-4">
        <div class="d-grid gap-2 mb-4">
            <a href="/scripts/pieces/formulaire_vente.php" class="btn btn-warning btn-lg fw-bold py-3 shadow">
                <i class="fas fa-cart-plus me-2"></i>VENTE PIÈCES
            </a>
            <a href="/scripts/factures/reparation.php" class="btn btn-primary btn-lg fw-bold py-3 shadow">
                <i class="fas fa-file-invoice-dollar me-2"></i>FACTURE RÉPARATION
            </a>
            <a href="/scripts/diagnostics/recherche_panne.php" class="btn btn-dark btn-lg fw-bold py-3 shadow border-warning">
                <i class="fas fa-microchip me-2"></i>DIAGNOSTIC / OBD
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-user-plus me-2"></i>NOUVEAU CLIENT
            </div>
            <div class="card-body bg-white">
                <form action="scripts/clients/traitement_client.php" method="POST">
                    <input type="text" name="nom" class="form-control mb-2" placeholder="Nom du Client" required>
                    <input type="text" name="telephone" class="form-control mb-2" placeholder="Téléphone" required>
                    <input type="text" name="immatriculation" class="form-control border-primary fw-bold text-center mb-3" placeholder="DK-0000-X" required>
                    <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">CRÉER LE DOSSIER</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-9 col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-database me-2 text-primary"></i>RÉSULTATS SYSTÈME</span>
                <span class="badge bg-secondary">Temps réel</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Type</th><th>Désignation / Nom</th><th>Détails Techniques</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = $_GET['q'] ?? '';
                        if($q):
                            $term = "%$q%";
                            // Recherche dans les Pièces
                            $stP = $db->prepare("SELECT * FROM pieces_detachees WHERE nom_piece LIKE ? LIMIT 5");
                            $stP->execute([$term]);
                            while($p = $stP->fetch()): ?>
                                <tr class="table-warning">
                                    <td><span class="badge bg-dark">PIÈCE</span></td>
                                    <td class="fw-bold text-primary"><?= $p['nom_piece'] ?></td>
                                    <td><?= number_format($p['prix_vente'], 0, '.', ' ') ?> F | <small>Stock :</small> <span class="badge bg-danger"><?= $p['stock_actuel'] ?></span></td>
                                    <td><a href="/scripts/pieces/formulaire_vente.php?id=<?= $p['id_piece'] ?>" class="btn btn-sm btn-dark">Ajouter</a></td>
                                </tr>
                            <?php endwhile;

                            // Recherche dans les Clients
                            $stC = $db->prepare("SELECT * FROM clients WHERE nom LIKE ? OR immatriculation_principale LIKE ? LIMIT 5");
                            $stC->execute([$term, $term]);
                            while($c = $stC->fetch()): ?>
                                <tr>
                                    <td><span class="badge bg-primary">CLIENT</span></td>
                                    <td class="fw-bold"><?= strtoupper($c['nom']) ?></td>
                                    <td><i class="fas fa-car me-2 text-secondary"></i><?= $c['immatriculation_principale'] ?></td>
                                    <td><a href="/scripts/clients/profil.php?id=<?= $c['id_client'] ?>" class="btn btn-sm btn-outline-primary">Profil</a></td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-5">
                                <i class="fas fa-search fa-3x mb-3 opacity-25"></i><br>
                                Entrez un nom, une plaque ou une pièce pour commencer...
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm bg-info text-white overflow-hidden">
            <div class="card-body d-flex justify-content-between align-items-center py-4">
                <div>
                    <h4 class="fw-bold mb-0">LAVAGE AUTO</h4>
                    <p class="mb-0 opacity-75">Suivi des prestations de nettoyage</p>
                </div>
                <a href="/scripts/lavage/entree_lavage.php" class="btn btn-light btn-lg fw-bold shadow-sm">NOUVEAU LAVAGE</a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm bg-dark text-white overflow-hidden" style="border-right: 5px solid var(--om-orange) !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-4">
                <div>
                    <h4 class="fw-bold mb-0 text-warning">RESSOURCES HUMAINES</h4>
                    <p class="mb-0 opacity-75">Staff technique & Paie</p>
                </div>
                <div class="btn-group shadow-sm">
                    <a href="/scripts/mecaniciens/liste_mecaniciens.php" class="btn btn-outline-light">Staff</a>
                    <a href="/scripts/fournisseurs/liste_fournisseurs.php" class="btn btn-outline-warning">Fournisseurs</a>
                </div>
        

 <div class="col-md-6">
        <div class="card border-0 shadow-sm bg-dark text-white overflow-hidden" style="border-right: 5px solid var(--om-orange) !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-4">
                <div>
                    <h4 class="fw-bold mb-0 text-warning">RESSOURCES HUMAINES</h4>
                    <p class="mb-0 opacity-75">Staff technique & Paie</p>
                </div>
                <div class="btn-group shadow-sm">
                    <a href="/scripts/mecaniciens/liste_mecaniciens.php" class="btn btn-outline-light">Staff</a>
                    <a href="/scripts/fournisseurs/liste_fournisseurs.php" class="btn btn-outline-warning">Factures </a>
                </div>
        




    </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
root@localhost:~/shared/htdocs/apachewsl2026/o_garage#
