<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT v.*, c.nom as client_nom, c.prenom as client_prenom, u.username as caissier
    FROM ventes v
    LEFT JOIN clients c ON v.client_id = c.id
    JOIN utilisateurs u ON v.utilisateur_id = u.id
    WHERE v.id = ?
");
$stmt->execute([$id]);
$vente = $stmt->fetch();

if (!$vente) {
    die("Facture non trouvée");
}

$stmt = $pdo->prepare("
    SELECT vl.*, l.titre, l.auteur
    FROM ventes_lignes vl
    JOIN livres l ON vl.livre_id = l.id
    WHERE vl.vente_id = ?
");
$stmt->execute([$id]);
$lignes = $stmt->fetchAll();

$page_title = 'Facture ' . $vente['numero_facture'];
include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card" id="facture">
            <div class="card-header text-center">
                <h2>OMEGA CONSULTING</h2>
                <p>Système de Gestion de Librairie</p>
                <h4>FACTURE N° <?php echo $vente['numero_facture']; ?></h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Client:</strong><br>
                        <?php if($vente['client_id']): ?>
                            <?php echo $vente['client_prenom'] . ' ' . $vente['client_nom']; ?>
                        <?php else: ?>
                            Client anonyme
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-end">
                        <strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?><br>
                        <strong>Caissier:</strong> <?php echo $vente['caissier']; ?>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Désignation</th>
                                <th>Auteur</th>
                                <th>Quantité</th>
                                <th>Prix unitaire</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lignes as $ligne): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ligne['titre']); ?></td>
                                <td><?php echo htmlspecialchars($ligne['auteur']); ?></td>
                                <td><?php echo $ligne['quantite']; ?></td>
                                <td><?php echo number_format($ligne['prix_unitaire'], 0, ',', ' '); ?> FCFA</td>
                                <td><?php echo number_format($ligne['sous_total'], 0, ',', ' '); ?> FCFA</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="4" class="text-end">Total</th>
                                <th><?php echo number_format($vente['montant_total'], 0, ',', ' '); ?> FCFA</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Montant payé</th>
                                <th><?php echo number_format($vente['montant_paye'], 0, ',', ' '); ?> FCFA</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Monnaie</th>
                                <th><?php echo number_format($vente['monnaie'], 0, ',', ' '); ?> FCFA</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <p class="text-muted">Merci de votre visite et à bientôt !</p>
                        <p class="text-muted small">OMEGA CONSULTING - Tél: +221 33 123 45 67</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <a href="nouvelle.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Nouvelle vente
            </a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
