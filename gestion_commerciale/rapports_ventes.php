<?php
// Fichier : rapports_ventes.php
// Outil d'Aide à la Décision (Rapports OLAP simples)

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();

// Récupérer la liste des vendeurs pour le filtre (si admin)
$vendeurs = []; 
$result_vendeurs = $conn->query("SELECT id_vendeur, nom FROM vendeurs ORDER BY nom ASC");
if ($result_vendeurs) {
    while ($row = $result_vendeurs->fetch_assoc()) {
        $vendeurs[] = $row;
    }
}

// --- LOGIQUE DE FILTRAGE PAR PÉRIODE ---
$date_debut = $_GET['date_debut'] ?? date('Y-m-01'); // Par défaut : début du mois
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');      // Par défaut : aujourd'hui
$id_vendeur_filtre = intval($_GET['id_vendeur'] ?? 0);

// Assurez-vous que l'utilisateur est admin ou que le filtre est sur son propre ID
if (!isset($_SESSION['est_admin']) || !$_SESSION['est_admin']) {
    $id_vendeur_filtre = $_SESSION['id_vendeur'];
}

$ventes_vendeur = [];
$top_produits = [];

// ----------------------------------------------------
// 1. PERFORMANCE DES VENDEURS
// ----------------------------------------------------
$sql_perf = "
    SELECT
        v.nom AS nom_vendeur,
        COUNT(f.id_facture) AS nombre_documents,
        SUM(CASE WHEN f.type_document IN ('FACTURE', 'BL') THEN f.total_facture ELSE 0 END) AS chiffre_affaires_reels
    FROM vendeurs v
    JOIN factures f ON v.id_vendeur = f.id_vendeur
    WHERE 
        f.type_document != 'BC' AND
        f.date_facture BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY) 
        " . ($id_vendeur_filtre > 0 ? " AND v.id_vendeur = ?" : "") . "
    GROUP BY v.id_vendeur, v.nom
    ORDER BY chiffre_affaires_reels DESC
";

$params_perf = [$date_debut, $date_fin];
$types_perf = "ss";

if ($id_vendeur_filtre > 0) {
    $params_perf[] = $id_vendeur_filtre;
    $types_perf .= "i";
}

$stmt_perf = $conn->prepare($sql_perf);
$stmt_perf->bind_param($types_perf, ...$params_perf);
$stmt_perf->execute();
$result_perf = $stmt_perf->get_result();
while ($row = $result_perf->fetch_assoc()) {
    $ventes_vendeur[] = $row;
}
$stmt_perf->close();

// ----------------------------------------------------
// 2. TOP 10 DES PRODUITS VENDUS
// ----------------------------------------------------
$sql_top = "
    SELECT
        p.code_produit,
        p.designation,
        SUM(df.quantite) AS total_quantite_vendue,
        SUM(df.quantite * df.prix_vente) AS total_montant_produit
    FROM produits p
    JOIN details_facture df ON p.id_produit = df.id_produit
    JOIN factures f ON df.id_facture = f.id_facture
    WHERE 
        f.type_document IN ('FACTURE', 'BL') AND
        f.date_facture BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
    GROUP BY p.id_produit, p.code_produit, p.designation
    ORDER BY total_quantite_vendue DESC
    LIMIT 10
";

$stmt_top = $conn->prepare($sql_top);
$stmt_top->bind_param("ss", $date_debut, $date_fin);
$stmt_top->execute();
$result_top = $stmt_top->get_result();
while ($row = $result_top->fetch_assoc()) {
    $top_produits[] = $row;
}
$stmt_top->close();

$conn->close();

// 1. INCLUSION DU HEADER
include 'header.php';
?>

<h1 class="mb-4 text-dark">📊 Rapports de Ventes (Aide à la Décision)</h1>
<p><a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour au Tableau de Bord</a></p>

---

## 📅 Filtre par Période et Vendeur

