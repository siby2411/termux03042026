<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Manuel d'affectation du résultat";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-primary text-white"><h5>Manuel d'affectation du résultat</h5></div><div class="card-body">
<h6>Principes généraux</h6>
<p>Le résultat net de l'exercice (bénéfice ou perte) doit être affecté selon les règles légales et statutaires.</p>
<h6>1. Pour un bénéfice</h6>
<ul><li><strong>Réserve légale</strong> : 5% du bénéfice jusqu'à 10% du capital social (obligatoire).</li>
<li><strong>Réserve statutaire</strong> : selon les statuts.</li>
<li><strong>Dividendes</strong> : distribution aux associés.</li>
<li><strong>Report à nouveau créditeur</strong> : solde non distribué.</li></ul>
<h6>2. Pour une perte</h6>
<ul><li>La perte est imputée sur les réserves disponibles.</li>
<li>Le solde est porté au <strong>report à nouveau débiteur</strong> (compte 113).</li></ul>
<h6>Chronologie</h6>
<ol><li>Calcul du résultat net (compte 120).</li>
<li>Proposition d'affectation par la direction.</li>
<li>Approbation par l'assemblée générale.</li>
<li>Enregistrement comptable des affectations.</li>
<li>Clôture du compte 120 (report à nouveau).</li></ol>
<p>Utilisez le module <a href="affectation_resultat.php">Affectation du résultat</a> pour simuler et enregistrer vos propositions.</p>
</div></div></div></div>
<?php include 'inc_footer.php'; ?>
