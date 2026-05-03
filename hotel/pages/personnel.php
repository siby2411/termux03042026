<?php
$message = "";

// 1. Traitement du formulaire d'embauche
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['embaucher'])) {
    try {
        $sql = "INSERT INTO personnel (nom, poste, salaire_base, date_embauche) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['nom'], $_POST['poste'], $_POST['salaire'], $_POST['date']]);
        $message = "<div class='alert alert-success shadow-sm'>✨ Nouvel employé enregistré avec succès !</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// 2. Récupération de la liste de l'équipe
$equipe = $pdo->query("SELECT * FROM personnel ORDER BY date_embauche DESC")->fetchAll();
?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-warning fw-bold">
                <i class="bi bi-person-plus-fill"></i> Embaucher
            </div>
            <div class="card-body">
                <?= $message ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nom Complet</label>
                        <input type="text" name="nom" class="form-control" placeholder="ex: Mamadou Diallo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Poste</label>
                        <select name="poste" class="form-select" required>
                            <option value="Réceptionniste">Réceptionniste</option>
                            <option value="Gouvernante">Gouvernante</option>
                            <option value="Cuisinier">Cuisinier</option>
                            <option value="Sécurité">Sécurité</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Salaire de base (FCFA)</label>
                        <input type="number" name="salaire" class="form-control" placeholder="ex: 150000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Date d'embauche</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <button type="submit" name="embaucher" class="btn btn-warning w-100 fw-bold shadow-sm">Valider l'embauche</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people-fill text-primary"></i> Équipe OMEGA</span>
                <span class="badge bg-primary"><?= count($equipe) ?> Employés</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Poste</th>
                                <th>Salaire</th>
                                <th>Ancienneté</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($equipe as $p): ?>
                            <tr>
                                <td class="fw-bold"><?= $p['nom'] ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $p['poste'] ?></span></td>
                                <td><?= number_format($p['salaire_base'], 0, ',', ' ') ?> F</td>
                                <td class="small text-muted"><?= date('d/m/Y', strtotime($p['date_embauche'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
