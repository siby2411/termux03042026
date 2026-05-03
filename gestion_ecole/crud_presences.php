<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$page_title = "Feuille de Présence - OMEGA";
include 'header_ecole.php';

$classe_id = $_GET['classe_id'] ?? 0;
$date_appel = $_GET['date'] ?? date('Y-m-d');

if (isset($_POST['save_presence'])) {
    foreach ($_POST['statut'] as $code_etudiant => $statut) {
        $justifie = isset($_POST['justifie'][$code_etudiant]) ? 1 : 0;
        $stmt = $conn->prepare("INSERT INTO presences (code_etudiant, id_classe, date_presence, statut, justifie) 
                                VALUES (?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE statut=?, justifie=?");
        $stmt->bind_param("sisssii", $code_etudiant, $classe_id, $date_appel, $statut, $justifie, $statut, $justifie);
        $stmt->execute();
    }
    echo "<div class='alert alert-success'>Appel enregistré pour le ".date('d/m/Y', strtotime($date_appel))."</div>";
}

$classes = $conn->query("SELECT * FROM classes");
$etudiants = ($classe_id) ? $conn->query("SELECT * FROM etudiants WHERE classe_id = $classe_id") : null;
?>

<div class="container mt-4">
    <div class="card omega-card shadow-sm border-0">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Classe</label>
                    <select name="classe_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Choisir Classe --</option>
                        <?php while($c = $classes->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= ($classe_id == $c['id']) ? 'selected' : '' ?>><?= $c['nom_class'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Date de l'appel</label>
                    <input type="date" name="date" class="form-control" value="<?= $date_appel ?>">
                </div>
            </form>

            <?php if ($etudiants && $etudiants->num_rows > 0): ?>
            <form method="POST">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Étudiant</th>
                            <th>Présent</th>
                            <th>Absent</th>
                            <th>Retard</th>
                            <th>Justifié</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($e = $etudiants->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= $e['nom'] ?> <?= $e['prenom'] ?></strong><br><small><?= $e['code_etudiant'] ?></small></td>
                            <td><input type="radio" name="statut[<?= $e['code_etudiant'] ?>]" value="Present" checked></td>
                            <td><input type="radio" name="statut[<?= $e['code_etudiant'] ?>]" value="Absent"></td>
                            <td><input type="radio" name="statut[<?= $e['code_etudiant'] ?>]" value="Retard"></td>
                            <td><input type="checkbox" name="justifie[<?= $e['code_etudiant'] ?>]"></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_presence" class="btn btn-omega w-100 py-3 shadow">ENREGISTRER L'APPEL</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
