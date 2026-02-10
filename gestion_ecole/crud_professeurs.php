<?php
// Fichier : crud_professeurs.php - Gestion des Professeurs
// Nécessite : db_connect_ecole.php, header_ecole.php, footer_ecole.php

// 1. Démarrage de la session et Inclusions
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php'; 

// Vérification de la session Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Accès non autorisé. Seul un administrateur peut gérer les professeurs.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php"); 
    exit();
}

// Initialiser la connexion
$conn = db_connect_ecole();
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

// Configuration de l'upload
$upload_dir = 'uploads/prof_cv/';
$full_upload_path = __DIR__ . '/' . $upload_dir;

if (!is_dir($full_upload_path)) {
    // Tente de créer le répertoire si inexistant
    @mkdir($full_upload_path, 0777, true);
}

// Initialisation des variables pour le formulaire
$is_editing = false;
$id_professeur = 0;
$nom = '';
$prenom = '';
$email = '';
$telephone = '';
$adresse = '';
$diplome = '';          // NOUVEAU
$experience = '';       // NOUVEAU
$cv_path_actuel = ''; 
$code_professeur = '';  // CORRIGÉ pour affichage
$matricule = '';        // CORRIGÉ (Initialisation de l'ancien nom de colonne pour la compatibilité dans le code HTML existant)

// -------------------------------------------------------------------
// 1. FONCTION D'UPLOAD CV (Aucune modification requise)
// -------------------------------------------------------------------

/**
 * Gère le téléchargement et la sécurisation du fichier CV.
 */
function handle_cv_upload($prof_id, $conn, $upload_dir, $full_upload_path, $cv_path_actuel = null) {
    if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] != UPLOAD_ERR_OK) {
        return $cv_path_actuel;
    }
    
    $file = $_FILES['cv_file'];
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file['type'], $allowed_types) || $file['size'] > $max_size) {
        $_SESSION['message'] = "Erreur CV: Le fichier doit être un PDF, DOC ou DOCX et ne doit pas dépasser 5 Mo.";
        $_SESSION['msg_type'] = "danger";
        return $cv_path_actuel;
    }

    if ($cv_path_actuel && file_exists(__DIR__ . '/' . $cv_path_actuel)) {
        @unlink(__DIR__ . '/' . $cv_path_actuel);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_name = 'PROF-' . date('YmdHis') . '-' . $prof_id . '.' . $extension;
    $new_file_path = $upload_dir . $safe_name;
    
    if (move_uploaded_file($file['tmp_name'], $full_upload_path . $safe_name)) {
        return $new_file_path;
    } else {
        $_SESSION['message'] = "Erreur lors du déplacement du CV vers le répertoire.";
        $_SESSION['msg_type'] = "danger";
        return $cv_path_actuel;
    }
}

