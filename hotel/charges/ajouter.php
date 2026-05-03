<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libelle = trim($_POST['libelle'] ?? '');
    $montant = str_replace([' ', ','], '', $_POST['montant'] ?? '0');
    $montant = (float)$montant;
    $date_charge = $_POST['date_charge'] ?? date('Y-m-d');
    $categorie = trim($_POST['categorie'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$libelle) {
        $error = "Le libellé est obligatoire.";
    } elseif ($montant <= 0) {
        $error = "Le montant doit être supérieur à 0.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO charges (libelle, montant, date_charge, categorie, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$libelle, $montant, $date_charge, $categorie, $notes]);
            $success = "Charge ajoutée avec succès. Montant: " . formatMoney($montant);
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter charge - OMEGA Hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-control:focus {
            border-color: #e94560;
            box-shadow: 0 0 0 0.2rem rgba(233,69,96,0.25);
        }
        .btn-primary {
            background: #e94560;
            border-color: #e94560;
        }
        .btn-primary:hover {
            background: #ff6b6b;
            border-color: #ff6b6b;
        }
    </style>
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter une charge</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i> <?= escape($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> <?= escape($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label><i class="fas fa-tag me-1"></i> Libellé *</label>
                                    <input type="text" name="libelle" class="form-control" placeholder="Ex: Électricité, Eau, Internet, Réparation..." required>
                                    <small class="text-muted">Décrivez la charge de manière claire</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label><i class="fas fa-money-bill-wave me-1"></i> Montant (FCFA) *</label>
                                    <input type="number" name="montant" class="form-control" step="1" min="1" placeholder="Ex: 12500, 45000, 75000" required>
                                    <small class="text-muted">Saisir un montant positif</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label><i class="fas fa-calendar-alt me-1"></i> Date</label>
                                    <input type="date" name="date_charge" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label><i class="fas fa-folder me-1"></i> Catégorie</label>
                                    <select name="categorie" class="form-control">
                                        <option value="">-- Sélectionner une catégorie --</option>
                                        <option value="Électricité">⚡ Électricité</option>
                                        <option value="Eau">💧 Eau</option>
                                        <option value="Internet">🌐 Internet</option>
                                        <option value="Entretien">🔧 Entretien / Maintenance</option>
                                        <option value="Fournitures">📦 Fournitures de bureau</option>
                                        <option value="Nettoyage">🧹 Produits de nettoyage</option>
                                        <option value="Taxes">📑 Taxes et impôts</option>
                                        <option value="Salaires">👥 Salaires du personnel</option>
                                        <option value="Marketing">📢 Marketing / Publicité</option>
                                        <option value="Transport">🚗 Transport / Carburant</option>
                                        <option value="Autres">📌 Autres charges</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label><i class="fas fa-sticky-note me-1"></i> Notes (optionnel)</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Informations complémentaires, justificatifs, référence facture..."></textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="liste.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer la charge
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Conseils -->
                <div class="card mt-3 bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Conseils pour la gestion des charges</h6>
                        <ul class="small mb-0">
                            <li>Enregistrez chaque charge dès qu'elle survient pour un suivi précis</li>
                            <li>Classez vos charges par catégorie pour mieux analyser vos dépenses</li>
                            <li>Ajoutez des notes avec les références de factures pour faciliter les audits</li>
                            <li>Le total des charges sera automatiquement calculé dans le tableau de bord</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation supplémentaire côté client
        document.querySelector('form').addEventListener('submit', function(e) {
            const montant = document.querySelector('input[name="montant"]').value;
            if (montant && parseFloat(montant) <= 0) {
                e.preventDefault();
                alert('Le montant doit être supérieur à 0');
            }
        });
    </script>
</body>
</html>
