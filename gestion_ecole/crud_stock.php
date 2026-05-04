<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';

$conn = db_connect_ecole();
if (!$conn) { die("Erreur de connexion."); }

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_item'])) {
    $nom = $conn->real_escape_string($_POST['nom_item']);
    $qte = intval($_POST['quantite']);
    $conn->query("INSERT INTO stock (nom_item, quantite) VALUES ('$nom', $qte) 
                  ON DUPLICATE KEY UPDATE quantite = quantite + $qte");
}

// Requête de sélection
$stock_result = $conn->query("SELECT * FROM stock ORDER BY nom_item ASC");

include 'header_ecole.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Entrée Stock</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-2">
                            <label class="small">Désignation</label>
                            <input type="text" name="nom_item" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="small">Quantité</label>
                            <input type="number" name="quantite" class="form-control" value="1" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 mt-2">Mettre à jour</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Article</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $stock_result->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($row['nom_item']) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $row['quantite'] < 5 ? 'bg-danger' : 'bg-success' ?>">
                                    <?= $row['quantite'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="text-danger"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
