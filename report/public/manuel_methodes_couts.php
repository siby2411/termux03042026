<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Manuel méthodes de coûts";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-calculator"></i> Manuel de formation : Méthodes de calcul des coûts</h5>
                    <small>Coûts complets, coûts variables, coûts directs, méthode ABC, coûts standards</small>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>📚 SOMMAIRE</strong>
                        <ol class="mb-0 mt-2">
                            <li>Coûts complets – principe et mise en œuvre</li>
                            <li>Coûts variables – marge sur coût variable</li>
                            <li>Coûts directs – imputation directe</li>
                            <li>Méthode ABC – Activity Based Costing</li>
                            <li>Coûts préétablis (standards) et analyse des écarts</li>
                            <li>Comparaison des méthodes – avantages / inconvénients</li>
                            <li>Cas pratique : fabrication de deux produits</li>
                        </ol>
                    </div>

                    <!-- 1. Coûts complets -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">1. Coûts complets</div><div class="card-body">
                        <p>Le coût complet prend en compte l’ensemble des charges (directes et indirectes). Il nécessite la répartition des charges indirectes via des centres d’analyse et des unités d’œuvre.</p>
                        <p><strong>Utilité :</strong> valorisation des stocks, prix de vente, résultat analytique.</p>
                    </div></div>

                    <!-- 2. Coûts variables -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">2. Coûts variables</div><div class="card-body">
                        <p>Seules les charges variables sont affectées aux produits. La marge sur coût variable (MSCV) sert à calculer le seuil de rentabilité.</p>
                        <div class="alert alert-success">MSCV = CA – Coûts variables ; Résultat = MSCV – Charges fixes</div>
                    </div></div>

                    <!-- 3. Coûts directs -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">3. Coûts directs</div><div class="card-body">
                        <p>On n’affecte aux produits que les charges directement identifiables (matières, main-d’œuvre directe). Les charges indirectes sont traitées en bloc au niveau de l’entreprise.</p>
                        <p><strong>Inconvénient :</strong> ne donne pas une vision complète de la rentabilité par produit.</p>
                    </div></div>

                    <!-- 4. Méthode ABC -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">4. Méthode ABC (Activity Based Costing)</div><div class="card-body">
                        <p>Elle identifie les activités qui consomment des ressources et utilise des inducteurs de coûts (nombre de commandes, de set-up, d’heures de contrôle, etc.).</p>
                        <p><strong>Avantage :</strong> meilleure affectation des coûts indirects, surtout en production multiproduits.</p>
                    </div></div>

                    <!-- 5. Coûts standards -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">5. Coûts préétablis (standards) et analyse des écarts</div><div class="card-body">
                        <p>Un coût standard est un coût de référence calculé a priori. L’écart entre le coût réel et le coût standard est analysé pour contrôler la performance.</p>
                        <div class="alert alert-primary">Écart global = Coût réel – Coût standard = Écart sur quantité + Écart sur prix + Écart sur rendement</div>
                    </div></div>

                    <!-- 6. Comparaison -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">6. Comparaison des méthodes</div><div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr><th>Méthode</th><th>Avantages</th><th>Inconvénients</th></tr>
                                <tr><td>Coûts complets</td><td>Conforme aux normes, valorise les stocks</td><td>Arbitraire des clés de répartition</td></tr>
                                <tr><td>Coûts variables</td><td>Simple, adapté au seuil de rentabilité</td><td>Ignore les coûts fixes unitaires</td></tr>
                                <tr><td>Coûts directs</td><td>Très simple, évite les répartitions</td><td>Ne donne pas le coût de revient complet</td></tr>
                                <tr><td>ABC</td><td>Précision des coûts indirects, pilotage par activité</td><td>Lourde à mettre en place</td></tr>
                            </table>
                        </div>
                    </div></div>

                    <!-- 7. Cas pratique -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">7. Cas pratique : deux produits (A et B)</div><div class="card-body">
                        <p>Données :</p>
                        <ul><li>Produit A : 1000 u, matière 10€/u, MOD 5€/u, lots de fabrication 20</li>
                        <li>Produit B : 500 u, matière 12€/u, MOD 6€/u, lots de fabrication 10</li>
                        <li>Frais de lancement : 2000 € (coût indirect – inducteur = nombre de lots)</li></ul>
                        <p>Méthode des coûts complets (répartition des frais de lancement) :</p>
                        <div class="alert alert-success">
                            Coût indirect unitaire A = (2000 × 20/30) / 1000 = 1,33 €<br>
                            Coût indirect unitaire B = (2000 × 10/30) / 500 = 1,33 €<br>
                            Coût complet unitaire A = 10+5+1,33 = 16,33 € ; B = 12+6+1,33 = 19,33 €
                        </div>
                    </div></div>

                    <div class="alert alert-info mt-3">
                        <strong>🌐 MODULES ASSOCIÉS :</strong><br>
                        <a href="couts_complets.php" class="btn btn-sm btn-primary">Coûts complets</a>
                        <a href="couts_variables.php" class="btn btn-sm btn-primary">Coûts variables</a>
                        <a href="couts_directs.php" class="btn btn-sm btn-primary">Coûts directs</a>
                        <a href="couts_abc.php" class="btn btn-sm btn-primary">Méthode ABC</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
