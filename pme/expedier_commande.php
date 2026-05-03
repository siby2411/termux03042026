<?php
include 'includes/db.php';
include 'includes/header.php';

$id = (int)$_GET['id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suivi = $_POST['numero_suivi'];
    $transp = $_POST['transporteur'];

    $stmt = $pdo->prepare("UPDATE commandes SET numero_suivi = ?, transporteur = ?, etat = 'expediee', date_expedition = NOW() WHERE id = ?");
    $stmt->execute([$suivi, $transp, $id]);
    
    echo "<div class='alert alert-success'>Commande expédiée ! Le lien de suivi a été généré.</div>";
}

$cmd = $pdo->prepare("SELECT * FROM commandes WHERE id = ?");
$cmd->execute([$id]);
$c = $cmd->fetch();
?>

<div class="container mt-4">
    <div class="card border-0 shadow-sm col-md-6 mx-auto">
        <div class="card-header bg-success text-white">📦 Expédition Commande #<?= $id ?></div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Transporteur</label>
                    <select name="transporteur" class="form-select" required>
                        <option value="DHL Express">DHL Express</option>
                        <option value="FedEx">FedEx</option>
                        <option value="Chronopost">Chronopost</option>
                        <option value="Dakar Logistique">Dakar Logistique</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Numéro de Suivi (Tracking ID)</label>
                    <input type="text" name="numero_suivi" class="form-control" placeholder="ex: SN-99887766" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Valider l'envoi au client</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
