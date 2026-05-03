<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// 1. Chiffre d'affaires du jour (Paiements encaissés aujourd'hui)
$ca_jour = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE DATE(date_paiement) = CURDATE()")->fetchColumn();

// 2. Livraisons urgentes (Dans les 3 prochains jours)
$urgences = $pdo->query("
    SELECT c.*, cl.prenom, cl.nom, cl.telephone 
    FROM commandes c 
    JOIN clients cl ON c.client_id = cl.id 
    WHERE c.date_livraison BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    AND c.statut NOT IN ('livrée', 'annulée')
    ORDER BY c.date_livraison ASC
")->fetchAll();

// 3. Statistiques globales
$nb_clients = $pdo->query("SELECT COUNT(*) FROM clients WHERE statut='actif'")->fetchColumn();
$commandes_en_cours = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_cours'")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold">Tableau de Bord</h2>
    <p class="text-muted">Bienvenue chez <?= APP_NAME ?>. Voici l'état de l'atelier aujourd'hui.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <h6 class="text-uppercase small opacity-75">Encaissé Aujourd'hui</h6>
                <h2 class="fw-bold mb-0"><?= number_format($ca_jour, 0, ',', ' ') ?> <small>FCFA</small></h2>
                <i class="bi bi-cash-coin position-absolute end-0 bottom-0 m-3 fs-1 opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Commandes en cours</h6>
                <h2 class="fw-bold mb-0"><?= $commandes_en_cours ?> 🧵</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Total Clients</h6>
                <h2 class="fw-bold mb-0"><?= $nb_clients ?> 👤</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold py-3">
                <i class="bi bi-clock-history me-2 text-danger"></i> Livraisons à venir (72h)
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Client</th>
                            <th>Date Limite</th>
                            <th>Reste</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($urgences as $u): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= $u['prenom'] ?> <?= $u['nom'] ?></div>
                                <div class="small text-muted"><?= $u['numero_commande'] ?></div>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger">
                                    <?= date('d/m/Y', strtotime($u['date_livraison'])) ?>
                                </span>
                            </td>
                            <td class="fw-bold"><?= number_format($u['reste_a_payer'], 0, ',', ' ') ?> F</td>
                            <td class="text-end">
                                <?php 
                                    $msg = "Salam {$u['prenom']}, c'est OMEGA COUTURE. Votre commande {$u['numero_commande']} est prête ! Reste à payer : " . number_format($u['reste_a_payer'], 0, ',', ' ') . " F.";
                                    $wa = lienWhatsApp($u['telephone'], $msg);
                                ?>
                                <a href="<?= $wa ?>" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-whatsapp"></i> Notifier
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($urgences)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">Aucune livraison urgente pour le moment. 🎉</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold py-3">Actions Rapides</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="commandes.php" class="btn btn-outline-primary text-start">
                        <i class="bi bi-plus-circle me-2"></i> Prendre une commande
                    </a>
                    <a href="clients.php" class="btn btn-outline-dark text-start">
                        <i class="bi bi-person-plus me-2"></i> Nouveau Client
                    </a>
                    <a href="depenses.php" class="btn btn-outline-danger text-start">
                        <i class="bi bi-cart-dash me-2"></i> Noter une dépense
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body small">
                <div class="fw-bold">Support Technique</div>
                <div class="text-muted">En cas de besoin, contactez OMEGA :</div>
                <a href="https://wa.me/221776542803" class="text-decoration-none fw-bold text-success">
                    <i class="bi bi-whatsapp"></i> +221 77 654 28 03
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
