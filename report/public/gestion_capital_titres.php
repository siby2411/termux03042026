<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Gestion des opérations sur capital et titres";
$page_icon = "bank";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $date_operation = $_POST['date_operation'];
    $montant = (float)$_POST['montant'];
    $type_operation = $_POST['type_operation'];
    
    // Calcul du DPS si augmentation de capital
    if ($type_operation == 'AUGMENTATION_CAPITAL' && isset($_POST['cours_avant'])) {
        $cours_avant = (float)$_POST['cours_avant'];
        $prix_emission = (float)$_POST['prix_emission'];
        $nb_actions_anciennes = (int)$_POST['nb_actions_anciennes'];
        
        $valeur_dps = ($cours_avant - $prix_emission) / ($nb_actions_anciennes + 1);
        $resultats['dps'] = $valeur_dps;
        $resultats['analyse'] = "Valeur du DPS calculée : " . number_format($valeur_dps, 2) . " €";
    }
    
    // Enregistrement de l'écriture comptable
    try {
        switch($type_operation) {
            case 'AUGMENTATION_CAPITAL':
                $sql = "INSERT INTO OPERATIONS_CAPITAL (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) 
                        VALUES (?, 'Augmentation de capital', 456, 101, ?, 'CAP-001', 'CAPITAL')";
                $pdo->prepare($sql)->execute([$date_operation, $montant]);
                $message = "✅ Augmentation de capital enregistrée - Montant : " . number_format($montant, 0, ',', ' ') . " FCFA";
                break;
                
            case 'LIBERATION_COMPENSATION':
                $sql = "INSERT INTO OPERATIONS_CAPITAL (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) 
                        VALUES (?, 'Libération par compensation', 401, 456, ?, 'CAP-002', 'CAPITAL')";
                $pdo->prepare($sql)->execute([$date_operation, $montant]);
                $message = "✅ Libération par compensation enregistrée";
                break;
                
            case 'DISTRIBUTION_DIVIDENDE':
                $sql = "INSERT INTO OPERATIONS_CAPITAL (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) 
                        VALUES (?, 'Distribution de dividende', 12, 457, ?, 'DIV-001', 'DIVIDENDE')";
                $pdo->prepare($sql)->execute([$date_operation, $montant]);
                $message = "✅ Distribution de dividende enregistrée";
                break;
                
            case 'PAIEMENT_DIVIDENDE_ACTIONS':
                $sql = "INSERT INTO OPERATIONS_CAPITAL (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) 
                        VALUES (?, 'Paiement dividende en actions', 457, 101, ?, 'DIV-002', 'DIVIDENDE')";
                $pdo->prepare($sql)->execute([$date_operation, $montant]);
                $message = "✅ Paiement de dividende en actions enregistré";
                break;
                
            case 'STOCK_OPTIONS':
                $nb_options = (int)$_POST['nb_options'];
                $prix_exercice = (float)$_POST['prix_exercice'];
                $valeur_action = (float)$_POST['valeur_action'];
                $gain_acquisition = ($valeur_action - $prix_exercice) * $nb_options;
                
                $resultats['gain_acquisition'] = $gain_acquisition;
                $message = "✅ Stock-options enregistrées - Gain d'acquisition : " . number_format($gain_acquisition, 0, ',', ' ') . " FCFA";
                break;
        }
    } catch (Exception $e) {
        $message = "❌ Erreur : " . $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-bank"></i> Gestion des opérations sur capital et titres</h5>
                <small>Conforme SYSCOHADA révisé / IFRS</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>

                <ul class="nav nav-tabs" id="capitalTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#augmentation">📈 Augmentation de capital</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#compensation">🔄 Libération par compensation</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#dividendes">💰 Dividendes</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#stockoptions">📊 Stock-options</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#dps">🧮 Calcul DPS</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <!-- Augmentation de capital -->
                    <div class="tab-pane fade show active" id="augmentation">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="AUGMENTATION_CAPITAL">
                                    <div class="col-md-4"><label>Date</label><input type="date" name="date_operation" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                                    <div class="col-md-4"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" step="100000" required></div>
                                    <div class="col-md-4"><button type="submit" class="btn-omega mt-4">Enregistrer</button></div>
                                </form>
                                <div class="alert alert-secondary mt-3">
                                    <strong>📖 Écriture générée :</strong><br>
                                    <code>456 (Associés) DÉBIT / 101 (Capital social) CRÉDIT</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Libération par compensation -->
                    <div class="tab-pane fade" id="compensation">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="LIBERATION_COMPENSATION">
                                    <div class="col-md-4"><label>Date</label><input type="date" name="date_operation" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                                    <div class="col-md-4"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" step="100000" required></div>
                                    <div class="col-md-4"><button type="submit" class="btn-omega mt-4">Enregistrer</button></div>
                                </form>
                                <div class="alert alert-secondary mt-3">
                                    <strong>📖 Écriture générée :</strong><br>
                                    <code>401 (Fournisseur) DÉBIT / 456 (Associés) CRÉDIT</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dividendes -->
                    <div class="tab-pane fade" id="dividendes">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Distribution de dividendes</h6>
                                            <form method="POST" class="row g-3">
                                                <input type="hidden" name="action" value="DISTRIBUTION_DIVIDENDE">
                                                <div class="col-md-6"><label>Date</label><input type="date" name="date_operation" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                                                <div class="col-md-6"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" step="100000" required></div>
                                                <div class="col-12"><button type="submit" class="btn-omega w-100">Enregistrer distribution</button></div>
                                            </form>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Paiement en actions</h6>
                                            <form method="POST" class="row g-3">
                                                <input type="hidden" name="action" value="PAIEMENT_DIVIDENDE_ACTIONS">
                                                <div class="col-md-6"><label>Date</label><input type="date" name="date_operation" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                                                <div class="col-md-6"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" step="100000" required></div>
                                                <div class="col-12"><button type="submit" class="btn-omega w-100">Enregistrer paiement</button></div>
                                            </form>
                                        </div>
                                    </div>
                                </form>
                                <div class="alert alert-secondary mt-3">
                                    <strong>📖 Écritures :</strong><br>
                                    Distribution : <code>12 (Résultat) DÉBIT / 457 (Associés) CRÉDIT</code><br>
                                    Paiement actions : <code>457 (Associés) DÉBIT / 101 (Capital) CRÉDIT</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock-options -->
                    <div class="tab-pane fade" id="stockoptions">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="STOCK_OPTIONS">
                                    <div class="col-md-3"><label>Nombre d'options</label><input type="number" name="nb_options" class="form-control" required></div>
                                    <div class="col-md-3"><label>Prix d'exercice (F)</label><input type="number" name="prix_exercice" class="form-control" step="100" required></div>
                                    <div class="col-md-3"><label>Valeur de l'action (F)</label><input type="number" name="valeur_action" class="form-control" step="100" required></div>
                                    <div class="col-md-3"><button type="submit" class="btn-omega mt-4">Calculer</button></div>
                                </form>
                                <?php if(isset($resultats['gain_acquisition'])): ?>
                                    <div class="alert alert-success mt-3">
                                        <strong>💰 Gain d'acquisition :</strong> <?= number_format($resultats['gain_acquisition'], 0, ',', ' ') ?> FCFA<br>
                                        <small>Ce montant est imposé dans la catégorie des traitements et salaires</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Calcul DPS -->
                    <div class="tab-pane fade" id="dps">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="AUGMENTATION_CAPITAL">
                                    <div class="col-md-3"><label>Cours avant augmentation (F)</label><input type="number" name="cours_avant" class="form-control" required></div>
                                    <div class="col-md-3"><label>Prix d'émission (F)</label><input type="number" name="prix_emission" class="form-control" required></div>
                                    <div class="col-md-3"><label>Nb actions anciennes / 1 nouvelle</label><input type="number" name="nb_actions_anciennes" class="form-control" required></div>
                                    <div class="col-md-3"><button type="submit" class="btn-omega mt-4">Calculer DPS</button></div>
                                </form>
                                <?php if(isset($resultats['dps'])): ?>
                                    <div class="alert alert-info mt-3">
                                        <strong>📊 Résultat :</strong><br>
                                        Valeur du DPS = <?= number_format($resultats['dps'], 2) ?> FCFA<br>
                                        <?= $resultats['analyse'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau récapitulatif -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">📋 Tableau récapitulatif des écritures types</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-dark">
                                    <tr><th>Opération</th><th>Compte Débit</th><th>Compte Crédit</th><th>Nature</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Augmentation de capital</td><td>456 (Associés)</td><td>101 (Capital) + 104 (Primes)</td><td>Ressource</td></tr>
                                    <tr><td>Libération par compensation</td><td>401 (Fournisseur)</td><td>456 (Associés)</td><td>Dette</td></tr>
                                    <tr><td>Distribution dividende</td><td>12 (Résultat)</td><td>457 (Associés)</td><td>Distribution</td></tr>
                                    <tr><td>Paiement dividende en actions</td><td>457 (Associés)</td><td>101 (Capital)</td><td>Renforcement</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
