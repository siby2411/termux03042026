<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel d'analyse financière";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel d'analyse financière - SYSCOHADA</h5>
                <small>Bilan fonctionnel, Tableaux de financement, Ratios</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Le bilan fonctionnel et ses retraitements</li>
                        <li>Les agrégats du bilan fonctionnel (FRNG, BFR, TN)</li>
                        <li>Le tableau de financement (besoins/dégagements)</li>
                        <li>Les ratios de liquidité et solvabilité</li>
                        <li>L'équilibre financier optimum</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">1. Le bilan fonctionnel</div>
                    <div class="card-body">
                        <p>Le <strong>bilan fonctionnel</strong> reclassifie les postes du bilan comptable selon leur fonction dans l'entreprise.</p>
                        
                        <h6>📊 Structure du bilan fonctionnel :</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-danger text-white">ACTIF FONCTIONNEL</div>
                                    <div class="card-body">
                                        <ul>
                                            <li><strong>Actif immobilisé</strong> : Biens durables (classes 2)</li>
                                            <li><strong>Actif circulant</strong> : Stocks, créances (classes 3-4)</li>
                                            <li><strong>Trésorerie actif</strong> : Disponibilités (classe 5)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-success text-white">PASSIF FONCTIONNEL</div>
                                    <div class="card-body">
                                        <ul>
                                            <li><strong>Ressources stables</strong> : Capitaux propres, dettes LT (classe 1, 16)</li>
                                            <li><strong>Passif circulant</strong> : Dettes fournisseurs (classe 4)</li>
                                            <li><strong>Trésorerie passif</strong> : Dettes bancaires CT (compte 521)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">2. Les indicateurs fondamentaux</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center bg-info text-white">
                                    <div class="card-body">
                                        <h4>FRNG</h4>
                                        <p class="mb-0">Fonds de Roulement Net Global</p>
                                        <small>Ressources stables - Actif immobilisé</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-warning text-dark">
                                    <div class="card-body">
                                        <h4>BFR</h4>
                                        <p class="mb-0">Besoin en Fonds de Roulement</p>
                                        <small>Actif circulant - Passif circulant</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-success text-white">
                                    <div class="card-body">
                                        <h4>TN</h4>
                                        <p class="mb-0">Trésorerie Nette</p>
                                        <small>Trésorerie actif - Trésorerie passif</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-secondary mt-3 text-center">
                            <strong>🔗 RELATION FONDAMENTALE : FRNG = BFR + TN</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">3. Tableau de financement (Besoins/Dégagements)</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">RESSOURCES (Dégagements)</div>
                                    <div class="card-body">
                                        <ul>
                                            <li>Capacité d'Autofinancement (CAF)</li>
                                            <li>Dotations aux amortissements</li>
                                            <li>Augmentation de capital</li>
                                            <li>Cessions d'immobilisations</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">EMPLOIS (Besoins)</div>
                                    <div class="card-body">
                                        <ul>
                                            <li>Investissements</li>
                                            <li>Remboursement d'emprunts</li>
                                            <li>Variation du BFR</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <strong>📝 Formule :</strong> Variation de trésorerie = Ressources - Emplois
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">4. Ratios de liquidité</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Ratio</th><th>Formule</th><th>Seuil</th><th>Interprétation</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Liquidité générale</td><td>Actif circulant / Passif circulant</td><td>> 1</td><td>Capacité à payer les dettes CT</td></tr>
                                    <tr><td>Liquidité réduite</td><td>(Actif - Stocks) / Passif circulant</td><td>> 0.5</td><td>Hors stocks difficilement réalisables</td></tr>
                                    <tr><td>Liquidité immédiate</td><td>Disponibilités / Passif circulant</td><td>> 0.2</td><td>Trésorerie réelle disponible</td></tr>
                                    <tr><td>Endettement</td><td>Dettes / Capitaux propres</td><td>< 1</td><td>Solvabilité à long terme</td></tr>
                                    <tr><td>Autonomie financière</td><td>Capitaux propres / Total ressources</td><td>> 50%</td><td>Indépendance financière</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">5. Interprétation de l'équilibre financier</div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <strong>🟢 ÉQUILIBRE OPTIMAL :</strong>
                            <ul>
                                <li>FRNG > 0</li>
                                <li>BFR < FRNG</li>
                                <li>Trésorerie Nette > 0</li>
                                <li>Ratio liquidité générale > 1</li>
                                <li>Ratio autonomie financière > 50%</li>
                            </ul>
                        </div>
                        <div class="alert alert-warning">
                            <strong>🟡 SITUATION TENDUE :</strong>
                            <ul>
                                <li>FRNG positif mais insuffisant pour financer le BFR</li>
                                <li>Trésorerie nette négative (découvert)</li>
                            </ul>
                            <strong>Actions correctives :</strong> Réduire le BFR, augmenter les ressources stables
                        </div>
                        <div class="alert alert-danger">
                            <strong>🔴 SITUATION CRITIQUE :</strong>
                            <ul>
                                <li>FRNG négatif (actif immobilisé > ressources stables)</li>
                                <li>Impossibilité de financer le cycle d'exploitation</li>
                            </ul>
                            <strong>Actions correctives :</strong> Augmentation de capital, renégociation des dettes, cession d'actifs
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AUX MODULES D'ANALYSE :</strong><br>
                    <a href="bilan_fonctionnel.php" class="btn btn-sm btn-primary">📊 Bilan fonctionnel</a>
                    <a href="tableau_financement.php" class="btn btn-sm btn-primary">💰 Tableau de financement</a>
                    <a href="ratios_liquidite.php" class="btn btn-sm btn-primary">📈 Ratios de liquidité</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
