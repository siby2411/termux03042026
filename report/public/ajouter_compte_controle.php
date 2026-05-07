<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$page_title = "Ajouter un compte - Plan comptable SYSCOHADA";
$page_icon = "plus-circle";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $compte_id = (int)$_POST['compte_id'];
    $intitule = trim($_POST['intitule']);
    $classe = floor($compte_id / 100);
    
    // Validation SYSCOHADA
    $classes_autorisees = [1,2,3,4,5,6,7,8];
    
    if ($compte_id < 1 || $compte_id > 99999) {
        $error = "❌ ERREUR SYSCOHADA: Le numéro de compte doit être compris entre 1 et 99999";
    } elseif (!in_array($classe, $classes_autorisees)) {
        $error = "❌ ERREUR SYSCOHADA: Le compte doit appartenir aux classes 1 à 8 (SYSCOHADA UEMOA)";
    } elseif (strlen($intitule) < 5) {
        $error = "❌ ERREUR: L'intitulé du compte est trop court (minimum 5 caractères)";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO PLAN_COMPTABLE_UEMOA (compte_id, intitule_compte) VALUES (?, ?)");
            $stmt->execute([$compte_id, $intitule]);
            $message = "✅ Compte $compte_id - $intitule ajouté avec succès (Classe $classe)";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "❌ ERREUR: Le compte $compte_id existe déjà dans le plan comptable";
            } else {
                $error = "❌ ERREUR: " . $e->getMessage();
            }
        }
    }
}

// Récupération des comptes par classe
$comptes = [];
for($c = 1; $c <= 8; $c++) {
    $stmt = $pdo->prepare("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA WHERE compte_id BETWEEN ? AND ? ORDER BY compte_id");
    $stmt->execute([$c * 100, ($c * 100) + 99]);
    $comptes[$c] = $stmt->fetchAll();
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-plus-circle"></i> Ajout de compte - Plan comptable SYSCOHADA UEMOA</h5>
                <small>Contrôle rigoureux conforme à l'Acte Uniforme OHADA</small>
            </div>
            <div class="card-body">
                
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <!-- Formulaire d'ajout -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">Ajouter un compte</div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label>Numéro de compte (SYSCOHADA)</label>
                                        <input type="number" name="compte_id" class="form-control" placeholder="Ex: 521 (Banque)" required>
                                        <small>Format: Classe 1-8 + numéro à 2 chiffres (ex: 521 = Banque)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label>Intitulé du compte</label>
                                        <input type="text" name="intitule" class="form-control" placeholder="Nom du compte" required>
                                    </div>
                                    <button type="submit" class="btn-omega w-100">Ajouter au plan comptable</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-header">Règles SYSCOHADA à respecter</div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li><strong>Classe 1</strong> : Capitaux propres (10-19)</li>
                                    <li><strong>Classe 2</strong> : Immobilisations (20-29)</li>
                                    <li><strong>Classe 3</strong> : Stocks (30-39)</li>
                                    <li><strong>Classe 4</strong> : Tiers (40-49)</li>
                                    <li><strong>Classe 5</strong> : Trésorerie (50-59)</li>
                                    <li><strong>Classe 6</strong> : Charges (60-69)</li>
                                    <li><strong>Classe 7</strong> : Produits (70-79)</li>
                                    <li><strong>Classe 8</strong> : Engagements (80-89)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Affichage plan comptable par classe -->
                <?php for($c = 1; $c <= 8; $c++): ?>
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white">Classe <?= $c ?> - <?= $classes_noms[$c] ?? 'Comptes' ?></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>N° compte</th><th>Intitulé</th></tr></thead>
                                <tbody>
                                    <?php foreach($comptes[$c] as $compte): ?>
                                    <tr><td><?= $compte['compte_id'] ?></td><td><?= htmlspecialchars($compte['intitule_compte']) ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <table>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<?php
$classes_noms = [
    1 => 'Capitaux propres', 2 => 'Immobilisations', 3 => 'Stocks',
    4 => 'Tiers', 5 => 'Trésorerie', 6 => 'Charges', 7 => 'Produits', 8 => 'Engagements'
];
include 'inc_footer.php'; ?>
