<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Balance après retraitements – Guide SYSCOHADA";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .card-retraitement { border-left: 5px solid #0d6efd; margin-bottom: 20px; transition: 0.2s; }
        .card-retraitement:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .formula { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; }
        .nature-badge { font-size: 0.8rem; padding: 3px 8px; border-radius: 15px; }
        .bg-leasing { background-color: #0d6efd; color: white; }
        .bg-interim { background-color: #fd7e14; color: white; }
        .bg-subvention { background-color: #198754; color: white; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-bar-chart-steps"></i> Balance après retraitements – Guide SYSCOHADA</h2>
                    <p>Retraitements analytiques : Crédit-bail, intérim, subventions – Passage de la comptabilité générale à la gestion financière</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : GUIDE THÉORIQUE ==================== -->
                    <h4 class="section-title"><i class="bi bi-question-circle"></i> 1. Pourquoi retraiter la balance SYSCOHADA ?</h4>
                    <div class="alert alert-info">
                        <strong>📌 Objectif :</strong> La balance comptable brute reflète la réalité juridique. La balance retraitée reflète la <strong>réalité économique</strong>. Les retraitements visent à neutraliser les effets de gestion fiscale pour se rapprocher de la <strong>Juste Valeur</strong>.
                    </div>

                    <h4 class="section-title mt-4"><i class="bi bi-files"></i> 2. Les 3 scénarios de retraitement (conformes SYSCOHADA)</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-retraitement">
                                <div class="card-body">
                                    <h5>🔧 Scénario 1 : Crédit-Bail (Leasing)</h5>
                                    <p>Selon le SYSCOHADA, le crédit-bail est souvent enregistré en charges. Pour une analyse financière, il faut le retraiter en immobilisation financée par emprunt.</p>
                                    <div class="formula mt-2">Retraitement :<br>
                                    • Réintégration de la valeur d'origine du bien à l'actif (Compte 21)<br>
                                    • Comptabilisation d'une dette financière (Compte 16)<br>
                                    • Annulation de la redevance (Compte 61) → remplacement par dotation aux amortissements (Compte 68) et intérêts financiers (Compte 66)</div>
                                    <span class="badge bg-leasing nature-badge mt-2">Leasing</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-retraitement">
                                <div class="card-body">
                                    <h5>👥 Scénario 2 : Personnel intérimaire</h5>
                                    <p>Les charges de personnel intérimaire (compte 612) polluent le ratio de productivité du travail.</p>
                                    <div class="formula mt-2">Retraitement :<br>
                                    • Sortir le coût des intérimaires des "Services Extérieurs" (Compte 61)<br>
                                    • Intégrer dans les "Charges de Personnel" (Compte 64)</div>
                                    <span class="badge bg-interim nature-badge mt-2">Intérim</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-retraitement">
                                <div class="card-body">
                                    <h5>💰 Scénario 3 : Subventions d'investissement</h5>
                                    <p>Souvent stockées en capitaux propres (Compte 13), elles doivent être retraitées pour isoler la part virée au résultat.</p>
                                    <div class="formula mt-2">Retraitement :<br>
                                    • Isoler la subvention dans un compte spécifique<br>
                                    • Neutraliser l'impact sur le résultat pour analyser la rentabilité opérationnelle réelle</div>
                                    <span class="badge bg-subvention nature-badge mt-2">Subvention</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 2 : FORMULAIRE DE SAISIE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-pencil-square"></i> 3. Enregistrer un retraitement</h4>
                    <form method="post" id="retraitementForm" class="p-4 border rounded shadow-sm bg-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Compte SYSCOHADA</label>
                                <input type="text" name="code_compte" class="form-control" placeholder="ex: 211" required>
                                <small class="text-muted">Code compte du plan SYSCOHADA</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Libellé de l'ajustement</label>
                                <input type="text" name="libelle_ajustement" class="form-control" placeholder="ex: Réintégration leasing" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Montant (€)</label>
                                <input type="number" step="0.01" name="montant" class="form-control" required>
                                <small>Positif = Débit / Négatif = Crédit</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nature du retraitement</label>
                                <select name="nature" class="form-control" required>
                                    <option value="Leasing">Crédit-Bail (Leasing)</option>
                                    <option value="Personnel_Interim">Personnel intérimaire</option>
                                    <option value="Subvention">Subvention d'investissement</option>
                                    <option value="Amortissement">Amortissement / Provision</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" name="enregistrer_retraitement" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer l'ajustement</button>
                                <button type="button" class="btn btn-info" onclick="validerRetraitements()"><i class="bi bi-check-circle"></i> Valider tous</button>
                                <button type="button" class="btn btn-secondary" onclick="exporterPDF()"><i class="bi bi-file-pdf"></i> Export PDF</button>
                            </div>
                        </div>
                    </form>

                    <?php
                    // Traitement PHP
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enregistrer_retraitement'])) {
                        $code_compte = trim($_POST['code_compte']);
                        $libelle = trim($_POST['libelle_ajustement']);
                        $montant = (float)$_POST['montant'];
                        $nature = $_POST['nature'];
                        $user_id = $_SESSION['user_id'] ?? 1;
                        
                        try {
                            $stmt = $pdo->prepare("INSERT INTO retraitements (code_compte, libelle_ajustement, montant_ajustement, nature_retraitement, utilisateur_id, status) VALUES (?, ?, ?, ?, ?, 'Brouillon')");
                            $stmt->execute([$code_compte, $libelle, $montant, $nature, $user_id]);
                            echo '<div class="alert alert-success mt-3">✅ Retraitement enregistré avec succès (statut : Brouillon).</div>';
                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger mt-3">❌ Erreur : ' . $e->getMessage() . '</div>';
                        }
                    }
                    
                    // Récupération des retraitements en cours
                    $retraitements = $pdo->query("SELECT * FROM retraitements ORDER BY date_creation DESC LIMIT 50")->fetchAll();
                    ?>
                    
                    <!-- ==================== SECTION 4 : LISTE DES RETRAITEMENTS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-list-ul"></i> 4. Retraitements enregistrés</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr><th>Compte</th><th>Libellé</th><th>Montant (€)</th><th>Nature</th><th>Statut</th><th>Date</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($retraitements as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['code_compte']) ?></td>
                                    <td><?= htmlspecialchars($r['libelle_ajustement']) ?></td>
                                    <td class="text-end"><?= number_format($r['montant_ajustement'], 2, ',', ' ') ?> €</th>
                                    <td><span class="badge bg-<?= $r['nature_retraitement'] == 'Leasing' ? 'primary' : ($r['nature_retraitement'] == 'Personnel_Interim' ? 'warning' : 'success') ?>"><?= $r['nature_retraitement'] ?></span></td>
                                    <td><span class="badge bg-<?= $r['status'] == 'Valide' ? 'success' : 'secondary' ?>"><?= $r['status'] ?></span></td>
                                    <td><?= date('d/m/Y H:i', strtotime($r['date_creation'])) ?></th>
                                    <td><button class="btn btn-sm btn-danger" onclick="supprimerRetraitement(<?= $r['id'] ?>)"><i class="bi bi-trash"></i></button></th>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 5 : VUE DE LA BALANCE RETRAITÉE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-eye"></i> 5. Balance retraitée (simulation)</h4>
                    <div class="alert alert-secondary">
                        <?php
                        // Simulation de calcul des soldes après retraitements
                        $total_debit_brut = 1250000;
                        $total_credit_brut = 1250000;
                        $total_ajustements = array_sum(array_column($retraitements, 'montant_ajustement'));
                        $solde_final = $total_debit_brut + $total_ajustements;
                        ?>
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Indicateur</th><th>Montant (€)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Total Débit brut</td><td class="text-end"><?= number_format($total_debit_brut, 2, ',', ' ') ?> €</td></tr>
                                <tr><td>Total Crédit brut</td><td class="text-end"><?= number_format($total_credit_brut, 2, ',', ' ') ?> €</td></tr>
                                <tr><td>Ajustements (retraitements)</td><td class="text-end <?= $total_ajustements >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($total_ajustements, 2, ',', ' ') ?> €</td></tr>
                                <tr><td><strong>Solde final après retraitement</strong></td><td class="text-end fw-bold"><?= number_format($solde_final, 2, ',', ' ') ?> €</td></tr>
                            </tbody>
                        </table>
                        <p class="mt-2 text-muted"><i class="bi bi-info-circle"></i> La balance après retraitements permet de calculer des ratios de gestion plus pertinents : ratio de liquidité générale, ratio de structure, capacité d'autofinancement (CAF).</p>
                    </div>

                    <!-- ==================== SECTION 6 : BONNES PRATIQUES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-check-circle"></i> 6. Bonnes pratiques d'expert-comptable</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="bi bi-journal-bookmark-fill"></i> Auditabilité</h6>
                                    <p>Chaque ligne de retraitement doit être justifiée par une pièce jointe (contrat de leasing, tableau d'amortissement).</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="bi bi-clock-history"></i> Permanence des méthodes</h6>
                                    <p>Ne changez pas vos méthodes de retraitement d'un exercice à l'autre pour garantir la comparabilité des données.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="bi bi-shield-lock"></i> Traçabilité</h6>
                                    <p>Enregistrez systématiquement l'utilisateur et la date de création. Utilisez un statut "Brouillon" avant validation définitive.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Note importante :</strong> La balance retraitée n'est pas un document officiel pour les impôts – c'est votre <strong>boussole de gestion</strong>. Elle vous permet de visualiser la santé financière réelle avant la clôture annuelle.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exporterPDF() {
    alert("Fonction d'export PDF en développement (DomPDF). Génère un rapport de synthèse complet.");
    // Implémentation DomPDF à ajouter
}

function validerRetraitements() {
    alert("Validation massive des retraitements sélectionnés.");
}

function supprimerRetraitement(id) {
    if(confirm("Supprimer ce retraitement ?")) {
        window.location.href = "?supprimer=" + id;
    }
}

$(document).ready(function() {
    // Validation des comptes SYSCOHADA
    $('input[name="code_compte"]').on('blur', function() {
        var code = $(this).val();
        if(code && !/^[0-9]{3,10}$/.test(code)) {
            alert("Le code compte SYSCOHADA doit contenir uniquement des chiffres (ex: 211, 611, 161).");
            $(this).focus();
        }
    });
});
</script>
<?php include 'inc_footer.php'; ?>
