<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupération des patients pour le sélecteur
$patients = $db->query("SELECT id, nom, prenom, code_patient FROM patients")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-receipt"></i> Créer une nouvelle facture</h4>
        </div>
        <div class="card-body">
            <form action="process_add.php" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Sélectionner le Patient</label>
                        <select name="id_patient" class="form-select" required>
                            <?php foreach($patients as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['code_patient'] ?> - <?= $p['nom'] ?> <?= $p['prenom'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="impayee">Impayée</option>
                            <option value="payee">Payée</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mode de Paiement</label>
                        <select name="mode_paiement" class="form-select">
                            <option value="especes">Espèces</option>
                            <option value="carte">Carte Bancaire</option>
                            <option value="virement">Virement / Mobile Money</option>
                        </select>
                    </div>
                </div>

                <h5 class="mt-4 mb-3">Détails des prestations</h5>
                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Désignation</th>
                            <th width="100">Quantité</th>
                            <th width="200">Prix Unitaire (FCFA)</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="designation[]" class="form-control" placeholder="Ex: Consultation Générale" required></td>
                            <td><input type="number" name="quantite[]" class="form-control" value="1" required></td>
                            <td><input type="number" name="prix_unitaire[]" class="form-control" placeholder="Montant" required></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addRow()">
                    <i class="bi bi-plus-circle"></i> Ajouter une ligne
                </button>

                <div class="mt-4 text-end">
                    <a href="list.php" class="btn btn-light">Annuler</a>
                    <button type="submit" class="btn btn-success">Générer la Facture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addRow() {
    const row = `<tr>
        <td><input type="text" name="designation[]" class="form-control" required></td>
        <td><input type="number" name="quantite[]" class="form-control" value="1" required></td>
        <td><input type="number" name="prix_unitaire[]" class="form-control" required></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
    </tr>`;
    document.querySelector('#itemsTable tbody').insertAdjacentHTML('beforeend', row);
}
</script>

<?php include '../../includes/footer.php'; ?>
