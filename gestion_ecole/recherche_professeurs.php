<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
include 'header_ecole.php';

$search_name = $_GET['nom'] ?? '';
$search_classe = $_GET['classe_id'] ?? '';

// Requête complexe liant Professeurs -> Affectations -> Classes -> Matières
$sql = "SELECT p.nom, p.prenom, p.specialite, p.id_prof_code,
               c.nom_classe, m.nom_matiere, 
               uv.coefficient, -- On utilise le coef comme base de volume horaire
               (uv.coefficient * 10) as volume_horaire_estime
        FROM professeurs p
        LEFT JOIN affectation_matieres am ON p.id_prof = am.id_prof
        LEFT JOIN classes c ON am.id_classe = c.id_classe
        LEFT JOIN matieres m ON am.id_matiere = m.id_matiere
        LEFT JOIN unites_valeur uv ON (m.id_matiere = uv.matiere_id AND c.id_classe = uv.classe_id)
        WHERE (p.nom LIKE ? OR p.id_prof_code LIKE ?)";

if (!empty($search_classe)) {
    $sql .= " AND c.id_classe = " . intval($search_classe);
}

$stmt = $conn->prepare($sql);
$term = "%$search_name%";
$stmt->bind_param("ss", $term, $term);
$stmt->execute();
$result = $stmt->get_result();

$classes = $conn->query("SELECT id_classe, nom_classe FROM classes");
?>

<div class="container mt-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-search"></i> Recherche d'Affectations Enseignants</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Nom ou Matricule Professeur</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($search_name) ?>" placeholder="Ex: NDIAYE">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filtrer par Classe</label>
                    <select name="classe_id" class="form-select">
                        <option value="">Toutes les classes</option>
                        <?php while($cl = $classes->fetch_assoc()): ?>
                            <option value="<?= $cl['id_classe'] ?>" <?= ($search_classe == $cl['id_classe']) ? 'selected' : '' ?>><?= $cl['nom_classe'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Lancer la recherche</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover bg-white shadow-sm border">
            <thead class="table-secondary">
                <tr>
                    <th>Professeur (Code)</th>
                    <th>Spécialité</th>
                    <th>Classe Affectée</th>
                    <th>Matière Enseignée</th>
                    <th>VH Estimé (h)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= strtoupper($row['nom']) ?></strong> <?= $row['prenom'] ?><br><small class="text-muted"><?= $row['id_prof_code'] ?></small></td>
                        <td><?= $row['specialite'] ?></td>
                        <td><span class="badge bg-info text-dark"><?= $row['nom_classe'] ?? 'N/A' ?></span></td>
                        <td><?= $row['nom_matiere'] ?? 'Pas de matière' ?></td>
                        <td class="fw-bold text-primary"><?= $row['volume_horaire_estime'] ?? 0 ?> h</td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Aucun résultat trouvé pour cette recherche.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
