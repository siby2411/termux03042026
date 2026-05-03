<?php
// 1. Activation du rapport d'erreurs pour le debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

// 2. Vérification de sécurité avec message explicite au lieu de page blanche
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Administrateur') {
    echo "<div style='padding:50px; text-align:center; font-family:sans-serif;'>
            <h2 style='color:red;'>Accès Refusé</h2>
            <p>Votre rôle actuel (<b>".($_SESSION['user_role'] ?? 'Inconnu')."</b>) ne vous permet pas d'accéder à la gestion des utilisateurs.</p>
            <a href='tableau_de_bord.php'>Retour au Dashboard</a>
          </div>";
    exit();
}

$database = new Database();
$db = $database->getConnection();

$page_title = "Gestion des Utilisateurs";
include '../../includes/header.php';

// Récupération des membres de l'équipe
$stmt = $db->query("SELECT id_utilisateur, username, nom, prenom, role FROM UTILISATEURS ORDER BY role ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="fas fa-user-shield text-primary me-2"></i> Contrôle des Accès</h3>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus-circle me-2"></i> Ajouter un collaborateur
    </button>
</div>

<div class="row g-3">
    <?php while($u = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light rounded-circle p-3 me-3">
                        <i class="fas fa-user text-secondary fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?= $u['prenom'] ?> <?= $u['nom'] ?></h6>
                        <span class="badge <?= $u['role'] == 'Administrateur' ? 'bg-primary' : 'bg-info' ?> small">
                            <?= $u['role'] ?>
                        </span>
                    </div>
                </div>
                <div class="small text-muted mb-3">
                    <i class="fas fa-sign-in-alt me-1"></i> ID : <b><?= $u['username'] ?></b>
                </div>
                <div class="btn-group w-100">
                    <button class="btn btn-sm btn-outline-secondary">Réinitialiser</button>
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="traitement_utilisateur.php" method="POST" class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nouveau Compte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Identifiant (Login)</label>
                        <input type="text" name="username" class="form-control" placeholder="ex: m.sow" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Mot de passe provisoire</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Rôle</label>
                        <select name="role" class="form-select">
                            <option value="Vendeur">Vendeur (Ventes & Stock uniquement)</option>
                            <option value="Administrateur">Administrateur (Accès total)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="submit" class="btn btn-primary px-4">Créer le compte</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
