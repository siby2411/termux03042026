<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Gestion du portefeuille-titres";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Ajout d'un titre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter_titre') {
        $code = trim($_POST['code']);
        $libelle = trim($_POST['libelle']);
        $type = $_POST['type_titre'];
        $date_acq = $_POST['date_acquisition'];
        $valeur = (float)$_POST['valeur_acquisition'];
        $quantite = (int)$_POST['quantite'];
        $frais = (float)$_POST['frais_acquisition'];
        $devise = $_POST['devise'];
        
        $stmt = $pdo->prepare("INSERT INTO PORTEFEUILLE_TITRES (code_titre, libelle, type_titre, date_acquisition, valeur_acquisition, quantite, frais_acquisition, devise) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $libelle, $type, $date_acq, $valeur, $quantite, $frais, $devise]);
        $titre_id = $pdo->lastInsertId();
        
        // Écriture comptable d'acquisition
        $compte_titre = ($type == 'PARTICIPATION') ? 261 : (($type == 'TIAP') ? 262 : ($type == 'AUTRE_IMMOBILISE' ? 263 : 271));
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, 521, ?, ?, 'TITRE')";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute([$date_acq, "Acquisition titre $code - $libelle", $compte_titre, $valeur + $frais, "TITRE-$code"]);
        
        $message = "✅ Titre ajouté - Valeur comptable: " . number_format($valeur + $frais, 0, ',', ' ') . " FCFA";
    }
    
    // Cession d'un titre
    if ($_POST['action'] === 'cesser_titre') {
        $titre_id = (int)$_POST['titre_id'];
        $date_cession = $_POST['date_cession'];
        $prix_cession = (float)$_POST['prix_cession'];
        $quantite_cession = (int)$_POST['quantite_cession'];
        
        $titre = $pdo->prepare("SELECT * FROM PORTEFEUILLE_TITRES WHERE id = ?");
        $titre->execute([$titre_id]);
        $t = $titre->fetch();
        
        $valeur_comptable = $t['valeur_comptable'] * $quantite_cession / $t['quantite'];
        $plus_value = $prix_cession - $valeur_comptable;
        
        // Écriture de cession
        if ($plus_value > 0) {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, 276, ?, ?, 'CESSION'), (?, ?, 276, ?, ?, ?, 'CESSION')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $date_cession, "Cession titre {$t['code_titre']}", $prix_cession, "CESS-{$t['code_titre']}",
                $date_cession, "Sortie titre {$t['code_titre']}", $t['compte_titre'], $valeur_comptable, "CESS-{$t['code_titre']}"
            ]);
        } else {
            $moins_value = abs($plus_value);
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, 277, ?, ?, 'CESSION'), (?, ?, 277, ?, ?, ?, 'CESSION')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $date_cession, "Cession titre {$t['code_titre']}", $prix_cession, "CESS-{$t['code_titre']}",
                $date_cession, "Sortie titre {$t['code_titre']}", $t['compte_titre'], $valeur_comptable, "CESS-{$t['code_titre']}"
            ]);
        }
        
        // Mise à jour du stock
        if ($quantite_cession >= $t['quantite']) {
            $update = $pdo->prepare("UPDATE PORTEFEUILLE_TITRES SET statut = 'CESSED' WHERE id = ?");
        } else {
            $new_quantite = $t['quantite'] - $quantite_cession;
            $new_valeur = $t['valeur_acquisition'] * $new_quantite / $t['quantite'];
            $update = $pdo->prepare("UPDATE PORTEFEUILLE_TITRES SET quantite = ?, valeur_acquisition = ?, statut = 'ACTIF' WHERE id = ?");
            $update->execute([$new_quantite, $new_valeur, $titre_id]);
        }
        $update->execute([$titre_id]);
        
        $message = "✅ Titre cédé - " . ($plus_value >= 0 ? "Plus-value" : "Moins-value") . " de " . number_format(abs($plus_value), 0, ',', ' ') . " FCFA";
    }
}

