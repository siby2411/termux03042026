<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Gestion des Salaires - SYSCOHADA";
$page_icon = "people";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Traitement du formulaire salaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter_salarie') {
        $stmt = $pdo->prepare("INSERT INTO SALARIES (matricule, nom, prenom, fonction, service, date_embauche, situation_familiale, nombre_enfants) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['matricule'], $_POST['nom'], $_POST['prenom'],
            $_POST['fonction'], $_POST['service'], $_POST['date_embauche'],
            $_POST['situation_familiale'], $_POST['nombre_enfants']
        ]);
        $message = "✅ Salarié ajouté avec succès";
    }
    
    if ($_POST['action'] === 'generer_bulletin') {
        $salarie_id = (int)$_POST['salarie_id'];
        $mois = (int)$_POST['mois'];
        $annee = (int)$_POST['annee'];
        $salaire_base = (float)$_POST['salaire_base'];
        $primes = (float)$_POST['primes'];
        $heures_sup = (float)$_POST['heures_sup'];
        
        // Calcul des charges
        $total_brut = $salaire_base + $primes + $heures_sup;
        
        // Part employé (retenues)
        $cnss_employe = $total_brut * 0.045;      // 4.5%
        $ipres_employe = $total_brut * 0.08;       // 8%
        $css_employe = $total_brut * 0.01;          // 1%
        $irpp = $total_brut - $cnss_employe - $ipres_employe - $css_employe;
        $irpp = $irpp * 0.20;                      // 20% IRPP simplifié
        
        // Part employeur (charges patronales)
        $cnss_patronal = $total_brut * 0.12;       // 12%
        $ipres_patronal = $total_brut * 0.08;       // 8%
        $css_patronal = $total_brut * 0.07;         // 7%
        
        $total_retenues = $cnss_employe + $ipres_employe + $css_employe + $irpp;
        $net_a_payer = $total_brut - $total_retenues;
        $total_charges = $cnss_patronal + $ipres_patronal + $css_patronal;
        
        // Insertion bulletin
        $stmt = $pdo->prepare("INSERT INTO BULLETINS_SALAIRE (salarie_id, mois, annee, salaire_base, primes, heures_supplementaires, cnss_employe, ipres_employe, css_employe, irpp, cnss_patronal, ipres_patronal, css_patronal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$salarie_id, $mois, $annee, $salaire_base, $primes, $heures_sup, $cnss_employe, $ipres_employe, $css_employe, $irpp, $cnss_patronal, $ipres_patronal, $css_patronal]);
        
        // Écriture comptable
        $compte_salaire = 641;
        $compte_cnss = 651;
        $compte_ipres = 652;
        $compte_css = 653;
        $compte_irpp = 4442;
        $compte_banque = 521;
        
        $total_brut_annee = $total_brut + $total_charges;
        
        // Écriture de paie
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES 
                (?, 'Salaires et charges sociales', $compte_salaire, $compte_banque, ?, 'PAIE-$annee-$mois', 'SALAIRE')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([date('Y-m-d'), $net_a_payer]);
        
        $message = "✅ Bulletin généré - Net à payer : " . number_format($net_a_payer, 0, ',', ' ') . " FCFA";
    }
}

// Récupération des salariés
$salaries = $pdo->query("SELECT * FROM SALARIES WHERE statut = 'ACTIF' ORDER BY nom")->fetchAll();

