<?php
// Fichier : crud_classes.php - Gestion des Classes
// RÔLE D'ACCÈS : ADMINISTRATEUR UNIQUEMENT

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Démarrage de la session et Inclusions
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php'; 

// Vérification de la session Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Accès non autorisé.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php"); 
    exit();
}

// Initialiser la connexion
$conn = db_connect_ecole();
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

// Initialisation des variables
$is_editing = false;
$id_classe = 0;
$nom_classe = '';
$annee_academique = '';
$id_filiere = '';
$cycle = '';

// Récupérer la liste des filières pour le formulaire
$filieres_result = $conn->query("SELECT id_filiere, nom_filiere FROM filieres ORDER BY nom_filiere");

// -------------------------------------------------------------------
// 2. GESTION DES REQUÊTES (INSERT, UPDATE)
// -------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    
    // Récupération et nettoyage des données POST
    $nom_classe = trim($_POST['nom_classe'] ?? '');
    $annee_academique = trim($_POST['annee_academique'] ?? '');
    $id_filiere = intval($_POST['id_filiere'] ?? 0);
    $cycle = trim($_POST['cycle'] ?? '');
    $id_classe = intval($_POST['id_classe'] ?? 0);
    $action = '';

    if (empty($nom_classe) || empty($annee_academique) || $id_filiere === 0 || empty($cycle)) {
        $_SESSION['message'] = "Veuillez remplir tous les champs obligatoires.";
        $_SESSION['msg_type'] = "danger";
        header("Location: crud_classes.php");
        exit();
    }

    if ($id_classe > 0) {
        // --- MISE À JOUR (UPDATE) ---
        $stmt = $conn->prepare("UPDATE classes SET nom_classe=?, annee_academique=?, id_filiere=?, cycle=? WHERE id_classe=?");
        $stmt->bind_param("ssisi", $nom_classe, $annee_academique, $id_filiere, $cycle, $id_classe);
        $action = "modifiée";
    } else {
        // --- AJOUT (INSERT) ---
        $stmt = $conn->prepare("INSERT INTO classes (nom_classe, annee_academique, id_filiere, cycle) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $nom_classe, $annee_academique, $id_filiere, $cycle);
        $action = "ajoutée";
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "La classe '{$nom_classe}' a été {$action} avec succès.";
        $_SESSION['msg_type'] = "success";
    } else {
        if ($conn->errno == 1062) {
             $_SESSION['message'] = "Erreur : Une classe avec ce nom et cette année académique existe déjà.";
             $_SESSION['msg_type'] = "danger";
        } else {
             $_SESSION['message'] = "Erreur lors de la requête : " . $stmt->error;
             $_SESSION['msg_type'] = "danger";
        }
    }
    $stmt->close();
    
    header("Location: crud_classes.php");
    exit();
}

// -------------------------------------------------------------------
// 3. GESTION DE L'ÉDITION (Chargement des données)
// -------------------------------------------------------------------

if (isset($_GET['edit'])) {
    $id_classe = intval($_GET['edit']);
    
    $stmt_select = $conn->prepare("SELECT * FROM classes WHERE id_classe=?");
    $stmt_select->bind_param("i", $id_classe);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $is_editing = true;
        $nom_classe = $row['nom_classe'];
        $annee_academique = $row['annee_academique'];
        $id_filiere = $row['id_filiere'];
        $cycle = $row['cycle'];
    }
    $stmt_select->close();
}

// -------------------------------------------------------------------
// 4. GESTION DE LA SUPPRESSION (DELETE)
// -------------------------------------------------------------------

if (isset($_GET['delete'])) {
    $id_classe = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM classes WHERE id_classe = ?");
    $stmt->bind_param("i", $id_classe);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "La classe a été supprimée avec succès.";
        $_SESSION['msg_type'] = "warning";
    } else {
        // En cas d'erreur de clé étrangère
        $_SESSION['message'] = "Erreur de suppression : Cette classe contient toujours des étudiants ou des données liées.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    
    header("Location: crud_classes.php");
    exit();
}

// -------------------------------------------------------------------
// 5. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE (READ)
// -------------------------------------------------------------------

