<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Équilibre financier (6 situations)";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2>Équilibre financier – 6 situations</h2>
                </div>
                <div class="card-body">
                    <p><strong>Règle d'or :</strong> FRNG = Ressources stables – Emplois stables<br>BFR = Actif circulant – Passif circulant<br>Trésorerie nette = FRNG – BFR</p>
                    <div class="row">
                        <div class="col-md-4"><div class="alert alert-success">Situation 1 : FRNG>0, BFR>0, TN>0 → équilibre sain</div></div>
                        <div class="col-md-4"><div class="alert alert-warning">Situation 2 : FRNG>0, BFR>0, TN<0 → découvert</div></div>
                        <div class="col-md-4"><div class="alert alert-danger">Situation 3 : FRNG<0, BFR>0, TN<0 → déséquilibre grave</div></div>
                        <div class="col-md-4"><div class="alert alert-info">Situation 4 : FRNG>0, BFR<0, TN>0 → excédent de ressources</div></div>
                        <div class="col-md-4"><div class="alert alert-danger">Situation 5 : FRNG<0, BFR<0, TN<0 → double déséquilibre</div></div>
                        <div class="col-md-4"><div class="alert alert-warning">Situation 6 : FRNG<0, BFR>0, TN>0 → cas rare</div></div>
                    </div>
                    <h4>Simulateur</h4>
                    <form method="post" class="row g-3">
                        <div class="col-md-6"><label>FRNG (k€)</label><input type="number" name="frng" class="form-control" value="150"></div>
                        <div class="col-md-6"><label>BFR (k€)</label><input type="number" name="bfr" class="form-control" value="120"></div>
                        <div class="col-12"><button type="submit" class="btn btn-primary">Analyser</button></div>
                    </form>
                    <?php if(isset($_POST['frng'])): $f=$_POST['frng']; $b=$_POST['bfr']; $t=$f-$b; ?>
                    <div class="alert alert-secondary mt-3">FRNG=<?=$f?>, BFR=<?=$b?>, TN=<?=$t?> → Situation 
                    <?php if($f>0 && $b>0 && $t>0) echo "1"; elseif($f>0 && $b>0 && $t<0) echo "2"; elseif($f<0 && $b>0 && $t<0) echo "3";
                    elseif($f>0 && $b<0 && $t>0) echo "4"; elseif($f<0 && $b<0 && $t<0) echo "5"; elseif($f<0 && $b>0 && $t>0) echo "6";
                    else echo "cas particulier"; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
