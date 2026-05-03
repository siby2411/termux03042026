<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$id_etudiant = $_GET['id'] ?? 0;

// Récupération des infos avec votre structure de table
$sql = "SELECT e.*, c.nom_class, b.* FROM etudiants e 
        JOIN classes c ON e.classe_id = c.id 
        LEFT JOIN bulletins b ON e.code_etudiant = b.code_etudiant 
        WHERE e.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_etudiant);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

include 'header_ecole.php';
?>

<div class="container mt-5">
    <div class="bg-white p-5 shadow rounded border-top border-5 border-info">
        <div class="row mb-4">
            <div class="col-6">
                <h1 class="display-6 fw-bold">BULLETIN</h1>
                <p class="badge bg-secondary">CODE : <?= $data['code_etudiant'] ?></p>
            </div>
            <div class="col-6 text-end">
                <h3><?= strtoupper($data['nom']) ?> <?= $data['prenom'] ?></h3>
                <p>Classe : <?= $data['nom_class'] ?></p>
            </div>
        </div>

        <table class="table table-hover border">
            <thead class="table-light">
                <tr>
                    <th>Semestre</th>
                    <th>Moyenne</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Semestre 1</td>
                    <td class="fw-bold"><?= number_format($data['moyenne_semestre1'] ?? 0, 2) ?></td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Semestre 2</td>
                    <td class="fw-bold"><?= number_format($data['moyenne_semestre2'] ?? 0, 2) ?></td>
                    <td>-</td>
                </tr>
                <tr class="table-primary">
                    <td class="fw-bold">MOYENNE ANNUELLE</td>
                    <td class="fw-bold"><?= number_format($data['moyenne_annuelle'] ?? 0, 2) ?></td>
                    <td class="fw-bold"><?= $data['statut_final'] ?? 'En attente' ?></td>
                </tr>
            </tbody>
        </table>

        <div class="mt-4 d-print-none text-center">
            <button onclick="window.print()" class="btn btn-dark btn-lg"><i class="bi bi-printer"></i> Imprimer Officiellement</button>
            <a href="crud_etudiants.php" class="btn btn-outline-secondary btn-lg">Retour</a>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
