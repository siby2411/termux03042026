<?php
$page_title = "Guide de Formation - SYSCOHADA";
require_once 'inc_navbar.php';
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-journal-bookmark-fill"></i> Guide Pratique - La Comptabilité Expliquée Simplement</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="bi bi-question-circle"></i> C'est quoi une écriture comptable ?</h6>
                            <p>Une opération qui se traduit toujours par : <strong>Quelqu'un donne = Quelqu'un reçoit</strong></p>
                            <ul>
                                <li><span class="text-danger">DÉBIT (gauche)</span> = Ce qui entre dans l'entreprise</li>
                                <li><span class="text-success">CRÉDIT (droite)</span> = Ce qui sort de l'entreprise</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h6><i class="bi bi-lightbulb"></i> Règle d'or SYSCOHADA</h6>
                            <p><strong>Total DÉBIT = Total CRÉDIT</strong> (Principe de la partie double)</p>
                            <p>Exemple : Achat de marchandises 100.000 F payé par chèque</p>
                            <ul>
                                <li>DÉBIT : Stock marchandises 100.000 (ça entre)</li>
                                <li>CRÉDIT : Banque 100.000 (ça sort)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
