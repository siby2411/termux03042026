<?php
// Fichier : crud_matieres.php - Gestion des Matières
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
    $_SESSION['message'] = "Accès non autorisé. Seul un administrateur peut gérer les matières.";
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
$id_matiere = 0;
$nom_matiere = '';
$coefficient = '';
$description = '';
$semestre = ''; 

// -------------------------------------------------------------------
// 2. GESTION DES REQUÊTES (INSERT, UPDATE)
// -------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    
    // Récupération et nettoyage des données POST
    $nom_matiere = trim($_POST['nom_matiere'] ?? '');
    // Utiliser floatval pour accepter les coefficients décimaux (ex: 1.5)
    $coefficient = floatval($_POST['coefficient'] ?? 1.0); 
    $description = trim($_POST['description'] ?? '');
    $semestre = $_POST['semestre'] ?? ''; 
    $id_matiere = intval($_POST['id_matiere'] ?? 0);
    $action = '';

    if (empty($nom_matiere) || empty($semestre) || $coefficient <= 0) {
        $_SESSION['message'] = "Veuillez remplir tous les champs obligatoires (Nom, Semestre, Coefficient > 0).";
        $_SESSION['msg_type'] = "danger";
        header("Location: crud_matieres.php");
        exit();
    }

    if ($id_matiere > 0) {
        // --- MISE À JOUR (UPDATE) ---
        $stmt = $conn->prepare("UPDATE matieres SET nom_matiere=?, coefficient=?, description=?, semestre=? WHERE id_matiere=?");
        // 's' (string), 'd' (double/float), 's' (string), 's' (string), 'i' (integer)
        $stmt->bind_param("sdssi", $nom_matiere, $coefficient, $description, $semestre, $id_matiere);
        $action = "modifiée";
    } else {
        // --- AJOUT (INSERT) ---
        $stmt = $conn->prepare("INSERT INTO matieres (nom_matiere, coefficient, description, semestre) VALUES (?, ?, ?, ?)");
        // 's' (string), 'd' (double/float), 's' (string), 's' (string)
        $stmt->bind_param("sdss", $nom_matiere, $coefficient, $description, $semestre);
        $action = "ajoutée";
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "La matière '**{$nom_matiere}**' (Coef: {$coefficient}) a été {$action} avec succès.";
        $_SESSION['msg_type'] = "success";
    } else {
        if ($conn->errno == 1062) {
             $_SESSION['message'] = "Erreur : Une matière avec ce nom existe déjà.";
             $_SESSION['msg_type'] = "danger";
        } else {
             $_SESSION['message'] = "Erreur lors de la requête : " . $stmt->error;
             $_SESSION['msg_type'] = "danger";
        }
    }
    $stmt->close();
    
    header("Location: crud_matieres.php");
    exit();
}

// -------------------------------------------------------------------
// 3. GESTION DE L'ÉDITION (Chargement des données)
// -------------------------------------------------------------------

if (isset($_GET['edit'])) {
    $id_matiere = intval($_GET['edit']);
    
    $stmt_select = $conn->prepare("SELECT * FROM matieres WHERE id_matiere=?");
    $stmt_select->bind_param("i", $id_matiere);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $is_editing = true;
        $nom_matiere = $row['nom_matiere'];
        $coefficient = $row['coefficient'];
        $description = $row['description'];
        $semestre = $row['semestre']; 
    }
    $stmt_select->close();
}

// -------------------------------------------------------------------
// 4. GESTION DE LA SUPPRESSION (DELETE)
// -------------------------------------------------------------------

if (isset($_GET['delete'])) {
    $id_matiere = intval($_GET['delete']);
    
    // Tentative de suppression. La base de données devrait gérer les contraintes.
    $stmt = $conn->prepare("DELETE FROM matieres WHERE id_matiere = ?");
    $stmt->bind_param("i", $id_matiere);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "La matière a été supprimée avec succès.";
        $_SESSION['msg_type'] = "warning";
    } else {
        // En cas d'erreur de clé étrangère non gérée par CASCADE :
        $_SESSION['message'] = "Erreur de suppression : Cette matière est peut-être toujours liée à des notes d'étudiants. Veuillez supprimer les notes associées d'abord.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    
    header("Location: crud_matieres.php");
    exit();
}

// -------------------------------------------------------------------
// 5. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE (READ)
// -------------------------------------------------------------------

$matieres_result = $conn->query("SELECT * FROM matieres ORDER BY semestre, nom_matiere");
if ($matieres_result === FALSE) {
     die("Erreur de requête pour la liste des matières : " . $conn->error);
}

$conn->close();

// -------------------------------------------------------------------
// 6. AFFICHAGE HTML
// -------------------------------------------------------------------

include 'header_ecole.php'; 
?>

<h1 class="mb-4">Gestion des Matières</h1>

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
                <h5 class="mb-0"><?php echo $is_editing ? 'Modifier la Matière' : 'Ajouter une Nouvelle Matière'; ?></h5>
            </div>
            <div class="card-body">
                <form action="crud_matieres.php" method="POST">
                    <input type="hidden" name="id_matiere" value="<?php echo $id_matiere; ?>">
                    
                    <div class="form-group mb-3">
                        <label for="nom_matiere">Nom de la Matière <span class="text-danger">*</span></label>
                        <input type="text" name="nom_matiere" id="nom_matiere" class="form-control" 
                               value="<?php echo htmlspecialchars($nom_matiere); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="coefficient">Coefficient <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="coefficient" id="coefficient" class="form-control" 
                               value="<?php echo htmlspecialchars($coefficient); ?>" required min="1">
                        <small class="form-text text-muted">Ex: 1, 2, 3... (peut être décimal)</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="semestre">Semestre <span class="text-danger">*</span></label>
                        <select name="semestre" id="semestre" class="form-control" required>
                            <option value="">-- Sélectionner le Semestre --</option>
                            <option value="S1" <?php echo ($semestre == 'S1') ? 'selected' : ''; ?>>Semestre 1 (S1)</option>
                            <option value="S2" <?php echo ($semestre == 'S2') ? 'selected' : ''; ?>>Semestre 2 (S2)</option>
                            <option value="Annuel" <?php echo ($semestre == 'Annuel') ? 'selected' : ''; ?>>Annuel</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">Description (Optionnel)</label>
                        <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <button type="submit" name="save" class="btn btn-<?php echo $is_editing ? 'warning' : 'primary'; ?> btn-block mt-3">
                        <?php echo $is_editing ? 'Enregistrer Modification' : 'Ajouter la Matière'; ?>
                    </button>
                    
                    <?php if ($is_editing): ?>
                        <a href="crud_matieres.php" class="btn btn-secondary btn-block mt-2">Annuler l'Édition</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <h2 class="mt-4 mt-md-0">Liste des Matières</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 200px;">Matière</th>
                        <th style="width: 100px;">Coefficient</th>
                        <th style="width: 100px;">Semestre</th>
                        <th>Description</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $matieres_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nom_matiere']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['coefficient'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($row['semestre']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <a href="crud_matieres.php?edit=<?php echo $row['id_matiere']; ?>" 
                               class="btn btn-sm btn-info mb-1">Modifier</a>
                            <a href="crud_matieres.php?delete=<?php echo $row['id_matiere']; ?>" 
                               class="btn btn-sm btn-danger mb-1" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer la matière <?php echo htmlspecialchars($row['nom_matiere']); ?> ? Cela peut affecter les notes des étudiants.');">Supprimer</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
