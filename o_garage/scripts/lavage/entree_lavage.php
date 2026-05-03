<?php 
require_once '../../includes/header.php'; 
// On récupère les clients comme dans fiche_entree.php
$clients = $db->query("SELECT id_client, nom, prenom, immatriculation FROM clients ORDER BY nom ASC")->fetchAll();
?>
<div class="container mt-4">
    <div class="card shadow-lg border-0 border-top border-info border-5">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-info"><i class="fas fa-soap me-2"></i>NOUVELLE OPÉRATION DE LAVAGE</h5>
        </div>
        <div class="card-body p-4">
            <form action="save_lavage.php" method="POST">
                <div class="mb-3">
                    <label class="fw-bold mb-1">Véhicule (Client enregistré)</label>
                    <select name="id_client" class="form-select form-select-lg border-info" required>
                        <option value="">-- Sélectionner la plaque --</option>
                        <?php foreach($clients as $c): ?>
                            <option value="<?= $c['id_client'] ?>">
                                <?= htmlspecialchars($c['immatriculation']) ?> (<?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Prestation</label>
                        <select name="type_lavage" class="form-select border-info">
                            <option value="simple">Lavage Simple</option>
                            <option value="moteur">Lavage Moteur</option>
                            <option value="complet">Complet + Graissage</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Montant (F CFA)</label>
                        <input type="number" name="montant" class="form-control border-info" placeholder="Ex: 3000" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-info w-100 btn-lg text-white fw-bold mt-4 shadow">VALIDER ET IMPRIMER TICKET</button>
            </form>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
