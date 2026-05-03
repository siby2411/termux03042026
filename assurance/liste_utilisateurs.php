<?php
require_once 'config/db.php';
$page_title = "Liste des utilisateurs - OMEGA Assurance";
require_once 'includes/header.php';

$db = getDB();

// Création des utilisateurs avec mots de passe
$users = [
    ['admin', 'Administrateur', 'Système', 'admin', 'admin123'],
    ['agent1', 'Agent', 'Commercial', 'agent', 'agent123'],
    ['agent2', 'Agent', 'Service', 'agent', 'agent123'],
    ['comptable', 'Comptable', 'Principal', 'comptable', 'compta123'],
    ['expert1', 'Expert', 'Sinistres', 'expert', 'expert123'],
    ['gestionnaire', 'Gestionnaire', 'Contrats', 'gestionnaire', 'gest123']
];

foreach($users as $user) {
    $password_hash = password_hash($user[4], PASSWORD_DEFAULT);
    try {
        $stmt = $db->prepare("INSERT INTO utilisateurs (nom_utilisateur, prenom, nom, role, mot_de_passe, email, actif) 
                              VALUES (:username, :prenom, :nom, :role, :password, :email, 1)
                              ON DUPLICATE KEY UPDATE mot_de_passe = :password");
        $stmt->execute([
            ':username' => $user[0],
            ':prenom' => $user[1],
            ':nom' => $user[2],
            ':role' => $user[3],
            ':password' => $password_hash,
            ':email' => $user[0] . '@omega-assurance.sn'
        ]);
    } catch(PDOException $e) {
        echo "Erreur: " . $e->getMessage() . "\n";
    }
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-users"></i> Liste des utilisateurs OMEGA Assurance</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Tous les mots de passe sont cryptés en base de données
            </div>
            
            <table class="table table-bordered datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Nom d'utilisateur</th>
                        <th>Nom complet</th>
                        <th>Rôle</th>
                        <th>Email</th>
                        <th>Mot de passe</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>admin</strong></td>
                        <td>Administrateur Système</td>
                        <td><span class="badge bg-danger">Administrateur</span></td>
                        <td>admin@omega-assurance.sn</td>
                        <td><code>admin123</code></td>
                        <td><span class="badge bg-success">Actif</span></td>
                    </tr>
                    <tr>
                        <td><strong>agent1</strong></td>
                        <td>Agent Commercial</td>
                        <td><span class="badge bg-primary">Agent</span></td>
                        <td>agent1@omega-assurance.sn</td>
                        <td><code>agent123</code></td>
                        <td><span class="badge bg-success">Actif</span></td>
                    </tr>
                    <tr>
                        <td><strong>agent2</strong></td>
                        <td>Agent Service</td>
                        <td><span class="badge bg-primary">Agent</span></td>
                        <td>agent2@omega-assurance.sn</td>
                        <td><code>agent123</code></td>
                        <td><span class="badge bg-success">Actif</span></td>
                    </tr>
                    <tr>
                        <td><strong>comptable</strong></td>
                        <td>Comptable Principal</td>
                        <td><span class="badge bg-info">Comptable</span></td>
                        <td>comptable@omega-assurance.sn</td>
                        <td><code>compta123</code></td>
                        <td><span class="badge bg-success">Actif</span></td>
                    </tr>
                    <tr>
                        <td><strong>expert1</strong></td>
                        <td>Expert Sinistres</td>
                        <td><span class="badge bg-warning">Expert</span></td>
                        <td>expert1@omega-assurance.sn</td>
                        <td><code>expert123</code></td>
                        <td><span class="badge bg-success">Actif</span></td>
                    </tr>
                    <tr>
                        <td><strong>gestionnaire</strong></td>
                        <td>Gestionnaire Contrats</td>
                        <td><span class="badge bg-secondary">Gestionnaire</span></td>
                        <td>gestionnaire@omega-assurance.sn</td>
                        <td><code>gest123</code></td>
                        <td><span class="badge bg-success">Actif</span></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="alert alert-warning mt-3">
                <strong><i class="fas fa-lock"></i> Sécurité :</strong>
                <ul>
                    <li>Changez les mots de passe par défaut lors de la première connexion</li>
                    <li>Les mots de passe sont hashés avec bcrypt (norme de sécurité)</li>
                    <li>Chaque utilisateur a des permissions spécifiques selon son rôle</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
