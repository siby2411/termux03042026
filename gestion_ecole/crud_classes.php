<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }

require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// --- AUTO-DÉTECTION DES COLONNES ---
function getColumns($conn, $table) {
    $res = $conn->query("SHOW COLUMNS FROM $table");
    $cols = [];
    while($row = $res->fetch_assoc()) { $cols[] = $row['Field']; }
    return $cols;
}

$cols_classes = getColumns($conn, 'classes');
$cols_filieres = getColumns($conn, 'filieres');

// Identification des colonnes clés
$col_id_c = $cols_classes[0];
$col_nom_c = isset($cols_classes[1]) ? $cols_classes[1] : $cols_classes[0];
$col_fk_f  = in_array('id_filiere', $cols_classes) ? 'id_filiere' : (in_array('filiere_id', $cols_classes) ? 'filiere_id' : (isset($cols_classes[2]) ? $cols_classes[2] : $cols_classes[0]));

$col_id_f  = $cols_filieres[0];
$col_nom_f = in_array('nom', $cols_filieres) ? 'nom' : (in_array('libelle', $cols_filieres) ? 'libelle' : $cols_filieres[1]);

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_val'])) {
    $nom = $conn->real_escape_string($_POST['nom_val']);
    $f_id = intval($_POST['f_id']);
    $conn->query("INSERT INTO classes ($col_nom_c, $col_fk_f) VALUES ('$nom', $f_id)");
}

// Requêtes
$filieres = $conn->query("SELECT $col_id_f, $col_nom_f FROM filieres ORDER BY 2 ASC");
$classes = $conn->query("
    SELECT c.*, f.$col_nom_f as label_filiere 
    FROM classes c 
    LEFT JOIN filieres f ON c.$col_fk_f = f.$col_id_f 
    ORDER BY 2 ASC
");

include 'header_ecole.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">Gestion des Classes</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="small">Libellé de la Classe</label>
                            <input type="text" name="nom_val" class="form-control" required placeholder="Ex: Master 1">
                        </div>
                        <div class="mb-3">
                            <label class="small">Filière associée</label>
                            <select name="f_id" class="form-select">
                                <?php while($f = $filieres->fetch_row()): ?>
                                    <option value="<?= $f[0] ?>"><?= htmlspecialchars($f[1]) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100 fw-bold">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark small">
                            <tr>
                                <th>ID</th>
                                <th>Classe</th>
                                <th>Filière</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $classes->fetch_array()): ?>
                            <tr>
                                <td><?= $row[0] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row[1]) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['label_filiere'] ?? 'N/A') ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
