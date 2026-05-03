<?php 
$page_title = "Gamou de Daakaa - Célébration Religieuse";
include('../../includes/header.php'); 
require_once('../../config.php');

// Récupération des éditions précédentes
$stmt = $pdo->query("SELECT * FROM activites_religieuses ORDER BY annee DESC");
$editions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
    <!-- En-tête du Gamou -->
    <div class="text-center mb-5">
        <div class="badge bg-success p-3 mb-3 fs-5">
            <i class="fas fa-mosque fa-2x"></i> Événement religieux majeur
        </div>
        <h1 class="display-4 text-success">🕌 Gamou de Daakaa</h1>
        <p class="lead">La plus grande célébration religieuse de la région de Velingara</p>
        <div class="row justify-content-center mt-3">
            <div class="col-auto">
                <div class="alert alert-success">
                    <i class="fas fa-calendar-alt"></i> <strong>Édition 2025</strong> : Prévue pour Novembre 2025
                </div>
            </div>
        </div>
    </div>

    <!-- Image locale du Gamou de Daakaa -->
    <div class="row mb-5">
        <div class="col-md-10 mx-auto">
            <div class="card shadow-lg border-0">
                <img src="/assets/images/gamou_daakaa.jpg" 
                     class="card-img-top rounded" 
                     alt="Gamou de Daakaa - Célébration religieuse à Velingara"
                     style="height: 450px; width: 100%; object-fit: cover; object-position: center;">
                <div class="card-body bg-light text-center">
                    <p class="mb-0"><i class="fas fa-mosque"></i> <strong>Célébration du Gamou de Daakaa</strong> - Rassemblement des fidèles à Velingara</p>
                    <small class="text-muted">Plus de 25 000 participants lors de la dernière édition</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Présentation du Gamou -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card h-100 shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fas fa-info-circle"></i> Qu'est-ce que le Gamou de Daakaa ?</h3>
                </div>
                <div class="card-body">
                    <p>Le <strong>Gamou de Daakaa</strong> est une célébration religieuse annuelle qui commémore la naissance du Prophète Mohamed (PSL). À Velingara, cette manifestation revêt une importance particulière car elle rassemble les communautés musulmanes non seulement du Sénégal mais aussi des pays voisins :</p>
                    <ul>
                        <li><i class="fas fa-check-circle text-success"></i> Guinée Conakry</li>
                        <li><i class="fas fa-check-circle text-success"></i> Guinée Bissau</li>
                        <li><i class="fas fa-check-circle text-success"></i> Mali</li>
                    </ul>
                    <p>C'est un moment de <strong>cohésion sociale, de partage et de spiritualité</strong> qui renforce les liens entre les peuples frontaliers.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow">
                <div class="card-header bg-warning text-dark">
                    <h3 class="mb-0"><i class="fas fa-hand-holding-heart"></i> Contribution du Conseil Départemental</h3>
                </div>
                <div class="card-body">
                    <p>Sous l'impulsion du <strong>Président Ibrahima Barry</strong>, le Conseil Départemental s'engage pleinement dans l'organisation du Gamou de Daakaa :</p>
                    <ul>
                        <li><i class="fas fa-check-circle text-success"></i> <strong>Financement</strong> : 50 millions FCFA mobilisés pour l'édition 2025</li>
                        <li><i class="fas fa-check-circle text-success"></i> <strong>Infrastructures</strong> : Aménagement du site de Daakaa</li>
                        <li><i class="fas fa-check-circle text-success"></i> <strong>Restauration</strong> : Distribution de repas aux fidèles</li>
                        <li><i class="fas fa-check-circle text-success"></i> <strong>Sécurité</strong> : Dispositif renforcé</li>
                        <li><i class="fas fa-check-circle text-success"></i> <strong>Transport</strong> : Navettes gratuites pour les pèlerins</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Chiffres clés du Gamou -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">📊 Le Gamou de Daakaa en chiffres</h2>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <i class="fas fa-users fa-3x mb-2"></i>
                    <h2>30 000+</h2>
                    <p>Fidèles attendus en 2025</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-3x mb-2"></i>
                    <h2>50 M</h2>
                    <p>FCFA d'investissement</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-warning text-dark">
                <div class="card-body">
                    <i class="fas fa-utensils fa-3x mb-2"></i>
                    <h2>15 000+</h2>
                    <p>Repas servis</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <i class="fas fa-mosque fa-3x mb-2"></i>
                    <h2>3 Pays</h2>
                    <p>Participants internationaux</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Éditions précédentes -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">📜 Éditions précédentes</h2>
        </div>
        <?php foreach($editions as $edition): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar"></i> Édition <?php echo $edition['annee']; ?></h4>
                </div>
                <div class="card-body">
                    <p><strong><i class="fas fa-tag"></i> Événement :</strong> <?php echo htmlspecialchars($edition['evenement']); ?></p>
                    <p><strong><i class="fas fa-hand-holding-heart"></i> Contribution :</strong> <?php echo htmlspecialchars($edition['contribution_conseil']); ?></p>
                    <p><strong><i class="fas fa-coins"></i> Investissement :</strong> <?php echo number_format($edition['montant_investi'], 0, ',', ' '); ?> FCFA</p>
                    <p><strong><i class="fas fa-users"></i> Participants :</strong> <?php echo number_format($edition['nombre_participants'], 0, ',', ' '); ?> fidèles</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Témoignages -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h3 class="mb-0"><i class="fas fa-comment-dots"></i> Témoignages des fidèles</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="alert alert-light border-left-success">
                                <i class="fas fa-quote-left text-success"></i> Grâce au soutien du Conseil Départemental, le Gamou de Daakaa est devenu un événement incontournable dans la sous-région.
                                <br><strong>- Imam de Velingara</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-light border-left-success">
                                <i class="fas fa-quote-left text-success"></i> La générosité du Président Ibrahima Barry permet à des milliers de fidèles de célébrer dans les meilleures conditions.
                                <br><strong>- Comité d'organisation</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-light border-left-success">
                                <i class="fas fa-quote-left text-success"></i> Je viens chaque année de Guinée Conakry. L'accueil est exceptionnel.
                                <br><strong>- Alimou Diallo, pèlerin guinéen</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Programme prévisionnel 2025 -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h3 class="mb-0"><i class="fas fa-calendar-week"></i> Programme prévisionnel Gamou de Daakaa 2025</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-success">
                                <tr><th>Jour</th><th>Activité</th><th>Lieu</th><th>Horaire</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="bg-light">Vendredi</td><td>Arrivée des délégations et installation</td><td>Site de Daakaa</td><td>À partir de 10h</td></tr>
                                <tr><td class="bg-light">Samedi</td><td>Conférences religieuses et lecture du Coran</td><td>Grande mosquée</td><td>9h - 17h</td></tr>
                                <tr><td class="bg-light">Dimanche</td><td>Grand Gamou - Prière collective et prêche</td><td>Esplanade de la mosquée</td><td>10h - 13h</td></tr>
                                <tr><td class="bg-light">Dimanche</td><td>Distribution de repas et partage</td><td>Site du Gamou</td><td>13h - 16h</td></tr>
                                <tr><td class="bg-light">Lundi</td><td>Cérémonie de clôture et remerciements</td><td>Conseil Départemental</td><td>10h</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Appel à la prière / Verset coranique -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <i class="fas fa-hands-praying fa-4x mb-3"></i>
                    <h3>« Et accomplissez la prière, acquittez la zakât, et inclinez-vous avec ceux qui s'inclinent. »</h3>
                    <p class="mt-3">Sourate Al-Baqara (2:43)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
