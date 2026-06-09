<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Manuel de formation – Audit interne & contrôle interne";
include 'inc_navbar.php';
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
        .card-chapitre { border-left: 5px solid #0d6efd; margin-bottom: 25px; transition: 0.2s; }
        .card-chapitre:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .formule { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; text-align: center; font-size: 1.2rem; }
        .exemple { background: #e9ecef; padding: 12px; border-radius: 8px; margin: 10px 0; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-journal-bookmark-fill"></i> Manuel de formation – Audit interne & contrôle interne</h2>
                    <p>Conforme aux normes IIA, COSO II ERM et SYSCOHADA</p>
                </div>
                <div class="card-body">

                    <!-- ==================== CHAPITRE 1 ==================== -->
                    <div class="card card-chapitre">
                        <div class="card-header bg-light fw-bold">Chapitre 1 : Fondements et compréhension du domaine</div>
                        <div class="card-body">
                            <p>L'audit n'est pas une simple vérification comptable, c'est une <strong>aide au pilotage de la performance</strong>.</p>
                            <h5>📌 Pyramide des objectifs (Cadre COSO II) :</h5>
                            <ul>
                                <li><strong>Stratégiques</strong> : Objectifs de haut niveau alignés sur la mission.</li>
                                <li><strong>Opérationnels</strong> : Efficacité et efficience de l'utilisation des ressources.</li>
                                <li><strong>Fiabilité</strong> : Intégrité des rapports financiers et opérationnels.</li>
                                <li><strong>Conformité</strong> : Respect des lois, règlements et politiques internes.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 2 ==================== -->
                    <div class="card card-chapitre">
                        <div class="card-header bg-light fw-bold">Chapitre 2 : Identification et analyse des processus</div>
                        <div class="card-body">
                            <p>L'auditeur doit cartographier les processus avant d'intervenir.</p>
                            <h5>📊 Typologie des processus :</h5>
                            <ul>
                                <li><strong>Pilotage</strong> : Stratégie et gouvernance.</li>
                                <li><strong>Réalisation</strong> : Cœur de métier (Production, Ventes).</li>
                                <li><strong>Support</strong> : Ressources humaines, SI, Comptabilité.</li>
                                <li><strong>Mesure</strong> : Audit interne, Contrôle de gestion.</li>
                            </ul>
                            <div class="exemple">
                                <i class="bi bi-info-circle"></i> <strong>Collecte d'informations :</strong> Utiliser les sources documentaires (organigrammes, manuels de procédures, contrats) pour comprendre les flux de données.
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 3 ==================== -->
                    <div class="card card-chapitre">
                        <div class="card-header bg-light fw-bold">Chapitre 3 : Indicateurs de performance (KPIs)</div>
                        <div class="card-body">
                            <p>Un indicateur n'est pertinent que s'il est <strong>SMART</strong> (Spécifique, Mesurable, Atteignable, Réaliste, Temporel).</p>
                            <h5>📈 Critères d'évaluation :</h5>
                            <ul>
                                <li>Pertinence</li>
                                <li>Disponibilité</li>
                                <li>Compréhension par les acteurs</li>
                            </ul>
                            <div class="exemple">
                                <i class="bi bi-graph-up"></i> <strong>Gestion des écarts :</strong> Définir la tolérance au risque. Toute déviation significative entre performance réelle et attendue déclenche une mission d'audit.
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 4 ==================== -->
                    <div class="card card-chapitre">
                        <div class="card-header bg-light fw-bold">Chapitre 4 : La cartographie des risques (Cœur technique)</div>
                        <div class="card-body">
                            <p>Le risque est tout événement empêchant l'atteinte des objectifs.</p>
                            <div class="formule">Criticité = Probabilité × Impact</div>
                            <h5 class="mt-3">🔍 Méthodologie d'évaluation :</h5>
                            <ol>
                                <li><strong>Identification</strong> : Inventaire des menaces potentielles.</li>
                                <li><strong>Mesure</strong> : Évaluation sur une échelle de 1 à 5 (Probabilité × Impact).</li>
                                <li><strong>Hiérarchisation</strong> : Placement des risques sur la Heat Map pour une prise de décision rapide.</li>
                            </ol>
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered">
                                    <thead class="table-dark"><tr><th>Score</th><th>Niveau de criticité</th><th>Action</th></tr></thead>
                                    <tbody>
                                        <tr><td>1-8</td><td><span class="badge bg-success">Faible</span></td><td>Risque acceptable – Surveillance simple</td></tr>
                                        <tr><td>9-15</td><td><span class="badge bg-warning">Modéré</span></td><td>Plan d'action à moyen terme</td></tr>
                                        <tr><td>16-25</td><td><span class="badge bg-danger">Majeur</span></td><td>Action immédiate – Audit prioritaire</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 5 ==================== -->
                    <div class="card card-chapitre">
                        <div class="card-header bg-light fw-bold">Chapitre 5 : Tests de conformité – Cycle Achat-Fournisseur</div>
                        <div class="card-body">
                            <h5>📋 Test 1 : Séparation des tâches (incompatibilités)</h5>
                            <p>Vérifier qu'une même personne ne peut pas réaliser deux étapes critiques (création fournisseur + validation paiement).</p>
                            <h5>📋 Test 2 : Three-Way Match (exhaustivité et réalité)</h5>
                            <p>Chaque facture doit être rapprochée de :</p>
                            <ul><li>Bon de commande (autorisation)</li><li>Bon de réception (preuve de livraison)</li></ul>
                            <h5>📋 Test 3 : Conformité fiscale (TVA et acomptes)</h5>
                            <p>Vérifier que la TVA déductible est appuyée par une facture conforme SYSCOHADA.</p>
                            <h5>📋 Test 4 : Cut-off (évaluation et rattachement)</h5>
                            <p>Analyser les factures reçues en début d'exercice N+1 pour identifier les charges de l'exercice N.</p>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 6 ==================== -->
                    <div class="card card-chapitre">
                        <div class="card-header bg-light fw-bold">Chapitre 6 : De l'anomalie à la recommandation (Méthode CAR)</div>
                        <div class="card-body">
                            <h5>🔹 Constat (l'anomalie)</h5>
                            <div class="exemple">"Nous avons identifié 15% de factures fournisseurs non rapprochées de bons de réception."</div>
                            <h5>🔹 Analyse (la cause racine – méthode des 5 pourquoi)</h5>
                            <div class="exemple">Pourquoi ? → La réception n'a pas été saisie. Pourquoi ? → Le magasinier n'a pas accès au logiciel...</div>
                            <h5>🔹 Recommandation (le plan d'action SMART)</h5>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm">
                                    <tr><th>Action</th><td>Modifier le workflow de validation des factures</td></tr>
                                    <tr><th>Responsable</th><td>Directeur des Achats</td></tr>
                                    <tr><th>Délai</th><td>31 juillet 2026</td></tr>
                                    <tr><th>Résultat attendu</th><td>Réduction de 90% des factures non rapprochées</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 7 ==================== -->
                    <div class="card card-chapitre">
                        <div class="card-header bg-light fw-bold">Chapitre 7 : Outils et automatisation (volet technique)</div>
                        <div class="card-body">
                            <h5>💻 Architecture de saisie</h5>
                            <p>Formulaire sécurisé (HTML/PHP) avec calcul automatique de la criticité.</p>
                            <h5>📊 Visualisation</h5>
                            <p>Utilisation de <strong>Chart.js</strong> pour générer des Heat Maps dynamiques.</p>
                            <h5>🗄️ Traçabilité</h5>
                            <p>Archivage des audits dans une base MariaDB (horodatage, ID auditeur, statut).</p>
                            <h5>📄 Reporting</h5>
                            <p>Exportation automatique des fiches d'audit au format PDF via Dompdf.</p>
                            <div class="alert alert-info mt-2">
                                <i class="bi bi-lightbulb"></i> <strong>Guide d'utilisation :</strong> "La performance n'exclut pas la conformité." Utilisez les outils de saisie automatisés pour chaque mission et assurez-vous que chaque risque identifié est documenté par une mesure de contrôle associée.
                            </div>
                        </div>
                    </div>

                    <!-- ==================== CHAPITRE 8 ==================== -->
                    <div class="card card-chapitre">
                        <div class="card-header bg-light fw-bold">Chapitre 8 : Document de spécifications fonctionnelles (DSF)</div>
                        <div class="card-body">
                            <h5>📌 Modules fonctionnels :</h5>
                            <ul>
                                <li><strong>Module A</strong> : Analyse financière & conformité (Z-Score, ratios, retraitements)</li>
                                <li><strong>Module B</strong> : Cartographie des risques (criticité P×I, Heat Map)</li>
                                <li><strong>Module C</strong> : Suivi du plan d'action (PAC) avec alertes mail automatiques</li>
                            </ul>
                            <h5>📅 Roadmap de déploiement :</h5>
                            <ul>
                                <li><strong>S1</strong> : Base de données et vues SQL</li>
                                <li><strong>S2</strong> : Formulaires de saisie sécurisés</li>
                                <li><strong>S3</strong> : Visualisations graphiques et exports PDF</li>
                                <li><strong>S4</strong> : Tests de conformité et recette</li>
                            </ul>
                        </div>
                    </div>

                    <!-- ==================== CONCLUSION ==================== -->
                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle"></i> <strong>Conclusion :</strong> Ce manuel constitue la base de votre système de gestion de l'audit. La réussite repose sur la documentation rigoureuse des risques, la traçabilité des actions et l'utilisation d'outils automatisés conformes aux normes SYSCOHADA et COSO II.
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Rappel d'expert :</strong> Un processus peut être extrêmement efficace (performance opérationnelle) mais totalement illégal (non-conformité). Votre tableau de bord doit colorer en rouge tout processus dont l'indicateur de conformité est défaillant.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
