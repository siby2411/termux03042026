<?php require_once 'includes/header.php'; ?>

<div class="row g-4">        
    <div class="col-12">
        <div class="card bg-dark border-0 shadow-lg text-white p-4" style="border-left: 5px solid var(--om-orange, #ffc107) !important;">                                  
            <form action="index.php" method="GET" class="row g-2">
                <div class="col-md-9">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-warning border-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Scanner une pièce, un nom ou une plaque (ex: DK-11, SALL...)" value="<?= $_GET['q'] ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">SCANNER SYSTÈME</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4 col-md-5">
        <div class="card shadow-sm border-0 border-top border-primary border-5 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-user-plus me-2"></i>NOUVEAU DOSSIER CLIENT</h5>
            </div>
            <div class="card-body bg-white">
                <form action="scripts/clients/traitement_client.php" method="POST">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="prenom" class="form-control mb-2" placeholder="Prénom" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="nom" class="form-control mb-2" placeholder="Nom" required>
                        </div>
                    </div>
                    <input type="text" name="telephone" class="form-control mb-2" placeholder="Téléphone (ex: 77...)" required>
                    <input type="text" name="adresse" class="form-control mb-2" placeholder="Quartier / Adresse (Dakar)">

                    <label class="small fw-bold text-danger mt-2">PIVOT TRAÇABILITÉ (IMMATRICULATION) :</label>
                    <input type="text" name="immatriculation" class="form-control border-danger fw-bold text-center mb-3" 
                           style="font-size: 1.3rem; background: #fff5f5; text-transform: uppercase;" 
                           placeholder="DK-0000-X" required>

                    <button type="submit" class="btn btn-success w-100 btn-lg fw-bold shadow-sm py-2">
                        <i class="fas fa-save me-2"></i>CRÉER ET ENREGISTRER
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3 small text-uppercase text-secondary">Gestion Magasin</h6>
                <a href="scripts/pieces/formulaire_vente.php" class="btn btn-warning w-100 fw-bold mb-2 shadow-sm">
                    <i class="fas fa-cart-plus me-2"></i>VENTE PIÈCE DÉTACHÉE
                </a>
                <a href="scripts/pieces/liste_ventes.php" class="btn btn-outline-dark w-100 fw-bold shadow-sm">
                    <i class="fas fa-history me-2"></i>HISTORIQUE DES VENTES
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-database me-2 text-primary"></i>RÉSULTATS DE RECHERCHE</span>
                <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <span class="badge bg-success">Opération réussie</span>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small text-uppercase">
                        <tr><th>Type</th><th>Identification</th><th>Détails</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = $_GET['q'] ?? '';
                        if($q):
                            $term = "%$q%";
                            
                            // 1. Recherche Clients (sur Nom, Immat ou Tel)
                            $stC = $db->prepare("SELECT * FROM clients WHERE nom LIKE ? OR immatriculation LIKE ? OR telephone LIKE ? LIMIT 5");
                            $stC->execute([$term, $term, $term]);
                            while($c = $stC->fetch()): ?>
                                <tr>
                                    <td><span class="badge bg-primary px-2">CLIENT</span></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= strtoupper($c['prenom'] . ' ' . $c['nom']) ?></div>
                                        <small class="text-muted"><i class="fas fa-phone-alt me-1 small"></i><?= $c['telephone'] ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-bold"><?= $c['immatriculation'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="scripts/clients/profil.php?id=<?= $c['id_client'] ?>" class="btn btn-sm btn-outline-primary">Profil</a>
                                    </td>
                                </tr>
                            <?php endwhile;

                            // 2. Recherche Pièces
                            $stP = $db->prepare("SELECT * FROM pieces_detachees WHERE nom_piece LIKE ? LIMIT 5");
                            $stP->execute([$term]);
                            while($p = $stP->fetch()): ?>
                                <tr class="table-warning">
                                    <td><span class="badge bg-dark px-2">PIÈCE</span></td>
                                    <td class="fw-bold"><?= $p['nom_piece'] ?></td>
                                    <td>
                                        <span class="text-success fw-bold"><?= number_format($p['prix_vente'], 0, '.', ' ') ?> F</span>
                                        <small class="ms-2">Stock: <?= $p['stock_actuel'] ?></small>
                                    </td>
                                    <td class="text-end">
                                        <a href="scripts/pieces/formulaire_vente.php?id=<?= $p['id_piece'] ?>" class="btn btn-sm btn-dark">Vendre</a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-5">
                                <i class="fas fa-search fa-3x mb-3 opacity-25"></i><br>
                                Entrez un nom, une plaque (ex: DK11) ou une pièce pour scanner...
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6 mt-4">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body d-flex justify-content-between align-items-center py-4">
                <div>
                    <h4 class="fw-bold mb-0 text-uppercase"><i class="fas fa-soap me-2"></i>Lavage Auto</h4>
                    <p class="mb-0 opacity-75 small">Gérer les tickets de lavage</p>
                </div>
                <a href="scripts/lavage/entree_lavage.php" class="btn btn-light btn-lg fw-bold shadow-sm text-info">NOUVELLE OPÉRATION</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mt-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body d-flex justify-content-between align-items-center py-4">
                <div>
                    <h4 class="fw-bold mb-0 text-uppercase text-white"><i class="fas fa-tools me-2"></i>Atelier / Réparation</h4>
                    <p class="mb-0 opacity-75 small">Fiches techniques & Factures</p>
                </div>
                <div class="btn-group shadow-sm">
                    <a href="scripts/vehicules/fiche_entree.php" class="btn btn-light fw-bold text-primary">ENTRÉE VÉHICULE</a>
                    <a href="scripts/factures/reparation.php" class="btn btn-dark fw-bold">FACTURER</a>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="mt-5 py-4 text-center border-top">
    <div class="container">
        <p class="mb-0 fw-bold">© 2026 OMEGA INFORMATIQUE CONSULTING | Dakar, Sénégal</p>
        <small class="text-secondary text-uppercase" style="letter-spacing: 1px;">Système Intégré de Gestion de Garage v3.0</small>
    </div>
</footer>

<?php require_once 'includes/footer.php'; ?>
