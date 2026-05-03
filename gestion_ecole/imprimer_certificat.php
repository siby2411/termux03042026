<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$code = $_GET['code'] ?? '';
$annee = "2025-2026";

// Récupération complète : Étudiant + Classe + Filière + Paiements
$sql = "SELECT e.*, c.nom_classe, c.cycle, f.nom_filiere,
        (SELECT SUM(montant_paye) FROM paiements WHERE code_etudiant = e.code_etudiant AND type_paiement = 'Inscription') as total_paye,
        t.droit_inscription
        FROM etudiants e
        JOIN classes c ON e.id_classe = c.id_classe
        JOIN filieres f ON c.id_filiere = f.id_filiere
        JOIN tarifs t ON t.classe_id = c.id_classe
        WHERE e.code_etudiant = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $code);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Récupération des UV
$uv_res = $conn->query("SELECT uv.*, m.nom_matiere FROM unites_valeur uv 
                       JOIN matieres m ON uv.matiere_id = m.id 
                       WHERE uv.classe_id = " . $data['id_classe']);

include 'header_ecole.php';
?>

<style>
    @media print { .no-print { display: none; } body { background: white; } .certificat-border { border: 2px solid #000 !important; } }
    .certificat-border { border: 5px double #dee2e6; padding: 30px; background: #fff; }
    .stamp { border: 3px solid #d9534f; color: #d9534f; transform: rotate(-15deg); display: inline-block; padding: 5px 10px; font-weight: bold; }
</style>

<div class="container mt-5">
    <div class="no-print mb-4">
        <button onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer"></i> Imprimer le Certificat</button>
        <a href="gestion_inscription.php" class="btn btn-secondary">Retour</a>
    </div>

    <div class="certificat-border shadow-lg">
        <div class="row align-items-center mb-4">
            <div class="col-8">
                <h2 class="text-uppercase fw-bold">OMEGA ÉCOLE SUPÉRIEURE</h2>
                <p class="mb-0 small">Autorisation N° 0045/MESRS/2026</p>
                <p class="small">Année Académique : <?= $annee ?></p>
            </div>
            <div class="col-4 text-end">
                <div class="stamp">INSCRIPTION VALIDÉE</div>
            </div>
        </div>

        <h3 class="text-center bg-light py-2 border">CERTIFICAT D'INSCRIPTION</h3>

        <div class="row mt-4">
            <div class="col-6">
                <p><strong>Nom & Prénom :</strong> <?= $data['nom'] . ' ' . $data['prenom'] ?></p>
                <p><strong>Code Étudiant :</strong> <?= $data['code_etudiant'] ?></p>
                <p><strong>Date de Naissance :</strong> <?= $data['date_naissance'] ?></p>
            </div>
            <div class="col-6">
                <p><strong>Filière :</strong> <?= $data['nom_filiere'] ?></p>
                <p><strong>Cycle :</strong> <?= $data['cycle'] ?></p>
                <p><strong>Classe :</strong> <?= $data['nom_classe'] ?></p>
            </div>
        </div>

        <h5 class="mt-4 border-bottom">Situation Financière (Droit d'Inscription)</h5>
        <table class="table table-bordered table-sm">
            <tr>
                <th>Montant Exigé</th>
                <td><?= number_format($data['droit_inscription'], 0) ?> FCFA</td>
                <th>Montant Versé</th>
                <td class="fw-bold"><?= number_format($data['total_paye'], 0) ?> FCFA</td>
            </tr>
        </table>

        <h5 class="mt-4 border-bottom">Unités de Valeur (UV) Inscrites</h5>
        <div class="row">
            <?php while($uv = $uv_res->fetch_assoc()): ?>
                <div class="col-md-6 small">• <?= $uv['nom_uv'] ?> (Coef: <?= $uv['coefficient'] ?>)</div>
            <?php endwhile; ?>
        </div>

        <div class="row mt-5">
            <div class="col-8 small">Fait à Dakar, le <?= date('d/m/Y') ?><br>Généré numériquement par OMEGA System</div>
            <div class="col-4 text-center border-top">Signature du Registraire</div>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
