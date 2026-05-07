<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Support SAV - OMEGA CONSULTING";
$page_icon = "headset";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-headset"></i> Support Après-Vente (SAV)</h5>
                <small>Assistance technique et pédagogique</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="card bg-light">
                            <div class="card-body">
                                <i class="bi bi-telephone fs-1 text-primary"></i>
                                <h6>Hotline technique</h6>
                                <p class="mb-0">+221 78 000 00 00</p>
                                <small>Lun-Ven 8h-18h</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="card bg-light">
                            <div class="card-body">
                                <i class="bi bi-envelope fs-1 text-success"></i>
                                <h6>Email support</h6>
                                <p class="mb-0">sav@omega-consulting.ci</p>
                                <small>Réponse sous 24h</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="card bg-light">
                            <div class="card-body">
                                <i class="bi bi-whatsapp fs-1 text-success"></i>
                                <h6>WhatsApp SAV</h6>
                                <p class="mb-0">+221 78 000 00 00</p>
                                <small>Assistance rapide</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i>
                    <strong>Avant de contacter le support :</strong>
                    <ul class="mt-2 mb-0">
                        <li>Consultez le <a href="formations/index.php">Centre de formation</a> pour les guides pas à pas</li>
                        <li>Visionnez les tutoriels vidéo dans chaque module de formation</li>
                        <li>Vérifiez le <a href="manuel_formation.php">Manuel utilisateur</a> pour les questions fréquentes</li>
                    </ul>
                </div>
                
                <div class="text-center mt-3">
                    <a href="formations/index.php" class="btn btn-primary">
                        <i class="bi bi-mortarboard"></i> Accéder au centre de formation
                    </a>
                    <a href="dashboard_expert.php" class="btn btn-outline-secondary">
                        <i class="bi bi-speedometer2"></i> Retour au dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
