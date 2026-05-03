<?php 
require_once '../../includes/header.php'; 
$clients = $db->query("SELECT id_client, nom, prenom, immatriculation FROM clients ORDER BY nom ASC")->fetchAll();
$mecaniciens = $db->query("SELECT id, nom_complet FROM equipe WHERE poste = 'Mécanicien'")->fetchAll();
?>

<div class="container mt-4">
    <div class="card shadow-lg border-0 border-top border-warning border-5">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-mitten me-2 text-warning"></i> RÉCEPTION VÉHICULE & DIAGNOSTIC INITIAL</h5>
        </div>
        <div class="card-body p-4">
            <form action="save_entree.php" method="POST">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="fw-bold text-primary">Sélectionner le Véhicule (Client)</label>
                        <select name="id_client" class="form-select form-select-lg border-primary" required>
                            <option value="">-- Choisir la plaque --</option>
                            <?php foreach($clients as $c): ?>
                                <option value="<?= $c['id_client'] ?>">
                                    <?= htmlspecialchars($c['immatriculation']) ?> - <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Mécanicien en charge</label>
                        <select name="id_mecanicien" class="form-select" required>
                            <?php foreach($mecaniciens as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom_complet']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Kilométrage à l'entrée</label>
                        <input type="number" name="km" class="form-control" placeholder="Ex: 145000" required>
                    </div>

                    <div class="col-md-12">
                        <label class="fw-bold text-danger">Nature de la Panne / Symptômes signalés</label>
                        <textarea name="description_panne" class="form-control border-danger" rows="4" 
                                  placeholder="Décrivez ici les problèmes (ex: Bruit train avant, fumée noire, voyant moteur allumé...)" required></textarea>
                    </div>

                    <div class="col-md-12 mt-4">
                        <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold shadow">
                            <i class="fas fa-save me-2"></i>OUVRIR LE DOSSIER D'INTERVENTION
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
