<?php
// administration.php - Version corrigée
require_once 'config/database.php';
require_once 'includes/header.php';

// Vérifier si admin
if ($_SESSION['role'] !== 'admin') {
    die('<div class="alert alert-danger m-4">Accès réservé aux administrateurs</div>');
}

// Récupérer les statistiques
$stats = [];
try {
    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $stmt->fetchColumn();
    
    // Compter les écritures
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM journal");
    $stats['ecritures'] = $stmt->fetchColumn();
    
    // Dernière activité
    $stmt = $pdo->query("SELECT MAX(date_derniere_connexion) as last FROM users");
    $stats['last_activity'] = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $stats['error'] = $e->getMessage();
}
?>

<div class="p-4">
    <h2><i class="bi bi-gear me-2"></i>Administration</h2>
    
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
        <div class="col">
            <div class="sysco-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Utilisateurs</h6>
                            <h3 class="mb-0"><?= $stats['users'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-people fs-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="sysco-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Écritures</h6>
                            <h3 class="mb-0"><?= $stats['ecritures'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-journal-text fs-1 text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="sysco-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Dernière activité</h6>
                            <h5 class="mb-0"><?= $stats['last_activity'] ?? 'Aucune' ?></h5>
                        </div>
                        <i class="bi bi-clock-history fs-1 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
