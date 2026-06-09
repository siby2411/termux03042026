<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Charte de Management de Haute Qualité – Guide stratégique";
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
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .card-outil { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 20px; }
        .card-outil:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .badge-step { font-size: 0.8rem; margin-right: 5px; }
        .lean-muda { font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-award"></i> Charte de Management de Haute Qualité – Omega Informatique Consulting</h2>
                    <p>Synthèse stratégique : BCG, Porter, SWOT, Lean, Design Thinking, Structure multidimensionnelle</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : VISION ET STRATÉGIE ==================== -->
                    <h4 class="section-title"><i class="bi bi-eye"></i> 1. Vision et stratégie (Le "Pourquoi" et le "Quoi")</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5><i class="bi bi-grid-3x3"></i> Matrice BCG</h5>
                                    <p>Évaluer le portefeuille d'activités :</p>
                                    <ul>
                                        <li><span class="badge bg-primary">Vedette</span> : Investir massivement</li>
                                        <li><span class="badge bg-success">Vache à lait</span> : Générer du cash</li>
                                        <li><span class="badge bg-warning">Dilemme</span> : Arbitrer stratégiquement</li>
                                        <li><span class="badge bg-danger">Poids mort</span> : Désinvestir</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5><i class="bi bi-bar-chart"></i> SWOT (FFOM)</h5>
                                    <p>Forces, Faiblesses, Opportunités, Menaces – diagnostic interne/externe.</p>
                                    <hr>
                                    <h5><i class="bi bi-building"></i> 5 Forces de Porter</h5>
                                    <p>Analyser l'intensité concurrentielle : nouveaux entrants, fournisseurs, clients, substituts, rivalité.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5><i class="bi bi-diagram-3"></i> Structure multidimensionnelle</h5>
                                    <p>Centres de responsabilité : Coût, Revenu, Profit, Investissement.<br>
                                    Prix de transfert à double tarification pour aligner les intérêts locaux et globaux.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 2 : OPTIMISATION ET PERFORMANCE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-gear"></i> 2. Optimisation et performance (Le "Comment")</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5><i class="bi bi-calculator"></i> Méthode Simplexe</h5>
                                    <p>Optimisation linéaire pour maximiser le profit sous contraintes (ressources rares, capacités de production).</p>
                                    <div class="alert alert-light">Max Z = Σ (Marge × Quantité) sous contraintes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5><i class="bi bi-tools"></i> Lean Management (Modèle Toyota)</h5>
                                    <p><strong>Les 7 gaspillages (Muda) :</strong></p>
                                    <div class="row">
                                        <div class="col-6 lean-muda">• Surproduction</div>
                                        <div class="col-6 lean-muda">• Attentes</div>
                                        <div class="col-6 lean-muda">• Transports inutiles</div>
                                        <div class="col-6 lean-muda">• Sur-traitement</div>
                                        <div class="col-6 lean-muda">• Stocks excessifs</div>
                                        <div class="col-6 lean-muda">• Mouvements inutiles</div>
                                        <div class="col-6 lean-muda">• Défauts / non-qualité</div>
                                    </div>
                                    <hr>
                                    <strong>Piliers :</strong> Kaizen (amélioration continue), Juste-à-temps, Qualité totale.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 3 : INNOVATION ET CENTRICITÉ HUMAINE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-lightbulb"></i> 3. Innovation et centricité humaine</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5><i class="bi bi-people"></i> Design Thinking (Stanford)</h5>
                                    <p><strong>5 étapes :</strong></p>
                                    <ol>
                                        <li><span class="badge bg-primary">1</span> Empathie – Comprendre l'utilisateur</li>
                                        <li><span class="badge bg-primary">2</span> Définition – Cadrer le problème</li>
                                        <li><span class="badge bg-primary">3</span> Idéation – Générer des idées</li>
                                        <li><span class="badge bg-primary">4</span> Prototypage – Tester rapidement</li>
                                        <li><span class="badge bg-primary">5</span> Test – Valider avec l'utilisateur</li>
                                    </ol>
                                    <p class="text-muted">Approche itérative, droit à l'erreur, solutions centrées sur l'humain.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5><i class="bi bi-people-fill"></i> Culture organisationnelle</h5>
                                    <p>La qualité comme valeur fondamentale :</p>
                                    <ul>
                                        <li>Discipline et rigueur</li>
                                        <li>Respect des collaborateurs</li>
                                        <li>Engagement collectif</li>
                                        <li>Transparence et data-driven</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4 : GOUVERNANCE ET RESPONSABILITÉ ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-shield-check"></i> 4. Gouvernance et responsabilité</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5>🏛️ Structure multidimensionnelle</h5>
                                    <p>Chaque centre de responsabilité est une mini-entreprise autonome.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5>💱 Prix de transfert (double tarification)</h5>
                                    <p>Alignement des intérêts locaux avec la profitabilité globale. Centre "Siège" pour la transparence.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outil">
                                <div class="card-body">
                                    <h5>⚠️ Gestion des risques</h5>
                                    <p>Chaque manager est "Risk Owner". Indice de Risque Composite (IRC) pour prioriser les actions correctives.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 5 : TABLEAU DE BORD LEAN CONSOLIDÉ ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-bar-chart-steps"></i> 5. Tableau de bord consolidé – Gaspillages par centre</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Centre de responsabilité</th><th>Gaspillage majeur (Muda)</th><th>KPI Lean</th><th>Impact financier (est.)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Production</td><td>Défauts / Surproduction</td><td>Taux de conformité (%)</td><td>Coût des non-qualités</td></tr>
                                <tr><td>Assemblage</td><td>Attentes / Temps cycle</td><td>Durée cycle (heures)</td><td>Coût des retards</td></tr>
                                <tr><td>Distribution</td><td>Transports / Stocks</td><td>Taux rotation stock</td><td>Coûts logistiques</td></tr>
                                <tr><td>Administratif</td><td>Sur-traitement</td><td>Temps traitement dossier</td><td>Productivité (heures/dossier)</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 6 : ENGAGEMENT ET SYNTHÈSE ==================== -->
                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle-fill"></i> <strong>Engagement de la Direction :</strong> Cette charte n'est pas un document statique. Elle vit à travers nos rituels de gestion, nos réunions Kaizen et nos revues stratégiques trimestrielles. Chaque membre est ambassadeur de cette culture d'excellence.
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Rappel :</strong> La performance n'exclut pas la conformité. Un processus peut être efficace mais non conforme – l'audit interne est garant de cette vigilance.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
