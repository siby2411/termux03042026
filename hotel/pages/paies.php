<?php
$message = "";

// 1. Traitement du versement de salaire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['payer'])) {
    try {
        $sql = "INSERT INTO paies (personnel_id, montant, date_paiement) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['personnel_id'], $_POST['montant'], $_POST['date']]);
        
        // Optionnel : On peut aussi insérer cela dans la table 'charges' pour centraliser
        $pdo->prepare("INSERT INTO charges (libelle, montant, categorie, date_charge) VALUES (?, ?, 'Autre', ?)")
            ->execute(["Salaire - " . $_POST['nom_employe'], $_POST['montant'], $_POST['date']]);
            
        $message = "<div class='alert alert-success shadow-sm'>💰 Salaire versé avec succès !</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// 2. Récupération des employés pour le formulaire
$employes = $pdo->query("SELECT * FROM personnel ORDER BY nom ASC")->fetchAll();

// 3. Historique des derniers paiements
$historique = $pdo->query("
    SELECT p.*, pers.nom, pers.poste 
    FROM paies p 
    JOIN personnel pers ON p.personnel_id = pers.id 
    ORDER BY p.date_paiement DESC LIMIT 10
")->fetchAll();
?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white fw-bold">
                <i class="bi bi-cash-coin"></i> Effectuer un Paiement
            </div>
            <div class="card-body">
                <?= $message ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Sélectionner l'employé</label>
                        <select name="personnel_id" id="select_emp" class="form-select" onchange="updateSalaire()" required>
                            <option value="">-- Choisir un membre du staff --</option>
                            <?php foreach($employes as $emp): ?>
                                <option value="<?= $emp['id'] ?>" data-salaire="<?= $emp['salaire_base'] ?>" data-nom="<?= $emp['nom'] ?>">
                                    <?= $emp['nom'] ?> (<?= $emp['poste'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="nom_employe" id="nom_employe">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Montant à verser (FCFA)</label>
                        <div class="input-group">
                            <input type="number" name="montant" id="montant_input" class="form-control fw-bold text-success" required>
                            <span class="input-group-text">F</span>
                        </div>
                        <small class="text-muted">Salaire de base enregistré : <span id="base_info">0</span> F</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Date de valeur</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <button type="submit" name="payer" class="btn btn-success w-100 fw-bold shadow-sm py-2">
                        <i class="bi bi-check-circle"></i> Confirmer le virement
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-clock-history text-muted"></i> Derniers versements effectués
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Employé</th>
                                <th>Date</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($historique as $h): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= $h['nom'] ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= $h['poste'] ?></div>
                                </td>
                                <td class="small"><?= date('d/m/Y', strtotime($h['date_paiement'])) ?></td>
                                <td class="fw-bold text-success text-end"><?= number_format($h['montant'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($historique)) echo "<tr><td colspan='3' class='text-center p-3'>Aucun paiement enregistré.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateSalaire() {
    const select = document.getElementById('select_emp');
    const selectedOption = select.options[select.selectedIndex];
    const salaire = selectedOption.getAttribute('data-salaire');
    const nom = selectedOption.getAttribute('data-nom');
    
    if (salaire) {
        document.getElementById('montant_input').value = salaire;
        document.getElementById('base_info').innerText = new Intl.NumberFormat().format(salaire);
        document.getElementById('nom_employe').value = nom;
    }
}
</script>
