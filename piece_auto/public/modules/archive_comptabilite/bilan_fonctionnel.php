<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Bilan fonctionnel (FRNG/BFR)";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2>Bilan fonctionnel (FRNG/BFR)</h2>
                </div>
                <div class="card-body">
                    <p>FRNG = Ressources stables – Emplois stables<br>BFRE = ACE – DE<br>Trésorerie nette = FRNG – BFR</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr><th>Emplois stables</th><td>1800 k€</td><th>Ressources stables</th><td>2300 k€</td></tr>
                            <tr><td colspan="2"><strong>FRNG = 500 k€</strong></td><td colspan="2">&nbsp;</td></tr>
                            <tr><th>Actif circulant exploitation</th><td>1200 k€</td><th>Dettes d'exploitation</th><td>750 k€</td></tr>
                            <tr><td colspan="2"><strong>BFRE = 450 k€</strong></td><td colspan="2">&nbsp;</td></tr>
                            <tr><th>Trésorerie active</th><td>80 k€</td><th>Trésorerie passive</th><td>30 k€</td></tr>
                            <tr><td colspan="2"><strong>Trésorerie nette = 50 k€</strong></td><td colspan="2">&nbsp;</td></tr>
                        </table>
                    </div>
                    <h4>Simulateur</h4>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-4"><label>Ressources stables (k€)</label><input type="number" name="rs" class="form-control" value="2300"></div>
                            <div class="col-md-4"><label>Emplois stables (k€)</label><input type="number" name="es" class="form-control" value="1800"></div>
                            <div class="col-md-4"><label>ACE (k€)</label><input type="number" name="ace" class="form-control" value="1200"></div>
                            <div class="col-md-4"><label>DE (k€)</label><input type="number" name="de" class="form-control" value="750"></div>
                        </div>
                        <button type="submit" name="simuler" class="btn btn-primary mt-3">Simuler</button>
                    </form>
                    <?php if(isset($_POST['simuler'])): 
                        $rs = (float)$_POST['rs']; $es = (float)$_POST['es']; $ace = (float)$_POST['ace']; $de = (float)$_POST['de'];
                        $frng = $rs - $es; $bfre = $ace - $de; $tn = $frng - $bfre;
                        echo "<div class='alert alert-success mt-3'>FRNG = $frng k€, BFRE = $bfre k€, Trésorerie nette = $tn k€</div>";
                    endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