// Récupération des bulletins
$bulletins = $pdo->query("
    SELECT b.*, s.nom, s.prenom, s.matricule 
    FROM BULLETINS_SALAIRE b
    JOIN SALARIES s ON b.salarie_id = s.id
    ORDER BY b.annee DESC, b.mois DESC
")->fetchAll();

$total_masse_salariale = array_sum(array_column($bulletins, 'total_brut'));
$total_charges_patronales = array_sum(array_column($bulletins, 'total_charges_patronales'));
?>

<div class="row">
    <div class="col-md-12">
        <!-- En-tête -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-people"></i> Gestion des Salaires - SYSCOHADA UEMOA</h5>
                <small>Comptabilisation des salaires, charges sociales et IRPP</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>📖 Schéma de comptabilisation SYSCOHADA :</strong><br>
                    <code>Débit 641 (Salaires) + Débit 651-653 (Charges sociales) / Crédit 521 (Banque) + Crédit 4442 (IRPP) + Crédit 431-432 (Organismes sociaux)</code>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body">
                        <i class="bi bi-people fs-2"></i>
                        <h4><?= count($salaries) ?></h4>
                        <small>Salariés actifs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white text-center">
                    <div class="card-body">
                        <i class="bi bi-cash-stack fs-2"></i>
                        <h4><?= number_format($total_masse_salariale, 0, ',', ' ') ?> F</h4>
                        <small>Masse salariale brute</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark text-center">
                    <div class="card-body">
                        <i class="bi bi-calculator fs-2"></i>
                        <h4><?= number_format($total_charges_patronales, 0, ',', ' ') ?> F</h4>
                        <small>Charges patronales</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <i class="bi bi-bank fs-2"></i>
                        <h4><?= number_format($total_masse_salariale + $total_charges_patronales, 0, ',', ' ') ?> F</h4>
                        <small>Coût total employeur</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="salaryTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#employes">👥 Employés</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#bulletins">📄 Bulletins de salaire</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nouveau">➕ Nouveau salarié</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#paie">💰 Générer paie</button></li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Onglet Employés -->
            <div class="tab-pane fade show active" id="employes">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Matricule</th><th>Nom complet</th><th>Fonction</th><th>Service</th><th>Date embauche</th><th>Situation</th><th>Enfants</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($salaries as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['matricule']) ?></td>
                                <td><?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?></td>
                                <td><?= htmlspecialchars($s['fonction'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($s['service'] ?? '-') ?></td>
                                <td><?= date('d/m/Y', strtotime($s['date_embauche'])) ?></td>
                                <td><?= $s['situation_familiale'] ?></td>
                                <td class="text-center"><?= $s['nombre_enfants'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet Bulletins -->
            <div class="tab-pane fade" id="bulletins">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Période</th><th>Salarié</th><th>Salaire brut</th><th>Retenues</th><th>Net à payer</th><th>Charges patronales</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($bulletins as $b): ?>
                            <tr>
                                <td><?= $b['mois'] ?>/<?= $b['annee'] ?></td>
                                <td><?= htmlspecialchars($b['nom'] . ' ' . $b['prenom']) ?></td>
                                <td class="text-end"><?= number_format($b['total_brut'], 0, ',', ' ') ?> F</td>
                                <td class="text-end text-danger"><?= number_format($b['total_retenues'], 0, ',', ' ') ?> F</td>
                                <td class="text-end text-success"><?= number_format($b['net_a_payer'], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($b['total_charges_patronales'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet Nouveau salarié -->
            <div class="tab-pane fade" id="nouveau">
                <div class="card bg-light">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="ajouter_salarie">
                            <div class="row">
                                <div class="col-md-4 mb-3"><label>Matricule</label><input type="text" name="matricule" class="form-control" required></div>
                                <div class="col-md-4 mb-3"><label>Nom</label><input type="text" name="nom" class="form-control" required></div>
                                <div class="col-md-4 mb-3"><label>Prénom</label><input type="text" name="prenom" class="form-control" required></div>
                                <div class="col-md-4 mb-3"><label>Fonction</label><input type="text" name="fonction" class="form-control"></div>
                                <div class="col-md-4 mb-3"><label>Service</label><input type="text" name="service" class="form-control"></div>
                                <div class="col-md-4 mb-3"><label>Date embauche</label><input type="date" name="date_embauche" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                                <div class="col-md-4 mb-3"><label>Situation familiale</label><select name="situation_familiale" class="form-select"><option>CELIBATAIRE</option><option>MARIE</option></select></div>
                                <div class="col-md-4 mb-3"><label>Nombre d'enfants</label><input type="number" name="nombre_enfants" class="form-control" value="0"></div>
                            </div>
                            <button type="submit" class="btn-omega">Enregistrer</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Onglet Génération paie -->
            <div class="tab-pane fade" id="paie">
                <div class="card bg-light">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="generer_bulletin">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>Salarié</label><select name="salarie_id" class="form-select" required><?php foreach($salaries as $s): ?><option value="<?= $s['id'] ?>"><?= $s['nom'] . ' ' . $s['prenom'] ?> (<?= $s['matricule'] ?>)</option><?php endforeach; ?></select></div>
                                <div class="col-md-3 mb-3"><label>Mois</label><select name="mois" class="form-select"><option value="<?= date('m') ?>"><?= date('F') ?></option></select></div>
                                <div class="col-md-3 mb-3"><label>Année</label><input type="number" name="annee" class="form-control" value="<?= date('Y') ?>"></div>
                                <div class="col-md-4 mb-3"><label>Salaire de base (FCFA)</label><input type="number" name="salaire_base" class="form-control" required></div>
                                <div class="col-md-4 mb-3"><label>Primes (FCFA)</label><input type="number" name="primes" class="form-control" value="0"></div>
                                <div class="col-md-4 mb-3"><label>Heures sup (FCFA)</label><input type="number" name="heures_sup" class="form-control" value="0"></div>
                            </div>
                            <button type="submit" class="btn-omega">Générer bulletin</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