// -------------------------------------------------------------------
// 2. GESTION DES REQUÊTES (INSERT, UPDATE)
// -------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    
    // Récupération des données POST
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $diplome = $_POST['diplome'] ?? '';       // NOUVEAU
    $experience = $_POST['experience'] ?? ''; // NOUVEAU
    
    $id_professeur = intval($_POST['id_professeur'] ?? 0);
    $cv_path_actuel = $_POST['cv_path_actuel'] ?? null;
    $nouveau_cv_path = $cv_path_actuel;
    $action = '';
    
    if ($id_professeur > 0) {
        // --- MISE À JOUR (UPDATE) ---
        $nouveau_cv_path = handle_cv_upload($id_professeur, $conn, $upload_dir, $full_upload_path, $cv_path_actuel);

        // Mise à jour de tous les champs, y compris les nouveaux
        $stmt = $conn->prepare("UPDATE professeurs SET nom=?, prenom=?, email=?, telephone=?, adresse=?, diplome=?, experience=?, cv_path=? WHERE id_professeur=?");
        $stmt->bind_param("ssssssssi", $nom, $prenom, $email, $telephone, $adresse, $diplome, $experience, $nouveau_cv_path, $id_professeur);
        $action = "modifié";
        
        if ($stmt->execute()) {
             $_SESSION['message'] = "Le professeur '{$prenom} {$nom}' a été {$action} avec succès.";
             $_SESSION['msg_type'] = "success";
        } else {
             $_SESSION['message'] = "Erreur lors de la modification : " . $stmt->error;
             $_SESSION['msg_type'] = "danger";
        }
        $stmt->close();
        
    } else {
        // --- AJOUT (INSERT) ---
        
        // ATTENTION : Suppression de la génération manuelle du matricule !
        // Le champ 'code_professeur' est maintenant géré par le TRIGGER MySQL.
        
        // 1. Insérer le professeur (sans code_professeur/matricule)
        $stmt_insert = $conn->prepare("INSERT INTO professeurs (nom, prenom, email, telephone, adresse, diplome, experience) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssssss", $nom, $prenom, $email, $telephone, $adresse, $diplome, $experience);

        if ($stmt_insert->execute()) {
            $id_professeur = $conn->insert_id;
            
            // 2. Gérer l'upload du CV
            $nouveau_cv_path = handle_cv_upload($id_professeur, $conn, $upload_dir, $full_upload_path);

            // 3. Mettre à jour le chemin CV (et récupérer le code_professeur généré par le trigger)
            $code_professeur_genere = '';
            if ($nouveau_cv_path) {
                // Utilisation de la nouvelle colonne 'cv_path' pour l'upload
                 $stmt_update_cv = $conn->prepare("UPDATE professeurs SET cv_path=? WHERE id_professeur=?");
                 $stmt_update_cv->bind_param("si", $nouveau_cv_path, $id_professeur);
                 $stmt_update_cv->execute();
                 $stmt_update_cv->close();
            }
            
            // Récupérer le code généré par le trigger pour l'affichage du message
            $stmt_code = $conn->query("SELECT code_professeur FROM professeurs WHERE id_professeur={$id_professeur}");
            if ($stmt_code && $row_code = $stmt_code->fetch_assoc()) {
                $code_professeur_genere = $row_code['code_professeur'];
            }
            
            $action = "ajouté";
            $_SESSION['msg_type'] = "success";
            $_SESSION['message'] = "Le professeur '{$prenom} {$nom}' a été {$action} avec succès. Code Professeur: {$code_professeur_genere}";

        } else {
            $_SESSION['message'] = "Erreur lors de l'ajout : " . $stmt_insert->error;
            $_SESSION['msg_type'] = "danger";
        }
        $stmt_insert->close();
    }
    
    header("Location: crud_professeurs.php");
    exit();
}

// -------------------------------------------------------------------
// 3. GESTION DE L'ÉDITION (Chargement des données)
// -------------------------------------------------------------------

if (isset($_GET['edit'])) {
    $id_professeur = intval($_GET['edit']);
    
    $stmt_select = $conn->prepare("SELECT * FROM professeurs WHERE id_professeur=?");
    $stmt_select->bind_param("i", $id_professeur);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $is_editing = true;
        $nom = $row['nom'];
        $prenom = $row['prenom'];
        $email = $row['email'];
        $telephone = $row['telephone'];
        $adresse = $row['adresse'];
        $diplome = $row['diplome'];          // CHARGEMENT NOUVEAU
        $experience = $row['experience'];    // CHARGEMENT NOUVEAU
        $cv_path_actuel = $row['cv_path'];
        // Note: L'ancien champ 'matricule' n'est plus utilisé/affiché ici, mais le nouveau 'code_professeur' le serait si nécessaire.
    }
    $stmt_select->close();
}

// -------------------------------------------------------------------
// 4. GESTION DE LA SUPPRESSION (DELETE) - Pas de modification requise
// -------------------------------------------------------------------

