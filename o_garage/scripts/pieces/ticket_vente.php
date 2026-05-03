<?php require_once '../../includes/header.php'; 
$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT fp.*, c.nom, c.telephone, c.immatriculation FROM factures_pieces fp JOIN clients c ON fp.id_client = c.id_client WHERE fp.id_facture = ?");
$stmt->execute([$id]);
$facture = $stmt->fetch();
?>
<div class="container mt-4 mb-5">
    <div class="ticket-print p-4 bg-white shadow mx-auto" style="max-width: 400px; font-family: 'Courier New', Courier, monospace;">
        <div class="text-center border-bottom pb-2 mb-2">
            <h4 class="fw-bold mb-0">OMEGA TECH</h4>
            <small>Dakar, Sénégal | Tel: +221 ...</small><br>
            <strong class="small text-uppercase">Ticket Vente Pièces #VP-<?= str_pad($id, 5, '0', STR_PAD_LEFT) ?></strong>
        </div>
        
        <div class="mb-2 small">
            Date: <?= date('d/m/Y H:i', strtotime($facture['date_facture'])) ?><br>
            Client: <?= $facture['nom'] ?><br>
            Véhicule: <?= $facture['immatriculation'] ?>
        </div>

        <table class="table table-sm small">
            <thead><tr class="border-bottom"><th>Désignation</th><th>Qté</th><th class="text-end">Total</th></tr></thead>
            <tbody>
                <?php 
                $details = $db->prepare("SELECT vd.*, p.nom_piece FROM vente_details vd JOIN pieces_detachees p ON vd.id_piece = p.id_piece WHERE vd.id_vente = ?");
                // Note: Assurez-vous que l'ID correspond à votre logique de table de liaison
                $details->execute([$id]);
                while($d = $details->fetch()): ?>
                <tr><td><?= $d['nom_piece'] ?></td><td>x<?= $d['quantite'] ?></td><td class="text-end"><?= number_format($d['sous_total'], 0, '', ' ') ?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="border-top pt-2 text-end">
            <h5 class="fw-bold">TOTAL: <?= number_format($facture['total_vente'], 0, '', ' ') ?> F</h5>
        </div>
        
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="fas fa-print"></i> Imprimer</button>
            <a href="liste_ventes.php" class="btn btn-primary btn-sm">Journal</a>
            <a href="formulaire_vente.php" class="btn btn-success btn-sm">Nouvelle Vente</a>
        </div>
    </div>
</div>

<style>
@media print { .no-print, .navbar, footer { display: none !important; } .ticket-print { box-shadow: none !important; margin: 0; width: 100%; } }
</style>
<?php require_once '../../includes/footer.php'; ?>
