<?php
require_once '../../includes/header.php';
// Connexion déjà incluse via header.php en général, sinon :
// $db = (new Database())->getConnection();

// Bilan Consolidé avec les colonnes RÉELLES (interventions et clients)
$bilan = $db->query("SELECT
    (SELECT COUNT(*) FROM interventions WHERE statut='En cours') as active,
    (SELECT COUNT(*) FROM clients) as total_clients,
    (SELECT SUM(main_doeuvre) FROM interventions WHERE statut='Terminé') as ca_total
")->fetch();
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-primary text-white p-3 text-center">
            <h6 class="small text-uppercase opacity-75">Unités en Atelier</h6>
            <h3 class="fw-bold"><?= $bilan['active'] ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-dark text-white p-3 text-center">
            <h6 class="small text-uppercase opacity-75">Parc Clients Global</h6>
            <h3 class="fw-bold"><?= $bilan['total_clients'] ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-success text-white p-3 text-center">
            <h6 class="small text-uppercase opacity-75">CA Main d'Œuvre (Clôturé)</h6>
            <h3 class="fw-bold"><?= number_format($bilan['ca_total'] ?? 0, 0, '.', ' ') ?> F</h3>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <span class="fw-bold"><i class="fas fa-microchip me-2"></i>CONSOLE DE SUPERVISION INGÉNIEUR</span>
        <div class="btn-group">
            <a href="../vehicules/fiche_entree.php" class="btn btn-warning btn-sm fw-bold">+ Entrée Atelier</a>
            <a href="../lavage/entree_lavage.php" class="btn btn-info btn-sm fw-bold">+ Lavage</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Véhicule & Client</th>
                    <th>Expert Assigné</th>
                    <th>KM Entrée</th>
                    <th>Symptômes / Diagnostic</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Jointure corrigée : On lie interventions et clients via immatriculation
                // Et on lie interventions et equipe via id_mecanicien
                $sql = "SELECT i.*, c.nom, c.prenom, e.nom_complet as expert
                        FROM interventions i
                        LEFT JOIN clients c ON i.immatriculation = c.immatriculation
                        LEFT JOIN equipe e ON i.id_mecanicien = e.id
                        ORDER BY i.id_intervention DESC";
                
                $stmt = $db->query($sql);
                while($r = $stmt->fetch()): ?>
                <tr>
                    <td>
                        <span class="fw-bold text-primary"><?= $r['immatriculation'] ?></span><br>
                        <small class="text-muted"><?= strtoupper($r['nom']) ?> <?= $r['prenom'] ?></small>
                    </td>
                    <td>
                        <span class="badge bg-secondary px-2"><?= $r['expert'] ?? 'Non assigné' ?></span>
                    </td>
                    <td><code class="text-dark"><?= number_format($r['km_entree'] ?? 0, 0, '.', ' ') ?> KM</code></td>
                    <td>
                        <div class="small text-truncate" style="max-width: 250px;">
                            <?= htmlspecialchars($r['description_panne']) ?>
                        </div>
                    </td>
                    <td>
                        <?php if($r['statut'] == 'En cours'): ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin me-1"></i>En cours</span>
                        <?php else: ?>
                            <span class="badge bg-success">Clôturé</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="../diagnostics/plaquette_tech.php?id=<?= $r['id_intervention'] ?>" class="btn btn-sm btn-outline-dark"><i class="fas fa-eye"></i></a>
                            <?php if($r['statut'] == 'En cours'): ?>
                                <a href="../factures/reparation.php" class="btn btn-sm btn-primary">Facturer</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