$query_classes = "
    SELECT 
        c.*, 
        f.nom_filiere 
    FROM 
        classes c
    JOIN 
        filieres f ON c.id_filiere = f.id_filiere
    ORDER BY 
        c.annee_academique DESC, c.nom_classe
";
$classes_list_result = $conn->query($query_classes);
if ($classes_list_result === FALSE) {
     die("Erreur de requête pour la liste des classes : " . $conn->error);
}

$conn->close();

// -------------------------------------------------------------------
// 6. AFFICHAGE HTML
// -------------------------------------------------------------------

include 'header_ecole.php'; 
?>

<h1 class="mb-4">Gestion des Classes et Affectations</h1>

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo $_SESSION['msg_type']; ?> mt-3">
    <?php echo $_SESSION['message']; ?>
</div>
<?php 
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
endif; 
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-<?php echo $is_editing ? 'warning' : 'primary'; ?> text-white">
                <h5 class="mb-0"><?php echo $is_editing ? 'Modifier la Classe' : 'Ajouter une Nouvelle Classe'; ?></h5>
            </div>
            <div class="card-body">
                <form action="crud_classes.php" method="POST">
                    <input type="hidden" name="id_classe" value="<?php echo $id_classe; ?>">
                    
                    <div class="form-group mb-3">
                        <label for="nom_classe">Nom de la Classe (Ex: L1, M2)</label>
                        <input type="text" name="nom_classe" id="nom_classe" class="form-control" 
                               value="<?php echo htmlspecialchars($nom_classe); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="annee_academique">Année Académique (Ex: 2025-2026)</label>
                        <input type="text" name="annee_academique" id="annee_academique" class="form-control" 
                               value="<?php echo htmlspecialchars($annee_academique); ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="cycle">Cycle</label>
                        <select name="cycle" id="cycle" class="form-control" required>
                            <option value="">-- Sélectionner le Cycle --</option>
                            <?php 
                            // Options simples pour le cycle. Ajustez si vous avez une table dédiée.
                            $cycles = ['Licence', 'Master', 'Doctorat', 'Prep']; 
                            foreach ($cycles as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo ($cycle == $c) ? 'selected' : ''; ?>>
                                    <?php echo $c; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="id_filiere">Filière</label>
                        <select name="id_filiere" id="id_filiere" class="form-control" required>
                            <option value="">-- Sélectionner la Filière --</option>
                            <?php 
                            // Assurez-vous de réinitialiser le pointeur car la connexion est rouverte/fermée
                            $filieres_result->data_seek(0);
                            while ($filiere = $filieres_result->fetch_assoc()): ?>
                                <option value="<?php echo $filiere['id_filiere']; ?>" 
                                    <?php echo ($id_filiere == $filiere['id_filiere']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($filiere['nom_filiere']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" name="save" class="btn btn-<?php echo $is_editing ? 'warning' : 'primary'; ?> btn-block mt-3">
                        <?php echo $is_editing ? 'Enregistrer Modification' : 'Ajouter la Classe'; ?>
                    </button>
                    
                    <?php if ($is_editing): ?>
                        <a href="crud_classes.php" class="btn btn-secondary btn-block mt-2">Annuler l'Édition</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <h2 class="mt-4 mt-md-0">Liste des Classes</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 150px;">Classe</th>
                        <th>Année Académique</th>
                        <th>Filière</th>
                        <th>Cycle</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $classes_list_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nom_classe']); ?></td>
                        <td><?php echo htmlspecialchars($row['annee_academique']); ?></td>
                        <td><?php echo htmlspecialchars($row['nom_filiere']); ?></td>
                        <td><?php echo htmlspecialchars($row['cycle']); ?></td>
                        <td>
                            <a href="crud_classes.php?edit=<?php echo $row['id_classe']; ?>" 
                               class="btn btn-sm btn-info mb-1">Modifier</a>
                            <a href="crud_classes.php?delete=<?php echo $row['id_classe']; ?>" 
                               class="btn btn-sm btn-danger mb-1" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer la classe ? Les étudiants associés devront être réaffectés.');">Supprimer</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