<div class="card shadow p-3 mb-5">
    <form method="get" action="rapports_ventes.php">
        <div class="row g-3 align-items-end">
            
            <div class="col-md-3">
                <label for="date_debut" class="form-label">Date Début:</label>
                <input type="date" name="date_debut" id="date_debut" class="form-control" 
                       value="<?php echo htmlspecialchars($date_debut); ?>" required>
            </div>
            
            <div class="col-md-3">
                <label for="date_fin" class="form-label">Date Fin:</label>
                <input type="date" name="date_fin" id="date_fin" class="form-control" 
                       value="<?php echo htmlspecialchars($date_fin); ?>" required>
            </div>

            <?php if (isset($_SESSION['est_admin']) && $_SESSION['est_admin']): ?>
            <div class="col-md-4">
                <label for="id_vendeur" class="form-label">Vendeur (Admin):</label>
                <select name="id_vendeur" id="id_vendeur" class="form-select">
                    <option value="0">Tous les Vendeurs</option>
                    <?php foreach ($vendeurs as $v): ?>
                        <option value="<?php echo $v['id_vendeur']; ?>" 
                                <?php echo ($id_vendeur_filtre == $v['id_vendeur']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($v['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Appliquer</button>
            </div>
            <?php else: ?>
            <div class="col-md-6">
                <p class="form-text alert alert-light mb-0">Rapport filtré automatiquement sur votre compte.</p>
                <button type="submit" class="btn btn-primary w-100">Actualiser</button>
            </div>
            <?php endif; ?>
            
        </div>
    </form>
</div>

---

## 1. Performance des Vendeurs (Ventes Réelles)

<?php if (count($ventes_vendeur) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>Vendeur</th>
                <th class="text-center">Documents Validés</th>
                <th class="text-end">Chiffre d'Affaires Réel (DZD)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_general_ca = array_sum(array_column($ventes_vendeur, 'chiffre_affaires_reels'));
            foreach ($ventes_vendeur as $v): 
            ?>
            <tr class="<?php echo ($v['chiffre_affaires_reels'] == $total_general_ca && $total_general_ca > 0) ? 'table-info' : ''; ?>">
                <td class="fw-bold"><?php echo htmlspecialchars($v['nom_vendeur']); ?></td>
                <td class="text-center"><?php echo $v['nombre_documents']; ?></td>
                <td class="text-end fw-bolder text-success fs-5">
                    <?php echo number_format($v['chiffre_affaires_reels'], 2, ',', ' '); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
            <tr>
                <td colspan="2" class="text-end fw-bold">TOTAL GÉNÉRAL :</td>
                <td class="text-end fw-bolder fs-5 text-dark">
                    <?php echo number_format($total_general_ca, 2, ',', ' '); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-info">
        Aucune vente enregistrée pour la période et les critères sélectionnés.
    </div>
<?php endif; ?>

---

## 2. Top 10 des Produits Vendus (Quantité et Montant)

<?php if (count($top_produits) > 0): ?>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle shadow-sm">
        <thead class="table-primary">
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 15%;">Code Produit</th>
                <th style="width: 40%;">Désignation</th>
                <th style="width: 20%;" class="text-center">Quantité Totale Vendue</th>
                <th style="width: 20%;" class="text-end">Montant Ventes Cumulé (DZD)</th>
            </tr>
        </thead>
        <tbody>
            <?php $rank = 1; foreach ($top_produits as $p): ?>
            <tr>
                <td class="text-center"><?php echo $rank++; ?></td>
                <td><?php echo htmlspecialchars($p['code_produit']); ?></td>
                <td><?php echo htmlspecialchars($p['designation']); ?></td>
                <td class="text-center fw-bold">
                    <span class="badge bg-secondary"><?php echo $p['total_quantite_vendue']; ?></span>
                </td>
                <td class="text-end text-primary fw-bold">
                    <?php echo number_format($p['total_montant_produit'], 2, ',', ' '); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-info">
        Aucun produit vendu pour la période sélectionnée.
    </div>
<?php endif; ?>

<?php
// 2. INCLUSION DU FOOTER
include 'footer.php';
?>