if (isset($_GET['delete'])) {
    $id_professeur = intval($_GET['delete']);
    
    // Récupérer le chemin du CV avant la suppression
    $row = $conn->query("SELECT cv_path FROM professeurs WHERE id_professeur=$id_professeur")->fetch_assoc();
    $cv_to_delete = $row['cv_path'] ?? null;
    
    $stmt = $conn->prepare("DELETE FROM professeurs WHERE id_professeur = ?");
    $stmt->bind_param("i", $id_professeur);
    
    if ($stmt->execute()) {
        // Supprimer le fichier CV du disque
        if ($cv_to_delete && file_exists(__DIR__ . '/' . $cv_to_delete)) {
            @unlink(__DIR__ . '/' . $cv_to_delete);
        }
        
        $_SESSION['message'] = "Le professeur a été supprimé avec succès.";
        $_SESSION['msg_type'] = "warning";
    } else {
        $_SESSION['message'] = "Erreur de suppression : Ce professeur est lié à d'autres données (ex: classes/matières).";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    
    header("Location: crud_professeurs.php");
    exit();
}

// -------------------------------------------------------------------
// 5. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE (READ)
// -------------------------------------------------------------------

// S'assurer de sélectionner le champ 'code_professeur' pour l'affichage dans le tableau
$result_professeurs = $conn->query("SELECT *, code_professeur FROM professeurs ORDER BY nom, prenom");
if ($result_professeurs === FALSE) {
      die("Erreur de requête pour la liste des professeurs : " . $conn->error);
}

$conn->close();

// -------------------------------------------------------------------
// 6. AFFICHAGE HTML (Formulaire et Tableau mis à jour)
// -------------------------------------------------------------------

include 'header_ecole.php'; 
?>

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo $_SESSION['msg_type']; ?> mt-3">
    <?php echo $_SESSION['message']; ?>
</div>
<?php 
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
endif; 
?>

<h1 class="mb-4">Gestion des Professeurs</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo $is_editing ? 'Modifier le Professeur' : 'Ajouter un Nouveau Professeur'; ?></h5>
            </div>
            <div class="card-body">
                <form action="crud_professeurs.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_professeur" value="<?php echo $id_professeur; ?>">
                    <input type="hidden" name="cv_path_actuel" value="<?php echo htmlspecialchars($cv_path_actuel); ?>">
                    
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" name="nom" id="nom" class="form-control" 
                               value="<?php echo htmlspecialchars($nom); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" name="prenom" id="prenom" class="form-control" 
                               value="<?php echo htmlspecialchars($prenom); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" 
                               value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="text" name="telephone" id="telephone" class="form-control" 
                               value="<?php echo htmlspecialchars($telephone); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="diplome">Diplôme (Ex: Master II Informatique)</label>
                        <input type="text" name="diplome" id="diplome" class="form-control" 
                               value="<?php echo htmlspecialchars($diplome); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Expérience (Texte libre)</label>
                        <textarea name="experience" id="experience" class="form-control" rows="3"><?php echo htmlspecialchars($experience); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <textarea name="adresse" id="adresse" class="form-control" rows="2"><?php echo htmlspecialchars($adresse); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="cv_file">CV (PDF, DOC, DOCX, max 5Mo)</label>
                        <input type="file" name="cv_file" id="cv_file" class="form-control-file">
                        <?php if ($is_editing && $cv_path_actuel): ?>
                            <small class="form-text text-muted">Fichier actuel : <a href="<?php echo htmlspecialchars($cv_path_actuel); ?>" target="_blank">Voir le CV</a>. Le remplacement annulera l'ancien.</small>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="save" class="btn btn-<?php echo $is_editing ? 'success' : 'primary'; ?> btn-block mt-3">
                        <?php echo $is_editing ? 'Enregistrer les Modifications' : 'Ajouter le Professeur'; ?>
                    </button>
                    
                    <?php if ($is_editing): ?>
                        <a href="crud_professeurs.php" class="btn btn-secondary btn-block mt-2">Annuler l'Édition</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <h2 class="mt-4 mt-md-0">Liste des Professeurs</h2>
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 120px; min-width: 120px;">Code Professeur</th> <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Diplôme / Expérience</th> <th style="width: 80px;">CV</th>
                        <th style="width: 150px; min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_professeurs->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge badge-secondary"><?php echo htmlspecialchars($row['code_professeur'] ?? 'N/A'); ?></span></td>
                        <td><?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['telephone']); ?></td>
                        <td>
                            **Diplôme:** <?php echo htmlspecialchars($row['diplome'] ?? 'N/A'); ?><br>
                            <small class="text-muted">Expérience: <?php echo substr(htmlspecialchars($row['experience'] ?? ''), 0, 30) . '...'; ?></small>
                        </td>
                        <td>
                            <?php if (!empty($row['cv_path'])): ?>
                                <a href="<?php echo htmlspecialchars($row['cv_path']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                    Voir CV
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="crud_professeurs.php?edit=<?php echo $row['id_professeur']; ?>" 
                               class="btn btn-sm btn-info mb-1">Modifier</a>
                            <a href="crud_professeurs.php?delete=<?php echo $row['id_professeur']; ?>" 
                               class="btn btn-sm btn-danger mb-1" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce professeur ?');">Supprimer</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
