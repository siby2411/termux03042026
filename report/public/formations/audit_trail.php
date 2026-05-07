<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Formation - Audit Trail";
$page_icon = "eye";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5><i class="bi bi-eye"></i> Formation : Audit Trail (Traçabilité)</h5>
                <small>Surveiller et tracer toutes les actions dans le système</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>📖 Qu'est-ce que l'Audit Trail ?</strong>
                    <p>C'est un journal qui enregistre TOUTES les actions effectuées dans l'application : création, modification, suppression, connexion.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-primary text-white">🔍 Types d'actions tracées</div>
                            <div class="card-body">
                                <ul>
                                    <li><span class="badge bg-success">INSERT</span> - Création d'un enregistrement</li>
                                    <li><span class="badge bg-warning">UPDATE</span> - Modification</li>
                                    <li><span class="badge bg-danger">DELETE</span> - Suppression</li>
                                    <li><span class="badge bg-info">LOGIN/LOGOUT</span> - Connexions</li>
                                    <li><span class="badge bg-secondary">EXPORT</span> - Export de données</li>
                                    <li><span class="badge bg-primary">VALIDATE</span> - Validation comptable</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">🔒 Obligations légales</div>
                            <div class="card-body">
                                <ul>
                                    <li>Conservation minimale : <strong>10 ans</strong></li>
                                    <li>Inchangéabilité : <strong>Les logs ne peuvent être modifiés</strong></li>
                                    <li>Accessible pour contrôle fiscal</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3 text-center">
                    <i class="bi bi-shield-lock"></i>
                    <strong>Seuls les administrateurs peuvent consulter l'Audit Trail</strong>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../audit_trail.php" class="btn btn-dark">Consulter l'Audit Trail</a>
                    <a href="declarations_fiscales.php" class="btn btn-outline-primary">Module suivant →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
