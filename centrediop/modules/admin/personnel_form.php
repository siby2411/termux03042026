<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$message = '';
$error = '';
$user_id = $_GET['id'] ?? null;
$user = null;
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();

if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    try {
        $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        
        if ($user_id) {
            // Mise à jour
            if ($password) {
                $stmt = $pdo->prepare("
                    UPDATE users SET 
                        prenom = ?, nom = ?, role = ?, service_id = ?,
                        telephone = ?, email = ?, password = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['prenom'], $_POST['nom'], $_POST['role'], $_POST['service_id'] ?: null,
                    $_POST['telephone'], $_POST['email'], $password, $user_id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users SET 
                        prenom = ?, nom = ?, role = ?, service_id = ?,
                        telephone = ?, email = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['prenom'], $_POST['nom'], $_POST['role'], $_POST['service_id'] ?: null,
                    $_POST['telephone'], $_POST['email'], $user_id
                ]);
            }
            $message = "Personnel modifié avec succès";
        } else {
            // Nouvel utilisateur
            $username = strtolower($_POST['prenom'] . '.' . $_POST['nom']);
            $password_hash = password_hash($_POST['password'] ?: 'default123', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role, prenom, nom, service_id, telephone, email, actif)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $username, $password_hash, $_POST['role'], $_POST['prenom'], $_POST['nom'],
                $_POST['service_id'] ?: null, $_POST['telephone'], $_POST['email']
            ]);
            $message = "Personnel créé avec succès. Login: $username";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $user_id ? 'Modifier' : 'Nouveau' ?> personnel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Administrateur</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="patients.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="patient_form.php"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                        <li><a href="personnel.php"><i class="fas fa-user-md"></i> Personnel</a></li>
                        <li><a href="personnel_form.php" class="active"><i class="fas fa-user-plus"></i> Nouveau personnel</a></li>
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../consultation/form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <li><a href="../paiements/liste.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../paiements/form.php"><i class="fas fa-plus-circle"></i> Nouveau paiement</a></li>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <li><a href="../statistiques/index.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-user-md"></i> <?= $user_id ? 'Modifier' : 'Nouveau' ?> personnel</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="dashboard-card">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="<?= $user['prenom'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= $user['nom'] ?? '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Rôle</label>
                                <select name="role" class="form-control" required>
                                    <option value="medecin" <?= ($user['role'] ?? '') == 'medecin' ? 'selected' : '' ?>>Médecin</option>
                                    <option value="sagefemme" <?= ($user['role'] ?? '') == 'sagefemme' ? 'selected' : '' ?>>Sage-femme</option>
                                    <option value="caissier" <?= ($user['role'] ?? '') == 'caissier' ? 'selected' : '' ?>>Caissier</option>
                                    <option value="admin" <?= ($user['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Service</label>
                                <select name="service_id" class="form-control">
                                    <option value="">Aucun</option>
                                    <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($user['service_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                        <?= $s['name'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Téléphone</label>
                                <input type="text" name="telephone" class="form-control" value="<?= $user['telephone'] ?? '' ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= $user['email'] ?? '' ?>">
                        </div>
                        
                        <?php if (!$user_id): ?>
                        <div class="mb-3">
                            <label>Mot de passe (laisser vide pour générer automatiquement)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" name="save_user" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="personnel.php" class="btn btn-secondary">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
