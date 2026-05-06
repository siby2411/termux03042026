<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Bilan comptable";
$page_icon = "pie-chart";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

// Calculs automatiques
$actif_total = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 200 AND 599")->fetchColumn();
$passif_total = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 100 AND 199 OR compte_credite_id BETWEEN 600 AND 899")->fetchColumn();
$fonds_roulement = $passif_total - $actif_total;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-pie-chart"></i> Module 2 : Le bilan comptable</h5>
                <small>Comprendre la situation patrimoniale de l'entreprise</small>
            </div>
            <div class="card-body">
                
                <!-- Méthodologie -->
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>📚 MÉTHODOLOGIE D'APPROCHE DU BILAN :</strong>
                    <p class="mt-2">Le bilan représente à un instant donné la situation financière de l'entreprise.</p>
                </div>
                
                <!-- Structure du bilan -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-primary text-white">
                                <h6>📊 ACTIF (Ce que l'entreprise possède)</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>Immobilisations</strong> (Classe 2) → Biens durables</li>
                                    <li><strong>Stocks</strong> (Classe 3) → Marchandises, matières</li>
                                    <li><strong>Créances</strong> (Classe 4) → Clients</li>
                                    <li><strong>Trésorerie</strong> (Classe 5) → Banque, caisse</li>
                                </ul>
                                <div class="alert alert-primary mt-2">
                                    <strong>TOTAL ACTIF :</strong> <?= number_format($actif_total, 0, ',', ' ') ?> FCFA
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">
                                <h6>💰 PASSIF (Ce que l'entreprise doit)</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>Capitaux propres</strong> (Classe 1) → Capital, réserves</li>
                                    <li><strong>Dettes</strong> (Classe 4-6-8) → Fournisseurs, emprunts</li>
                                </ul>
                                <div class="alert alert-success mt-2">
                                    <strong>TOTAL PASSIF :</strong> <?= number_format($passif_total, 0, ',', ' ') ?> FCFA
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Équation fondamentale -->
                <div class="alert alert-info text-center mt-3">
                    <strong>🔑 ÉQUATION FONDAMENTALE DU BILAN :</strong><br>
                    <code class="fs-4">ACTIF = PASSIF</code>
                    <div class="mt-2">
                        <?php if(abs($actif_total - $passif_total) < 1): ?>
                            <span class="badge bg-success">✓ Votre bilan est ÉQUILIBRÉ</span>
                        <?php else: ?>
                            <span class="badge bg-danger">✗ Votre bilan n'est pas équilibré</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Fonds de roulement -->
                <div class="card bg-primary text-white mt-4">
                    <div class="card-header">
                        <h6><i class="bi bi-calculator"></i> LE FONDS DE ROULEMENT</h6>
                    </div>
                    <div class="card-body">
                        <p>Le Fonds de Roulement (FR) représente la marge de sécurité financière de l'entreprise.</p>
                        <div class="bg-white text-dark p-2 rounded">
                            <strong>🔢 Formule :</strong><br>
                            <code>FR = Capitaux permanents - Actif immobilisé</code><br>
                            <code>FR = (Passif) - (Actif classé 2)</code>
                        </div>
                        <div class="mt-3">
                            <strong>📊 Votre FR calculé :</strong>
                            <h4><?= number_format($fonds_roulement, 0, ',', ' ') ?> FCFA</h4>
                            <?php if($fonds_roulement > 0): ?>
                                <span class="badge bg-success">✓ FR positif - bonne santé financière</span>
                            <?php else: ?>
                                <span class="badge bg-warning">⚠️ FR négatif - besoin de financement long terme</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../bilan.php" class="btn btn-danger">Voir votre bilan complet →</a>
                    <a href="sig.php" class="btn btn-info">Module suivant : SIG →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
