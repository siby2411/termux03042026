<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }

require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// --- DÉTECTION DYNAMIQUE DU SCHÉMA ---
$res = $conn->query("SHOW COLUMNS FROM filieres");
$cols = [];
while($row = $res->fetch_assoc()) { $cols[] = $row['Field']; }

$col_id = $cols[0];
// On cherche une colonne de type 'nom' ou 'libelle', sinon on prend la 2ème colonne
$col_nom = "nom"; 
foreach(['nom', 'nom_filiere', 'libelle', 'designation'] as $possibilite) {
    if (in_array($possibilite, $cols)) { $col_nom = $possibilite; break; }
}
if ($col_nom == "nom" && !in_array("nom", $cols)) { $col_nom = $cols[1]; }

// Traitement de l'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filiere_nom'])) {
    $nom_val = $conn->real_escape_string($_POST['filiere_nom']);
    $conn->query("INSERT INTO filieres ($col_nom) VALUES ('$nom_val')");
}

// Suppression
if (isset($_GET['del'])) {
    $id_del = intval($_GET['del']);
    $conn->query("DELETE FROM filieres WHERE $col_id = $id_del");
    header("Location: crud_filieres.php");
    exit();
}

$resultat = $conn->query("SELECT * FROM filieres ORDER BY $col_nom ASC");

include 'header_ecole.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">Nouvelle Filière</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="small">Nom de la Filière</label>
                            <input type="text" name="filiere_nom" class="form-control" placeholder="Ex: Informatique de Gestion" required>
                        </div>
                        <button class="btn btn-primary w-100 fw-bold">Ajouter la Filière</button>
                    </form>
                </div>
            </div>
            <div class="mt-3">
                <a href="crud_classes.php" class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-arrow-left"></i> Gérer les Classes
                </a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark small">
                            <tr>
                                <th>ID</th>
                                <th>Désignation de la Filière</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultat && $resultat->num_rows > 0): ?>
                                <?php while($f = $resultat->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $f[$col_id] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($f[$col_nom]) ?></td>
                                    <td class="text-end">
                                        <a href="crud_filieres.php?del=<?= $f[$col_id] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Supprimer cette filière ?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">Aucune filière trouvée.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
