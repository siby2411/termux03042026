<?php
// modules/parents/inscription_parent.php (version avec codes)
session_start();
require_once '../../config/database.php';
require_once '../../includes/codes_manager.php';

$database = new Database();
$db = $database->getConnection();
$message = '';
$generated_codes = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // 1. Insertion du parent
        $parent_nom = $_POST['parent_nom'];
        $parent_prenom = $_POST['parent_prenom'];
        $parent_telephone = $_POST['parent_telephone'];
        $parent_email = $_POST['parent_email'];
        $parent_adresse = $_POST['parent_adresse'];
        
        // Génération login unique
        $login = strtolower(substr($parent_prenom, 0, 1) . $parent_nom . rand(100, 999));
        $temp_password = generatePassword();
        $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
        
        // Insertion parent (le trigger va générer code_parent automatiquement)
        $insert_parent = $db->prepare("INSERT INTO parents (nom, prenom, telephone, email, adresse_complete, login_user, mot_de_passe, statut_compte) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, 'actif')");
        $insert_parent->execute([$parent_nom, $parent_prenom, $parent_telephone, $parent_email, $parent_adresse, $login, $password_hash]);
        $id_parent = $db->lastInsertId();
        
        // Récupérer le code généré automatiquement
        $get_code = $db->prepare("SELECT code_parent FROM parents WHERE id_parent = ?");
        $get_code->execute([$id_parent]);
        $code_parent = $get_code->fetchColumn();
        
        // 2. Insertion de l'élève
        $eleve_nom = $_POST['eleve_nom'];
        $eleve_prenom = $_POST['eleve_prenom'];
        $eleve_classe = $_POST['eleve_classe'];
        $id_ecole = $_POST['id_ecole'];
        $point_prise = $_POST['point_prise'];
        $latitude_prise = $_POST['latitude_prise'] ?? null;
        $longitude_prise = $_POST['longitude_prise'] ?? null;
        
        // Insertion élève (trigger va générer code_eleve)
        $insert_eleve = $db->prepare("INSERT INTO eleves (id_parent, id_ecole, nom_eleve, prenom_eleve, classe, point_prise_en_charge, latitude_prise, longitude_prise, statut_inscription) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
        $insert_eleve->execute([$id_parent, $id_ecole, $eleve_nom, $eleve_prenom, $eleve_classe, $point_prise, $latitude_prise, $longitude_prise]);
        $id_eleve = $db->lastInsertId();
        
        // Récupérer le code élève
        $get_eleve_code = $db->prepare("SELECT code_eleve FROM eleves WHERE id_eleve = ?");
        $get_eleve_code->execute([$id_eleve]);
        $code_eleve = $get_eleve_code->fetchColumn();
        
        $db->commit();
        
        // Stocker les codes pour affichage
        $generated_codes = [
            'code_parent' => $code_parent,
            'code_eleve' => $code_eleve,
            'login' => $login,
            'password' => $temp_password
        ];
        
        $message = '<div class="alert alert-success">Inscription réussie ! Vos codes uniques ont été générés.</div>';
        
    } catch(Exception $e) {
        $db->rollBack();
        $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
    }
}

// Récupération des écoles
$ecoles = $db->query("SELECT id_ecole, nom_ecole, adresse_ecole FROM ecoles");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .code-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        .code-display {
            background: white;
            color: #333;
            padding: 15px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 1.2rem;
            letter-spacing: 2px;
            text-align: center;
        }
        .info-text {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3><i class="fas fa-user-plus"></i> Inscription Transport Scolaire</h3>
                    <p class="mb-0">Chaque parent et chaque élève reçoit un code unique pour faciliter les transactions</p>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <?php if(!empty($generated_codes)): ?>
                    <!-- Affichage des codes générés -->
                    <div class="code-card">
                        <h4><i class="fas fa-id-card"></i> Vos codes uniques</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Code Parent :</label>
                                <div class="code-display">
                                    <i class="fas fa-user"></i> <?php echo $generated_codes['code_parent']; ?>
                                </div>
                                <small class="text-white">À utiliser pour toutes les transactions</small>
                            </div>
                            <div class="col-md-6">
                                <label>Code Élève :</label>
                                <div class="code-display">
                                    <i class="fas fa-child"></i> <?php echo $generated_codes['code_eleve']; ?>
                                </div>
                                <small class="text-white">Code unique pour votre enfant</small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Identifiants de connexion :</label>
                                <div class="code-display">
                                    Login: <?php echo $generated_codes['login']; ?> | Mot de passe: <?php echo $generated_codes['password']; ?>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="login_parent.php" class="btn btn-light">Se connecter</a>
                        </div>
                    </div>
                    <?php else: ?>
                    
                    <form method="POST" id="inscriptionForm">
                        <!-- PARTIE PARENT -->
                        <h4><i class="fas fa-user-friends"></i> Informations du parent</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Nom *</label>
                                <input type="text" name="parent_nom" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Prénom *</label>
                                <input type="text" name="parent_prenom" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Téléphone (Wave/Orange) *</label>
                                <input type="tel" name="parent_telephone" class="form-control" placeholder="77 123 45 67" required>
                                <small class="info-text">Ce numéro servira à générer votre code unique</small>
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="parent_email" class="form-control">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Adresse complète *</label>
                                <textarea name="parent_adresse" class="form-control" rows="2" required></textarea>
                            </div>
                        </div>
                        
                        <!-- PARTIE ÉLÈVE -->
                        <h4 class="mt-4"><i class="fas fa-child"></i> Informations de l'élève</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Nom de l'élève *</label>
                                <input type="text" name="eleve_nom" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Prénom de l'élève *</label>
                                <input type="text" name="eleve_prenom" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Classe *</label>
                                <select name="eleve_classe" class="form-control" required>
                                    <option value="">Sélectionner</option>
                                    <option>CI</option><option>CP</option><option>CE1</option><option>CE2</option>
                                    <option>CM1</option><option>CM2</option><option>6ème</option><option>5ème</option>
                                    <option>4ème</option><option>3ème</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>École *</label>
                                <select name="id_ecole" class="form-control" required>
                                    <option value="">Sélectionner une école</option>
                                    <?php while($ecole = $ecoles->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $ecole['id_ecole']; ?>">
                                        <?php echo htmlspecialchars($ecole['nom_ecole']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Point de prise en charge</label>
                                <input type="text" name="point_prise" class="form-control" placeholder="Adresse de prise en charge">
                                <small class="info-text">Le code élève sera généré automatiquement à partir du téléphone parent</small>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Finaliser l'inscription
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
