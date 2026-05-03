<?php
require_once 'config.php';
require_once 'header.php';

// Récupérer le numéro de déclaration si fourni
$numero_declaration = isset($_GET['numero']) ? $_GET['numero'] : '';
$declaration_id = isset($_GET['id']) ? $_GET['id'] : '';

// Rechercher la déclaration
if($numero_declaration) {
    $stmt = $pdo->prepare("SELECT id FROM declarations_douane WHERE numero_declaration = ?");
    $stmt->execute([$numero_declaration]);
    $declaration = $stmt->fetch();
    if($declaration) {
        $declaration_id = $declaration['id'];
    }
}

// Récupérer les infos de la déclaration et son tracking
if($declaration_id) {
    $stmt = $pdo->prepare("SELECT d.*, c.raison_sociale 
                          FROM declarations_douane d 
                          JOIN clients c ON d.client_id = c.id 
                          WHERE d.id = ?");
    $stmt->execute([$declaration_id]);
    $declaration = $stmt->fetch();
    
    // Récupérer le tracking
    $stmt = $pdo->prepare("SELECT * FROM tracking 
                          WHERE declaration_id = ? 
                          ORDER BY date_etape DESC");
    $stmt->execute([$declaration_id]);
    $trackings = $stmt->fetchAll();
}
?>

<div class="row">
    <div class="col-md-12">
        <h1>Suivi des Déclarations en Douane</h1>
        <p class="text-muted">Suivez en temps réel l'évolution de vos déclarations</p>
        
        <!-- Formulaire de recherche -->
        <div class="card-modern p-4 mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <label>Numéro de déclaration</label>
                    <input type="text" name="numero" class="form-control" 
                           placeholder="Ex: DEC-2024-0001" 
                           value="<?php echo htmlspecialchars($numero_declaration); ?>">
                </div>
                <div class="col-md-4">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-omega w-100">
                        <i class="fas fa-search me-2"></i>Suivre
                    </button>
                </div>
            </form>
        </div>
        
        <?php if($declaration_id && $declaration): ?>
        <!-- Informations de la déclaration -->
        <div class="card-modern p-4 mb-4">
            <div class="row">
                <div class="col-md-6">
                    <h5>Déclaration N°: <?php echo $declaration['numero_declaration']; ?></h5>
                    <p><strong>Client:</strong> <?php echo $declaration['raison_sociale']; ?></p>
                    <p><strong>Type transport:</strong> <?php echo ucfirst($declaration['type_transport']); ?></p>
                    <p><strong>Régime:</strong> <?php echo ucfirst($declaration['regime']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Date dépôt:</strong> <?php echo date('d/m/Y', strtotime($declaration['date_depot'])); ?></p>
                    <p><strong>Valeur CIF:</strong> <?php echo number_format($declaration['valeur_cif'], 0, ',', ' '); ?> FCFA</p>
                    <p><strong>Statut actuel:</strong> 
                        <span class="badge bg-<?php 
                            echo $declaration['statut'] == 'acquitte' ? 'success' : 
                                ($declaration['statut'] == 'depose' ? 'info' : 
                                ($declaration['statut'] == 'controle' ? 'warning' : 'secondary')); 
                        ?>">
                            <?php echo ucfirst($declaration['statut']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Timeline de suivi -->
        <div class="card-modern p-4">
            <h5>Historique des étapes</h5>
            <div class="timeline">
                <?php if(count($trackings) > 0): ?>
                    <?php foreach($trackings as $track): ?>
                    <div class="timeline-item">
                        <div class="timeline-badge">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <h6><?php echo ucfirst($track['etape']); ?></h6>
                            <p><?php echo $track['description']; ?></p>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($track['date_etape'])); ?>
                                <?php if($track['lieu']): ?> - Lieu: <?php echo $track['lieu']; endif; ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Aucune étape enregistrée pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php elseif($numero_declaration): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Aucune déclaration trouvée avec ce numéro.
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 30px;
}

.timeline-badge {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    background: #004080;
    border-radius: 50%;
    text-align: center;
    line-height: 40px;
    color: white;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #004080;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -30px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}
</style>

<?php require_once 'footer.php'; ?>
