<?php
// Fichier : crud_filieres.php - Gestion CRUD des Filières

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php'; 

// Vérification admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Accès non autorisé.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php"); 
    exit();
}

$conn = db_connect_ecole();
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// --- FORM VARIABLES ---
$id_filiere = 0;
$nom_filiere = '';
$cycle_id = 0;
$is_editing = false;

// --- SAVE / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_filiere'])) {

    $nom_filiere = $_POST['nom_filiere'] ?? '';
    $cycle_id = intval($_POST['cycle_id'] ?? 0);
    $id_filiere = intval($_POST['id_filiere'] ?? 0);

    if (empty($nom_filiere) || $cycle_id == 0) {
        $_SESSION['message'] = "Veuillez remplir tous les champs.";
        $_SESSION['msg_type'] = "danger";
    } else {
        if ($id_filiere > 0) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE filieres SET nom_filiere = ?, cycle_id = ? WHERE id_filiere = ?");
            $stmt->bind_param("sii", $nom_filiere, $cycle_id, $id_filiere);
            $action = "modifiée";
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO filieres (nom_filiere, cycle_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nom_filiere, $cycle_id);
            $action = "ajoutée";
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "Filière '{$nom_filiere}' $action avec succès.";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Erreur SQL : " . $stmt->error;
            $_SESSION['msg_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: crud_filieres.php");
    exit();
}

// --- EDIT ---
if (isset($_GET['edit'])) {
    $id_filiere = intval($_GET['edit']);
    $is_editing = true;

    $res = $conn->query("SELECT * FROM filieres WHERE id_filiere = $id_filiere");

    if ($res->num_rows == 1) {
        $row = $res->fetch_assoc();
        $nom_filiere = $row['nom_filiere'];
        $cycle_id = $row['cycle_id'];
    }
}

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id_filiere = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM filieres WHERE id_filiere = ?");
    $stmt->bind_param("i", $id_filiere);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Filière supprimée avec succès.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Erreur suppression : " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
    }

    $stmt->close();
    header("Location: crud_filieres.php");
    exit();
}

// --- FETCH DATA ---
$result_filieres = $conn->query("
    SELECT f.*, c.nom AS cycle_nom
    FROM filieres f
    JOIN cycles c ON f.cycle_id = c.id
    ORDER BY f.nom_filiere
");

$cycles = $conn->query("SELECT id, nom FROM cycles ORDER BY nom");

include 'header_ecole.php';
?>

<div class="container mt-4">

<h2 class="mb-4">Gestion des Filières</h2>

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
    <?php echo $_SESSION['message']; ?>
</div>
<?php unset($_SESSION['message'], $_SESSION['msg_type']); endif; ?>


<div class="row">
    <div class="col-md-4">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <?php echo $is_editing ? "Modifier Filière" : "Ajouter Filière"; ?>
            </div>
            <div class="card-body">

                <form action="crud_filieres.php" method="POST">

                    <input type="hidden" name="id_filiere" value="<?php echo $id_filiere; ?>">

                    <div class="mb-3">
                        <label>Nom de la Filière</label>
                        <input type="text" name="nom_filiere" class="form-control" required
                               value="<?php echo htmlspecialchars($nom_filiere); ?>">
                    </div>

                    <div class="mb-3">
                        <label>Cycle</label>
                        <select name="cycle_id" class="form-select" required>
                            <option value="">-- Choisir un cycle --</option>
                            <?php while ($c = $cycles->fetch_assoc()): ?>
                                <option value="<?= $c['id']; ?>" 
                                    <?php if ($cycle_id == $c['id']) echo "selected"; ?>>
                                    <?= htmlspecialchars($c['nom']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button class="btn btn-success w-100" name="save_filiere">
                        <?php echo $is_editing ? "Mettre à jour" : "Enregistrer"; ?>
                    </button>

                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <h4>Liste des Filières</h4>
        <table class="table table-bordered table-striped shadow">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Filière</th>
                    <th>Cycle</th>
                    <th width="160px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_filieres->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_filiere']; ?></td>
                    <td><?= htmlspecialchars($row['nom_filiere']); ?></td>
                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['cycle_nom']); ?></span></td>
                    <td>
                        <a href="crud_filieres.php?edit=<?= $row['id_filiere']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="crud_filieres.php?delete=<?= $row['id_filiere']; ?>" 
                           onclick="return confirm('Supprimer cette filière ?');"
                           class="btn btn-sm btn-danger mt-1">Supprimer</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<?php include 'footer_ecole.php'; ?>

