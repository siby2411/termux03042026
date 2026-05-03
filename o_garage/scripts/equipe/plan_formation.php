<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

// 1. Récupérer les mécaniciens sous le seuil de 60%
$alerte_query = "SELECT p.*, n.note_technique, n.commentaires 
                 FROM personnel p
                 JOIN notes_performance n ON p.id_personnel = n.id_personnel
                 WHERE n.note_technique < 60 
                 AND n.mois_annee = '" . date('Y-m') . "'";
$alertes = $db->query($alerte_query)->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h2 class="fw-bold text-danger"><i class="fas fa-graduation-cap me-2"></i>PROGRAMME DE RÉNOVATION TECHNIQUE</h2>
            <p class="text-muted">Analyse prédictive basée sur les notes de performance du mois en cours.</p>
        </div>
    </div>

    <div class="row">
        <?php if(empty($alertes)): ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-success d-inline-block shadow-sm">
                    <i class="fas fa-check-double me-2"></i> Félicitations : Tous les techniciens sont au-dessus du seuil de compétence (60%).
                </div>
            </div>
        <?php else: ?>
            <?php foreach($alertes as $a): ?>
                <div class="col-md-6 mb-4">
                    <div class="card border-start border-danger border-5 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold mb-0"><?= $a['nom_complet'] ?></h5>
                                    <span class="badge bg-dark mb-3"><?= $a['code_interne'] ?></span>
                                </div>
                                <div class="text-end">
                                    <div class="h4 text-danger fw-bold mb-0"><?= $a['note_technique'] ?>%</div>
                                    <small class="text-muted">Note Technique</small>
                                </div>
                            </div>
                            
                            <p class="small bg-light p-2 rounded italic">
                                <i class="fas fa-comment-dots me-1"></i> <strong>Avis Ingénieur :</strong> "<?= $a['commentaires'] ?>"
                            </p>

                            <hr>
                            <h6><i class="fas fa-lightbulb text-warning me-2"></i>Formations Suggérées :</h6>
                            <form action="assigner_formation.php" method="POST">
                                <input type="hidden" name="id_p" value="<?= $a['id_personnel'] ?>">
                                <div class="list-group list-group-flush mb-3">
                                    <?php 
                                    $formations = $db->query("SELECT * FROM modules_formation LIMIT 2");
                                    while($f = $formations->fetch()):
                                    ?>
                                    <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span>
                                            <input class="form-check-input me-2" type="radio" name="id_f" value="<?= $f['id_formation'] ?>" required>
                                            <strong><?= $f['titre_formation'] ?></strong> (<?= $f['domaine'] ?>)
                                        </span>
                                        <i class="fas fa-chevron-right text-muted small"></i>
                                    </label>
                                    <?php endwhile; ?>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm w-100 shadow-sm">
                                    Déclencher le Plan de Rénovation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
