<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Flux de trésorerie (OEC)";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2>Tableau des flux de trésorerie (OEC)</h2>
                </div>
                <div class="card-body">
                    <p>Variation de trésorerie = Flux net d'activité + Flux net d'investissement + Flux net de financement</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light"><div class="card-body">
                                <strong>Flux d'activité</strong><br>MBA (marge brute d'autofinancement) – Δ BFRE
                            </div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light"><div class="card-body">
                                <strong>Flux d'investissement</strong><br>Acquisitions – Cessions
                            </div></div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <div class="card bg-light"><div class="card-body">
                                <strong>Flux de financement</strong><br>Augmentation capital – Dividendes + Nouveaux emprunts – Remboursements
                            </div></div>
                        </div>
                    </div>
                    <h4 class="mt-4">Simulateur</h4>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-3"><label>MBA (k€)</label><input type="number" name="mba" class="form-control" value="280"></div>
                            <div class="col-md-3"><label>Δ BFRE</label><input type="number" name="var_bfre" class="form-control" value="-20"></div>
                            <div class="col-md-3"><label>Acquisitions</label><input type="number" name="acq" class="form-control" value="150"></div>
                            <div class="col-md-3"><label>Cessions</label><input type="number" name="cess" class="form-control" value="30"></div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3"><label>Augmentation capital</label><input type="number" name="aug_cap" class="form-control" value="50"></div>
                            <div class="col-md-3"><label>Dividendes versés</label><input type="number" name="div" class="form-control" value="40"></div>
                            <div class="col-md-3"><label>Nouveaux emprunts</label><input type="number" name="emp_new" class="form-control" value="100"></div>
                            <div class="col-md-3"><label>Remboursements</label><input type="number" name="emp_remb" class="form-control" value="60"></div>
                        </div>
                        <button type="submit" name="simuler" class="btn btn-primary mt-3">Calculer variation</button>
                    </form>
                    <?php if(isset($_POST['simuler'])): 
                        $mba = (float)$_POST['mba']; $var = (float)$_POST['var_bfre']; $acq = (float)$_POST['acq']; $cess = (float)$_POST['cess'];
                        $aug_cap = (float)$_POST['aug_cap']; $div = (float)$_POST['div']; $emp_new = (float)$_POST['emp_new']; $emp_remb = (float)$_POST['emp_remb'];
                        $flux_act = $mba - $var; $flux_inv = -$acq + $cess; $flux_fin = $aug_cap - $div + $emp_new - $emp_remb;
                        $var_tres = $flux_act + $flux_inv + $flux_fin;
                        echo "<div class='alert alert-success mt-3'>Flux activité = $flux_act, Flux investissement = $flux_inv, Flux financement = $flux_fin → Variation trésorerie = $var_tres k€</div>";
                    endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
