<?php
// Fichier : crud_classe_matiere.php - Affectation des matières aux classes
// RÔLE D'ACCÈS : ADMINISTRATEUR UNIQUEMENT

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
$id_classe_matiere = 0;
$id_classe = '';
$id_matiere = '';
$id_professeur = '';
$semestre_affectation = '';

// -------------------------------------------------------------------
// 2. RÉCUPÉRATION DES DONNÉES DU FORMULAIRE (CLASSES, MATIERES, PROFESSEURS)
// -------------------------------------------------------------------

// Récupérer la liste des classes
$classes_result = $conn->query("SELECT id_classe, nom_classe FROM classes ORDER BY nom_classe");

// Récupérer la liste des matières
$matieres_result = $conn->query("SELECT id_matiere, nom_matiere FROM matieres ORDER BY nom_matiere");

// Récupérer la liste des professeurs
$profs_result = $conn->query("SELECT id_professeur, nom, prenom FROM professeurs ORDER BY nom, prenom");


// -------------------------------------------------------------------
// 3. GESTION DES REQUÊTES (INSERT, UPDATE)
// -------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    
    // Récupération et nettoyage des données POST
    $id_classe = intval($_POST['id_classe'] ?? 0);
    $id_matiere = intval($_POST['id_matiere'] ?? 0);
    $id_professeur = intval($_POST['id_professeur'] ?? 0);
    $semestre_affectation = $_POST['semestre_affectation'] ?? null;
    $id_classe_matiere = intval($_POST['id_classe_matiere'] ?? 0);
    
    // Si id_professeur est 0 (Non Spécifié), stocker NULL
    $prof_id_db = ($id_professeur > 0) ? $id_professeur : NULL; 

    if ($id_classe_matiere > 0) {
        // --- MISE À JOUR (UPDATE) ---
        $stmt = $conn->prepare("UPDATE classes_matieres SET id_classe=?, id_matiere=?, id_professeur=?, semestre_affectation=? WHERE id_classe_matiere=?");
        $stmt->bind_param("iisis", $id_classe, $id_matiere, $prof_id_db, $semestre_affectation, $id_classe_matiere);
        $action = "modifiée";
    } else {
        // --- AJOUT (INSERT) ---
        $stmt = $conn->prepare("INSERT INTO classes_matieres (id_classe, id_matiere, id_professeur, semestre_affectation) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $id_classe, $id_matiere, $prof_id_db, $semestre_affectation);
        $action = "ajoutée";
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "L'affectation a été {$action} avec succès.";
        $_SESSION['msg_type'] = "success";
    } else {
        // Gérer l'erreur UNIQUE KEY si la classe/matière existe déjà
        if ($conn->errno == 1062) {
             $_SESSION['message'] = "Erreur : Cette matière est déjà affectée à cette classe.";
             $_SESSION['msg_type'] = "danger";
        } else {
             $_SESSION['message'] = "Erreur lors de la requête : " . $stmt->error;
             $_SESSION['msg_type'] = "danger";
        }
    }
    $stmt->close();
    
    header("Location: crud_classe_matiere.php");
    exit();
}

// -------------------------------------------------------------------
// 4. GESTION DE L'ÉDITION ET DE LA SUPPRESSION
// -------------------------------------------------------------------

// Logique EDIT
if (isset($_GET['edit'])) {
    $id_classe_matiere = intval($_GET['edit']);
    
    $stmt_select = $conn->prepare("SELECT * FROM classes_matieres WHERE id_classe_matiere=?");
    $stmt_select->bind_param("i", $id_classe_matiere);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $is_editing = true;
        $id_classe = $row['id_classe'];
        $id_matiere = $row['id_matiere'];
        $id_professeur = $row['id_professeur'];
        $semestre_affectation = $row['semestre_affectation'];
    }
    $stmt_select->close();
}

// Logique DELETE
if (isset($_GET['delete'])) {
    $id_classe_matiere = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM classes_matieres WHERE id_classe_matiere = ?");
    $stmt->bind_param("i", $id_classe_matiere);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "L'affectation a été supprimée avec succès.";
        $_SESSION['msg_type'] = "warning";
    } else {
        $_SESSION['message'] = "Erreur de suppression : " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    
    header("Location: crud_classe_matiere.php");
    exit();
}

// -------------------------------------------------------------------
// 5. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE (READ)
// -------------------------------------------------------------------

$query = "
    SELECT 
        cm.*, c.nom_classe, m.nom_matiere, p.nom AS nom_prof, p.prenom AS prenom_prof
    FROM 
        classes_matieres cm
    JOIN 
        classes c ON cm.id_classe = c.id_classe
    JOIN 
        matieres m ON cm.id_matiere = m.id_matiere
    LEFT JOIN 
        professeurs p ON cm.id_professeur = p.id_professeur
    ORDER BY 
        c.nom_classe, m.nom_matiere
