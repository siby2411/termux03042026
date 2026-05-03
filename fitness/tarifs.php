<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM disciplines WHERE actif=1 ORDER BY nom";
$disciplines = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-tags"></i> Tarification des Disciplines</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach($disciplines as $d): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-fist-raised fa-3x mb-3" style="color: var(--primary)"></i>
                            <h4><?= htmlspecialchars($d['nom']) ?></h4>
                            <p class="text-muted"><?= htmlspecialchars($d['description']) ?></p>
                            <hr>
                            <div class="pricing">
                                <p><strong>Mensuel:</strong> <?= number_format($d['tarif_mensuel'], 0, ',', ' ') ?> FCFA</p>
                                <p><strong>Trimestriel:</strong> <?= number_format($d['tarif_trimestriel'], 0, ',', ' ') ?> FCFA</p>
                                <p><strong>Annuel:</strong> <?= number_format($d['tarif_annuel'], 0, ',', ' ') ?> FCFA</p>
                                <p><strong>Cours libre:</strong> <?= number_format($d['tarif_cours_libre'], 0, ',', ' ') ?> FCFA/séance</p>
                            </div>
                            <button class="btn btn-primary mt-3" onclick="window.location.href='adherents.php'">
                                S'inscrire
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
