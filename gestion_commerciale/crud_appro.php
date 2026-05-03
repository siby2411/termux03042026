<?php
// Fichier : crud_appro.php
// Formulaire pour enregistrer un approvisionnement + Historique

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

// 1. Établir la connexion UNE SEULE FOIS
$conn = db_connect();

// --- LOGIQUE POUR LA LISTE DÉROULANTE DES PRODUITS ---
$produits = [];
$result = $conn->query("SELECT id_produit, designation, code_produit, stock_actuel FROM produits ORDER BY designation ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
    }
}

// --- LOGIQUE D'AFFICHAGE DE L'HISTORIQUE (READ) ---
$historique_appro = [];
$sql_hist = "SELECT a.id_appro, a.quantite_entree, a.date_appro, p.designation, v.nom AS nom_vendeur
             FROM approvisionnements a
             JOIN produits p ON a.id_produit = p.id_produit
             JOIN vendeurs v ON a.id_vendeur = v.id_vendeur
             ORDER BY a.date_appro DESC LIMIT 10"; // 10 dernières transactions

$result_hist = $conn->query($sql_hist);
if ($result_hist && $result_hist->num_rows > 0) {
    while ($row = $result_hist->fetch_assoc()) {
        $historique_appro[] = $row;
    }
}

// 2. Fermer la connexion APRÈS toutes les requêtes
$conn->close();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// 1. INCLUSION DU HEADER
include 'header.php';
?>

<h1 class="mb-4 text-warning">🚚 Enregistrement d'un Approvisionnement</h1>
<p><a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour au Tableau de Bord</a></p>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<hr>

<div class="card shadow mb-5">
    <div class="card-header bg-warning text-dark">
        <h2 class="h5 mb-0">Ajouter une Réception de Stock</h2>
    </div>
    <div class="card-body">
        <form action="traitement_appro.php" method="post">
            
            <input type="hidden" name="id_vendeur" value="<?php echo $_SESSION['id_vendeur']; ?>">
            
            <div class="row g-3 align-items-end">
                
                <div class="col-md-7">
                    <label for="id_produit" class="form-label">Produit Reçu:</label>
                    <select class="form-select" id="id_produit" name="id_produit" required>
                        <option value="" selected disabled>-- Sélectionner un produit --</option>
                        <?php foreach ($produits as $p): ?>
                            <option value="<?php echo $p['id_produit']; ?>">
                                <?php echo htmlspecialchars($p['designation']) . " (" . $p['code_produit'] . ") - Stock actuel: " . $p['stock_actuel']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="quantite_entree" class="form-label">Quantité Reçue:</label>
                    <input type="number" class="form-control" id="quantite_entree" name="quantite_entree" required min="1" value="1">
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning w-100">Enregistrer</button>
                </div>
                
            </div>
        </form>
    </div>
</div>

<h2 class="mb-3">Historique des 10 Dernières Réceptions <span class="badge bg-secondary"><?php echo count($historique_appro); ?></span></h2>

<?php if (count($historique_appro) > 0): ?>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID Appro</th>
                <th>Produit</th>
                <th class="text-center">Quantité Entrée</th>
                <th>Date</th>
                <th>Enregistré par</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historique_appro as $h): ?>
            <tr>
                <td><?php echo $h['id_appro']; ?></td>
                <td class="fw-bold"><?php echo htmlspecialchars($h['designation']); ?></td>
                <td class="text-center">
                    <span class="badge bg-success fs-6"><?php echo $h['quantite_entree']; ?></span>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($h['date_appro'])); ?></td>
                <td><?php echo htmlspecialchars($h['nom_vendeur']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">Historique Vide</h4>
        <p>Aucun mouvement d'approvisionnement n'a été enregistré récemment.</p>
    </div>
<?php endif; ?>

<?php
// 2. INCLUSION DU FOOTER
include 'footer.php';
?>
