<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel - Prévisions et Programmation linéaire";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Manuel technique – Prévisions des ventes et optimisation par programmation linéaire</h5>
            </div>
            <div class="card-body">
                <h6>1. Prévisions des ventes</h6>
                <p>Le module <code>previsions_ventes.php</code> propose quatre méthodes quantitatives :</p>
                <ul>
                    <li><strong>Moindres carrés</strong> : ajustement linéaire des données historiques.</li>
                    <li><strong>Moyennes mobiles</strong> : lissage sur 3 mois.</li>
                    <li><strong>Lissage exponentiel</strong> (α=0.3) : pondération décroissante des observations passées.</li>
                    <li><strong>Méthode saisonnière</strong> : application de coefficients mensuels à la tendance linéaire.</li>
                </ul>
                <p>Les prévisions sont affichées mois par mois. Les coefficients saisonniers sont stockés dans la table <code>COEFFICIENTS_SAISONNIERS</code> et peuvent être mis à jour.</p>

                <h6>2. Programmation linéaire – Dualité</h6>
                <p>Le module <code>programmation_lineaire.php</code> résout un problème de maximisation de profit sous contraintes de ressources (2 produits, 3 ressources).</p>
                <ul>
                    <li><strong>Problème primal</strong> : maximiser Z = c₁x₁ + c₂x₂ avec contraintes A x ≤ b, x ≥ 0.</li>
                    <li><strong>Problème dual</strong> : minimiser W = bᵀy avec Aᵀy ≥ c, y ≥ 0. Les variables duales y représentent le prix implicite des ressources.</li>
                </ul>
                <p>La résolution utilise la méthode des sommets (graphique). Pour un nombre plus élevé de produits, il faudrait implémenter l’algorithme du simplexe complet.</p>

                <h6>3. Analyse des écarts</h6>
                <p>Le module <code>analyse_ecarts_avancee.php</code> calcule :</p>
                <ul>
                    <li>Écart sur prix et quantité pour les charges directes.</li>
                    <li>Écart sur chiffre d’affaires et marge.</li>
                    <li>Comparaison coûts standard / coûts réels par centre de profit.</li>
                </ul>

                <h6>4. Cas pratique</h6>
                <p><strong>Exemple :</strong> Une entreprise fabrique deux produits A et B. Les marges unitaires sont respectivement 40 et 30 FCFA. Les ressources (matières, main d’œuvre) sont limitées. Utilisez le module de programmation linéaire pour déterminer la production optimale et le profit maximal. Ensuite, dans le module d’analyse des écarts, comparez les coûts réels avec les standards.</p>

                <div class="alert alert-info">
                    🌐 Accès aux modules :<br>
                    - <a href="previsions_ventes.php">Prévisions des ventes</a><br>
                    - <a href="programmation_lineaire.php">Programmation linéaire</a><br>
                    - <a href="analyse_ecarts_avancee.php">Analyse des écarts</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
