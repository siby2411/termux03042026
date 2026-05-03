<?php 
require_once '../../includes/header.php'; 

try {
    // Correction de la requête pour accepter 'En cours' ou 'en_cours'
    // Et s'assurer que la jointure se fait sur la colonne immatriculation
    $sql = "SELECT i.id_intervention, c.nom, i.immatriculation 
            FROM interventions i 
            JOIN clients c ON i.immatriculation = c.immatriculation
            WHERE LOWER(i.statut) = 'en cours' OR LOWER(i.statut) = 'en_cours' 
            ORDER BY i.id_intervention DESC";
            
    $interventions = $db->query($sql)->fetchAll();

    // Récupération de la tarification
    $tarifs = $db->query("SELECT * FROM types_reparation ORDER BY libelle_reparation ASC")->fetchAll();
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur SQL : " . $e->getMessage() . "</div>";
    $interventions = [];
}
?>

<div class="container mt-4">
    <div class="card shadow-lg border-0 border-top border-primary border-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-file-invoice me-2"></i>FACTURE RÉPARATION / ATELIER</h5>
            <span class="badge bg-dark">N° BL-<?= date('ymdHi') ?></span>
        </div>
        <div class="card-body p-4">
            <?php if(empty($interventions)): ?>
                <div class="alert alert-warning border-warning">
                    <h4 class="alert-heading"><i class="fas fa-pause-circle me-2"></i>File d'attente vide</h4>
                    <p>Aucun véhicule n'est actuellement marqué "En cours" dans l'atelier.</p>
                    <hr>
                    <a href="../vehicules/fiche_entree.php" class="btn btn-warning fw-bold">
                        <i class="fas fa-plus me-2"></i>Enregistrer une nouvelle Entrée
                    </a>
                </div>
            <?php else: ?>
                <form action="save_reparation.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="fw-bold text-danger">Sélectionner le Dossier Actif</label>
                            <select name="id_intervention" class="form-select form-select-lg border-danger" required>
                                <option value="">-- Choisir le véhicule à facturer --</option>
                                <?php foreach($interventions as $i): ?>
                                    <option value="<?= $i['id_intervention'] ?>">
                                        BL #<?= $i['id_intervention'] ?> - <?= htmlspecialchars($i['immatriculation']) ?> (<?= htmlspecialchars($i['nom']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="fw-bold">Type de Réparation effectuée</label>
                            <select id="select-tarif" name="id_type_reparation" class="form-select border-primary" onchange="updatePrice()">
                                <option value="" data-price="0">-- Choisir un forfait --</option>
                                <?php foreach($tarifs as $t): ?>
                                    <option value="<?= $t['id_type'] ?>" data-price="<?= $t['tarif_main_oeuvre'] ?>">
                                        <?= htmlspecialchars($t['libelle_reparation']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="fw-bold">Montant Main d'œuvre (F CFA)</label>
                            <input type="number" id="prix_mo" name="montant_mo" class="form-control fw-bold text-primary" required>
                        </div>

                        <div class="col-md-12">
                            <label class="fw-bold">Rapport d'intervention (Travaux réalisés)</label>
                            <textarea name="observations" class="form-control" rows="3" placeholder="Qu'est-ce qui a été réparé ?"></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold mt-4 shadow">
                        <i class="fas fa-print me-2"></i>VALIDER ET GÉNÉRER LE BON
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updatePrice() {
    const select = document.getElementById('select-tarif');
    const priceInput = document.getElementById('prix_mo');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    priceInput.value = price;
}
</script>
<?php require_once '../../includes/footer.php'; ?>