";

$affectations_result = $conn->query($query);
if ($affectations_result === FALSE) {
     // Si l'erreur se produit ici, vérifiez le nom des colonnes (semestre_affectation)
     die("Erreur de requête pour l'affichage : " . $conn->error);
}

$conn->close();

// -------------------------------------------------------------------
// 6. AFFICHAGE HTML
// -------------------------------------------------------------------

include 'header_ecole.php'; 
?>

<h1 class="mb-4">Affectation des Matières aux Classes</h1>

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
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo $is_editing ? 'Modifier l\'Affectation' : 'Ajouter une Matière à une Classe'; ?></h5>
            </div>
            <div class="card-body">
                <form action="crud_classe_matiere.php" method="POST">
                    <input type="hidden" name="id_classe_matiere" value="<?php echo $id_classe_matiere; ?>">
                    
                    <div class="form-group">
                        <label for="id_classe">Choisir la Classe</label>
                        <select name="id_classe" id="id_classe" class="form-control" required>
                            <option value="">-- Sélectionner une Classe --</option>
                            <?php 
                            // Réinitialiser le pointeur pour la boucle
                            $classes_result->data_seek(0);
                            while ($classe = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $classe['id_classe']; ?>" 
                                    <?php echo ($id_classe == $classe['id_classe']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($classe['nom_classe']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_matiere">Choisir la Matière</label>
                        <select name="id_matiere" id="id_matiere" class="form-control" required>
                            <option value="">-- Sélectionner une Matière --</option>
                            <?php 
                            // Réinitialiser le pointeur
                            $matieres_result->data_seek(0);
                            while ($matiere = $matieres_result->fetch_assoc()): ?>
                                <option value="<?php echo $matiere['id_matiere']; ?>" 
                                    <?php echo ($id_matiere == $matiere['id_matiere']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($matiere['nom_matiere']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_professeur">Professeur Responsable (pour cette affectation)</label>
                        <select name="id_professeur" id="id_professeur" class="form-control">
                            <option value="0">-- Non Spécifié (Optionnel) --</option>
                            <?php 
                            // Réinitialiser le pointeur
                            $profs_result->data_seek(0);
                            while ($prof = $profs_result->fetch_assoc()): ?>
                                <option value="<?php echo $prof['id_professeur']; ?>" 
                                    <?php echo ($id_professeur == $prof['id_professeur']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text text-muted">Celui qui donnera les notes.</small>
                    </div>

                    <div class="form-group">
                        <label for="semestre_affectation">Semestre concerné</label>
                        <select name="semestre_affectation" id="semestre_affectation" class="form-control">
                            <option value="" <?php echo ($semestre_affectation == '') ? 'selected' : ''; ?>>-- Annuel / Non Spécifié --</option>
                            <option value="Semestre 1" <?php echo ($semestre_affectation == 'Semestre 1') ? 'selected' : ''; ?>>Semestre 1</option>
                            <option value="Semestre 2" <?php echo ($semestre_affectation == 'Semestre 2') ? 'selected' : ''; ?>>Semestre 2</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="save" class="btn btn-<?php echo $is_editing ? 'success' : 'primary'; ?> btn-block mt-3">
                        <?php echo $is_editing ? 'Enregistrer Modification' : 'Affecter la Matière'; ?>
                    </button>
                    
                    <?php if ($is_editing): ?>
                        <a href="crud_classe_matiere.php" class="btn btn-secondary btn-block mt-2">Annuler l'Édition</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <h2 class="mt-4 mt-md-0">Affectations Actuelles</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Classe</th>
                        <th>Matière</th>
                        <th>Professeur</th>
                        <th style="width: 120px;">Semestre</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $affectations_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nom_classe']); ?></td>
                        <td><?php echo htmlspecialchars($row['nom_matiere']); ?></td>
                        <td>
                            <?php 
                                if (!empty($row['nom_prof'])) {
                                    echo htmlspecialchars($row['prenom_prof'] . ' ' . $row['nom_prof']);
                                } else {
                                    echo '<span class="text-danger">Non Assigné</span>';
                                }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['semestre_affectation'] ?? 'Annuel'); ?></td>
                        <td>
                            <a href="crud_classe_matiere.php?edit=<?php echo $row['id_classe_matiere']; ?>" 
                               class="btn btn-sm btn-info mb-1">Modifier</a>
                            <a href="crud_classe_matiere.php?delete=<?php echo $row['id_classe_matiere']; ?>" 
                               class="btn btn-sm btn-danger mb-1" 
                               onclick="return confirm('Supprimer cette affectation ?');">Supprimer</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
