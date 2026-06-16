<?php
include 'header_ecole.php';
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$search_query = "";
if (isset($_GET['code_etudiant']) && !empty($_GET['code_etudiant'])) {
    $code = $conn->real_escape_string($_GET['code_etudiant']);
    $search_query = "WHERE e.code_etudiant = '$code'";
}

$sql = "SELECT e.id, e.code_etudiant, e.nom, e.prenom, c.nom_class 
        FROM etudiants e 
        JOIN classes c ON e.classe_id = c.id 
        $search_query 
        ORDER BY e.nom ASC";
$res = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-search"></i> Recherche Paiements et Inscriptions par Étudiant</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-8">
                    <input type="text" name="code_etudiant" class="form-control" placeholder="Entrez le code étudiant..." value="<?= htmlspecialchars($_GET['code_etudiant'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark w-100">Rechercher</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Nom & Prénom</th>
                            <th>Classe</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res->num_rows > 0): ?>
                            <?php while($e = $res->fetch_assoc()): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($e['code_etudiant']) ?></code></td>
                                <td class="fw-bold"><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></td>
                                <td><?= htmlspecialchars($e['nom_class']) ?></td>
                                <td class="text-center">
                                    <a href="fiche_suivi.php?code_etudiant=<?= $e['code_etudiant'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-person-lines-fill"></i> Voir Suivi
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">Aucun étudiant trouvé.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
