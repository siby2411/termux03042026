<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Analyse des ratios financiers";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2>Analyse des ratios financiers</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><div class="card bg-light"><div class="card-body"><strong>Marge nette</strong><br>Résultat net / CA<br>Seuil >5%</div></div></div>
                        <div class="col-md-4"><div class="card bg-light"><div class="card-body"><strong>ROE</strong><br>Résultat net / Capitaux propres<br>Seuil >10%</div></div></div>
                        <div class="col-md-4"><div class="card bg-light"><div class="card-body"><strong>Liquidité générale</strong><br>Actif circulant / Dettes CT<br>Seuil >1</div></div></div>
                    </div>
                    <h4 class="mt-4">Simulateur</h4>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-4"><label>CA (k€)</label><input type="number" name="ca" class="form-control" value="2500"></div>
                            <div class="col-md-4"><label>Résultat net (k€)</label><input type="number" name="rn" class="form-control" value="120"></div>
                            <div class="col-md-4"><label>Capitaux propres (k€)</label><input type="number" name="cp" class="form-control" value="800"></div>
                            <div class="col-md-4"><label>Actif circulant (k€)</label><input type="number" name="ac" class="form-control" value="1200"></div>
                            <div class="col-md-4"><label>Dettes CT (k€)</label><input type="number" name="dct" class="form-control" value="750"></div>
                        </div>
                        <button type="submit" name="calculer" class="btn btn-primary mt-3">Calculer</button>
                    </form>
                    <?php if(isset($_POST['calculer'])): 
                        $ca = (float)$_POST['ca']; $rn = (float)$_POST['rn']; $cp = (float)$_POST['cp'];
                        $ac = (float)$_POST['ac']; $dct = (float)$_POST['dct'];
                        $marge_nette = ($ca>0)?$rn/$ca*100:0; $roe=($cp>0)?$rn/$cp*100:0; $lg=($dct>0)?$ac/$dct:0;
                        echo "<div class='alert alert-success mt-3'>Marge nette = ".round($marge_nette,1)."%, ROE = ".round($roe,1)."%, Liquidité générale = ".round($lg,2)."</div>";
                    endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
