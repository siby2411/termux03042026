<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "EBE - EBITDA - CAF - Free cash‑flow";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="fas fa-chart-line"></i> Diagnostic financier approfondi</h2>
                    <p>EBE, EBITDA, CAF, autofinancement, free cash‑flow, ETE, BFRE – aide à la décision stratégique</p>
                </div>
                <div class="card-body">

                    <!-- Partie théorique -->
                    <h3 class="mt-3"><i class="fas fa-book-open"></i> 1. Concepts clés</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card indicator">
                                <div class="card-header bg-light">Excédent Brut d’Exploitation (EBE)</div>
                                <div class="card-body">
                                    <p>L’EBE mesure la performance économique <strong>avant</strong> : dotations, provisions, résultats financiers, impôts.</p>
                                    <div class="formula">EBE = Produits d’exploitation – Charges d’exploitation (hors dotations)</div>
                                    <p><strong>Interprétation :</strong> Un EBE négatif sur 3 ans signale une asphyxie financière.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card indicator">
                                <div class="card-header bg-light">EBITDA</div>
                                <div class="card-body">
                                    <p>Indicateur international proche de l’EBE.</p>
                                    <div class="formula">EBITDA = EBE (ou résultat d’exploitation + dotations)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card indicator">
                                <div class="card-header bg-light">Capacité d’autofinancement (CAF)</div>
                                <div class="card-body">
                                    <div class="formula">CAF = Résultat net + Dotations – Reprises – Produits de cession</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card indicator">
                                <div class="card-header bg-light">Free cash‑flow (FCF)</div>
                                <div class="card-body">
                                    <div class="formula">FCF = CAF – Investissements – ΔBFRE</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cas pratique -->
                    <h3 class="mt-5"><i class="fas fa-calculator"></i> 2. Cas pratique : société fictive</h3>
                    <?php
                    $data = [
                        'ca' => 100, 'achats_consommes' => 20, 'charges_externes' => 10, 'charges_personnel' => 10,
                        'impots_taxes' => 2, 'dotations_amortissements' => 4, 'dotations_provisions' => 2,
                        'autres_charges_op' => 8, 'produits_financiers' => 4, 'charges_financieres' => 7,
                        'is' => 12, 'dividendes' => 10, 'investissements' => 6, 'var_bfre' => 3
                    ];
                    $ebitda = $data['ca'] - ($data['achats_consommes']+$data['charges_externes']+$data['charges_personnel']+$data['impots_taxes']);
                    $resultat_operationnel_courant = $ebitda - $data['dotations_amortissements'] - $data['dotations_provisions'];
                    $resultat_operationnel = $resultat_operationnel_courant - $data['autres_charges_op'];
                    $cout_endettement_net = $data['charges_financieres'] - $data['produits_financiers'];
                    $resultat_net = $resultat_operationnel - $cout_endettement_net - $data['is'];
                    $caf = $resultat_net + $data['dotations_amortissements'] + $data['dotations_provisions'];
                    $autofinancement = $caf - $data['dividendes'];
                    $free_cash_flow = $autofinancement + $cout_endettement_net - $data['investissements'] - $data['var_bfre'];
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark"><tr><th>Poste</th><th>Montant (k€)</th><th>Calcul</th></tr></thead>
                            <tbody>
                                <tr><td>EBITDA</td><td><?= $ebitda ?></td><td>CA – achats – externes – personnel – impôts</td></tr>
                                <tr><td>Résultat net</td><td><?= $resultat_net ?></td><td>Résultat op. – coût endettement – IS</td></tr>
                                <tr class="table-success"><td>CAF</td><td><?= $caf ?></td><td>Résultat net + dotations</td></tr>
                                <tr class="table-success"><td>Autofinancement</td><td><?= $autofinancement ?></td><td>CAF – dividendes</td></tr>
                                <tr class="table-danger"><td>Free cash‑flow</td><td><?= $free_cash_flow ?></td><td>Autofinancement + charges financières – investissements – ΔBFRE</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Simulateur -->
                    <h3 class="mt-4"><i class="fas fa-sliders-h"></i> 3. Simulateur interactif</h3>
                    <div class="card bg-light">
                        <div class="card-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-3"><label>CA (k€)</label><input type="number" name="ca" class="form-control" value="100"></div>
                                    <div class="col-md-3"><label>Achats</label><input type="number" name="achats" class="form-control" value="20"></div>
                                    <div class="col-md-3"><label>Charges externes</label><input type="number" name="externes" class="form-control" value="10"></div>
                                    <div class="col-md-3"><label>Personnel</label><input type="number" name="personnel" class="form-control" value="10"></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3"><label>Impôts & taxes</label><input type="number" name="impots" class="form-control" value="2"></div>
                                    <div class="col-md-3"><label>Dotations amort.</label><input type="number" name="amort" class="form-control" value="4"></div>
                                    <div class="col-md-3"><label>Dotations prov.</label><input type="number" name="prov" class="form-control" value="2"></div>
                                    <div class="col-md-3"><label>Autres charges op.</label><input type="number" name="autres" class="form-control" value="8"></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3"><label>Produits financiers</label><input type="number" name="prod_fin" class="form-control" value="4"></div>
                                    <div class="col-md-3"><label>Charges financières</label><input type="number" name="charges_fin" class="form-control" value="7"></div>
                                    <div class="col-md-3"><label>IS</label><input type="number" name="is" class="form-control" value="12"></div>
                                    <div class="col-md-3"><label>Dividendes</label><input type="number" name="dividendes" class="form-control" value="10"></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3"><label>Investissements</label><input type="number" name="invest" class="form-control" value="6"></div>
                                    <div class="col-md-3"><label>Δ BFRE</label><input type="number" name="var_bfre" class="form-control" value="3"></div>
                                </div>
                                <button type="submit" name="simuler" class="btn btn-primary mt-3">Recalculer</button>
                            </form>
                            <?php if(isset($_POST['simuler'])): 
                                $s_ca = (float)$_POST['ca']; $s_achats = (float)$_POST['achats']; $s_externes = (float)$_POST['externes'];
                                $s_personnel = (float)$_POST['personnel']; $s_impots = (float)$_POST['impots']; $s_amort = (float)$_POST['amort'];
                                $s_prov = (float)$_POST['prov']; $s_autres = (float)$_POST['autres']; $s_prod_fin = (float)$_POST['prod_fin'];
                                $s_charges_fin = (float)$_POST['charges_fin']; $s_is = (float)$_POST['is']; $s_div = (float)$_POST['dividendes'];
                                $s_invest = (float)$_POST['invest']; $s_var_bfre = (float)$_POST['var_bfre'];
                                $s_ebitda = $s_ca - ($s_achats+$s_externes+$s_personnel+$s_impots);
                                $s_roc = $s_ebitda - $s_amort - $s_prov;
                                $s_ro = $s_roc - $s_autres;
                                $s_cout_fin = $s_charges_fin - $s_prod_fin;
                                $s_rnet = $s_ro - $s_cout_fin - $s_is;
                                $s_caf = $s_rnet + $s_amort + $s_prov;
                                $s_auto = $s_caf - $s_div;
                                $s_fcf = $s_auto + $s_cout_fin - $s_invest - $s_var_bfre;
                                echo "<div class='alert alert-success mt-3'><strong>Résultats :</strong> EBITDA = $s_ebitda k€, CAF = $s_caf k€, Free cash-flow = $s_fcf k€</div>";
                            endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