$titres = $pdo->query("SELECT * FROM PORTEFEUILLE_TITRES ORDER BY type_titre, code_titre")->fetchAll();
$total_portefeuille = array_sum(array_column($titres, 'valeur_comptable'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Gestion du portefeuille-titres</h5>
                <small>Titres de participation, TIAP, VMP - SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                <?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= count($titres) ?></h4>
                                <small>Titres détenus</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_portefeuille, 0, ',', ' ') ?> F</h4>
                                <small>Valeur totale du portefeuille</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#newTitreModal">+ Nouveau titre</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classification des titres -->
                <div class="alert alert-info">
                    <strong>📋 Classification SYSCOHADA des titres :</strong><br>
                    - <strong>Titres de participation</strong> (compte 261) : Prise de contrôle d'une autre société<br>
                    - <strong>TIAP</strong> (compte 262) : Titres immobilisés de l'activité de portefeuille<br>
                    - <strong>Autres titres immobilisés</strong> (compte 263) : Placements à long terme<br>
                    - <strong>VMP</strong> (compte 27) : Valeurs mobilières de placement (courant)
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Code</th><th>Libellé</th><th>Type</th><th>Date acq.</th>
                                <th class="text-end">Quantité</th><th class="text-end">Prix unitaire</th>
                                <th class="text-end">Valeur comptable</th><th>Statut</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($titres as $t): ?>
                            <tr>
                                <td class="text-center"><?= $t['code_titre'] ?> </td>
                                <td><?= htmlspecialchars($t['libelle']) ?> </td>
                                <td class="text-center"><span class="badge bg-primary"><?= $t['type_titre'] ?></span> </td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($t['date_acquisition'])) ?> </td>
                                <td class="text-end"><?= number_format($t['quantite'], 0, ',', ' ') ?> </td>
                                <td class="text-end"><?= number_format($t['valeur_unitaire'], 0, ',', ' ') ?> <?= $t['devise'] ?> </td>
                                <td class="text-end fw-bold"><?= number_format($t['valeur_comptable'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><span class="badge <?= $t['statut'] == 'ACTIF' ? 'bg-success' : 'bg-danger' ?>"><?= $t['statut'] ?></span> </td>
                                <td class="text-center">
                                    <?php if($t['statut'] == 'ACTIF'): ?>
                                    <button class="btn btn-sm btn-warning" onclick="cederTitre(<?= $t['id'] ?>, '<?= $t['code_titre'] ?>', <?= $t['quantite'] ?>, <?= $t['valeur_unitaire'] ?>)">
                                        <i class="bi bi-arrow-right"></i> Céder
                                    </button>
                                    <?php endif; ?>
                                 </td>
                             </tr>
                            <?php endforeach; ?>
                        </tbody>
                     </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal ajout titre -->
<div class="modal fade" id="newTitreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5>➕ Nouveau titre</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="ajouter_titre">
                    <div class="mb-2"><label>Code titre</label><input type="text" name="code" class="form-control" required></div>
                    <div class="mb-2"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                    <div class="mb-2"><label>Type de titre</label>
                        <select name="type_titre" class="form-select">
                            <option value="PARTICIPATION">Titre de participation</option>
                            <option value="TIAP">TIAP</option>
                            <option value="AUTRE_IMMOBILISE">Autre titre immobilisé</option>
                            <option value="VMP">Valeur mobilière de placement</option>
                        </select>
                    </div>
                    <div class="mb-2"><label>Date acquisition</label><input type="date" name="date_acquisition" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <div class="mb-2"><label>Quantité</label><input type="number" name="quantite" class="form-control" required></div>
                    <div class="mb-2"><label>Valeur d'acquisition (FCFA)</label><input type="number" name="valeur_acquisition" class="form-control" required></div>
                    <div class="mb-2"><label>Frais d'acquisition (FCFA)</label><input type="number" name="frais_acquisition" class="form-control" value="0"></div>
                    <div class="mb-2"><label>Devise</label><select name="devise" class="form-select"><option>XOF</option><option>EUR</option><option>USD</option></select></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Ajouter</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function cederTitre(id, code, quantite, prix) {
    let f = document.createElement('form');
    f.method = 'POST';
    f.innerHTML = '<input type="hidden" name="action" value="cesser_titre">' +
                  '<input type="hidden" name="titre_id" value="' + id + '">' +
                  '<label>Date de cession</label><input type="date" name="date_cession" value="<?= date('Y-m-d') ?>" class="form-control">' +
                  '<label>Quantité à céder (max ' + quantite + ')</label><input type="number" name="quantite_cession" max="' + quantite + '" class="form-control">' +
                  '<label>Prix de cession (FCFA)</label><input type="number" name="prix_cession" class="form-control">';
    document.body.appendChild(f);
    f.submit();
}
</script>

<?php include 'inc_footer.php'; ?>
