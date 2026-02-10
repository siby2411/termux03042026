<?php
/**
 * Module: CLOTURE
 * SyscoHADA - SENECOM SA
 * Conformité OHADA Révision 2023
 */

require_once __DIR__ . '/../../config.php';
verifier_session();

$conn = get_db_connection();

// Vérifier l'exercice actif
$result = $conn->query("SELECT * FROM exercices_comptables WHERE statut = 'actif' LIMIT 1");
if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">';
    echo '<h4><i class="bi bi-exclamation-triangle"></i> Exercice comptable non trouvé</h4>';
    echo '<p>Pour utiliser ce module, vous devez avoir un exercice comptable actif.</p>';
    echo '<a href="' . BASE_URL . 'index.php?module=exercices_comptables" class="btn btn-primary">';
    echo '<i class="bi bi-plus-circle"></i> Créer un exercice</a>';
    echo '</div>';
    $conn->close();
    return;
}

$exercice = $result->fetch_assoc();

// Journaliser l'accès
logger_action("Accès module cloture", "Exercice: " . $exercice['libelle']);

// Titres des modules
$titres = [
    'flux' => 'Flux de Trésorerie',
    'soldes' => 'Soldes Intermédiaires de Gestion',
    'budget' => 'Gestion Budgétaire',
    'cloture' => 'Travaux de Clôture',
    'rapprochement' => 'Rapprochement Bancaire',
    'journaux' => 'Journaux Comptables',
    'articles' => 'Gestion des Articles'
];

// Icônes des modules
$icones = [
    'flux' => 'cash-stack',
    'soldes' => 'calculator',
    'budget' => 'pie-chart',
    'cloture' => 'lock',
    'rapprochement' => 'arrow-left-right',
    'journaux' => 'journal',
    'articles' => 'box'
];
?>

<div class="row mb-4">
    <div class="col-12">
        <h2>
            <i class="bi bi-<?php echo $icones['cloture']; ?>"></i>
            <?php echo $titres['cloture']; ?>
        </h2>
        <div class="alert alert-success">
            <i class="bi bi-calendar-check"></i>
            Exercice actif : <strong><?php echo htmlspecialchars($exercice['libelle']); ?></strong>
            (<?php echo date('d/m/Y', strtotime($exercice['date_debut'])); ?> - 
             <?php echo date('d/m/Y', strtotime($exercice['date_fin'])); ?>)
        </div>
    </div>
</div>

<?php if ('cloture' == 'flux'): ?>
<!-- Module Flux de Trésorerie -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tableau des Flux de Trésorerie</h5>
                <p class="card-subtitle text-muted">Conforme au SYSCOHADA - Méthode indirecte</p>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Le tableau des flux de trésorerie retrace les entrées et sorties de liquidités
                    selon les trois activités : exploitation, investissement, financement.
                </div>
                
                <form id="formFlux" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="date_debut" class="form-label">Date début</label>
                        <input type="date" class="form-control" id="date_debut" 
                               value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="date_fin" class="form-label">Date fin</label>
                        <input type="date" class="form-control" id="date_fin" 
                               value="<?php echo date('Y-m-t'); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator"></i> Calculer les flux
                        </button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tableFlux">
                        <thead class="table-dark">
                            <tr>
                                <th colspan="3" class="text-center">TABLEAU DES FLUX DE TRÉSORERIE</th>
                            </tr>
                            <tr>
                                <th width="60%">RUBRIQUES</th>
                                <th width="20%" class="text-end">PÉRIODE</th>
                                <th width="20%" class="text-end">CUMUL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-primary">
                                <td colspan="3"><strong>A - FLUX DE TRÉSORERIE LIÉS À L'EXPLOITATION</strong></td>
                            </tr>
                            <tr>
                                <td>Résultat net de l'exercice</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 30px;">+ Dotations aux amortissements</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 30px;">+ Variations du besoin en fonds de roulement</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>Flux nets de trésorerie liés à l'exploitation</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            
                            <tr class="table-primary">
                                <td colspan="3"><strong>B - FLUX DE TRÉSORERIE LIÉS À L'INVESTISSEMENT</strong></td>
                            </tr>
                            <tr>
                                <td>Acquisitions d'immobilisations</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>Cessions d'immobilisations</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>Flux nets de trésorerie liés à l'investissement</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            
                            <tr class="table-primary">
                                <td colspan="3"><strong>C - FLUX DE TRÉSORERIE LIÉS AU FINANCEMENT</strong></td>
                            </tr>
                            <tr>
                                <td>Augmentations de capital</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>Emprunts et dettes financières</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>Flux nets de trésorerie liés au financement</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            
                            <tr class="table-success">
                                <td><strong>TRÉSORERIE NETTE DE LA PÉRIODE (A+B+C)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            
                            <tr>
                                <td>Trésorerie d'ouverture</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            
                            <tr class="table-warning">
                                <td><strong>TRÉSORERIE DE CLÔTURE</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-success">
                        <i class="bi bi-file-earmark-pdf"></i> Exporter en PDF
                    </button>
                    <button class="btn btn-primary">
                        <i class="bi bi-file-earmark-excel"></i> Exporter en Excel
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-printer"></i> Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour le module Flux
document.getElementById('formFlux').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const dateDebut = document.getElementById('date_debut').value;
    const dateFin = document.getElementById('date_fin').value;
    
    // Simulation de calcul
    alert('Calcul des flux de trésorerie pour la période ' + dateDebut + ' au ' + dateFin);
});
</script>

<?php elseif ('cloture' == 'soldes'): ?>
<!-- Module Soldes Intermédiaires de Gestion -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Soldes Intermédiaires de Gestion (SIG)</h5>
                <p class="card-subtitle text-muted">Calcul conforme aux normes OHADA</p>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Les SIG permettent d'analyser la formation du résultat en décomposant
                    la valeur ajoutée, l'excédent brut d'exploitation, etc.
                </div>
                
                <form class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Exercice</label>
                        <select class="form-select">
                            <option value="<?php echo $exercice['id']; ?>" selected>
                                <?php echo htmlspecialchars($exercice['libelle']); ?>
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Période</label>
                        <select class="form-select">
                            <option value="annuel" selected>Annuel</option>
                            <option value="trim1">1er Trimestre</option>
                            <option value="trim2">2ème Trimestre</option>
                            <option value="trim3">3ème Trimestre</option>
                            <option value="trim4">4ème Trimestre</option>
                            <option value="mensuel">Mensuel</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calculator"></i> Calculer les SIG
                        </button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="60%">SOLDES INTERMÉDIAIRES DE GESTION</th>
                                <th width="20%" class="text-end">N</th>
                                <th width="20%" class="text-end">N-1</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-primary">
                                <td colspan="3"><strong>I - ACTIVITÉ ORDINAIRE</strong></td>
                            </tr>
                            <tr>
                                <td>1. Chiffre d'affaires net</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>2. Production stockée</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>3. Production immobilisée</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>4. PRODUCTION DE L'EXERCICE (1+2+3)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr>
                                <td>5. Achats consommés</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>6. Autres charges externes</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>7. VALEUR AJOUTÉE (4-5-6)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr>
                                <td>8. Charges de personnel</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>9. EXCÉDENT BRUT D'EXPLOITATION (7-8)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr>
                                <td>10. Autres produits d'exploitation</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>11. Autres charges d'exploitation</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>12. Dotations aux amortissements</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>13. RÉSULTAT D'EXPLOITATION (9+10-11-12)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3"><strong>II - ACTIVITÉ FINANCIÈRE</strong></td>
                            </tr>
                            <tr>
                                <td>14. Produits financiers</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>15. Charges financières</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>16. RÉSULTAT FINANCIER (14-15)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>17. RÉSULTAT COURANT (13+16)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3"><strong>III - ACTIVITÉ EXCEPTIONNELLE</strong></td>
                            </tr>
                            <tr>
                                <td>18. Produits exceptionnels</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>19. Charges exceptionnelles</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>20. RÉSULTAT EXCEPTIONNEL (18-19)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                            <tr>
                                <td>21. Participation des salariés</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr>
                                <td>22. Impôts sur les bénéfices</td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                                <td class="text-end"><?php echo format_montant_ohada(0); ?></td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>23. RÉSULTAT NET DE L'EXERCICE (17+20-21-22)</strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                                <td class="text-end"><strong><?php echo format_montant_ohada(0); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Analyse des SIG</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>Marge Commerciale :</strong> 
                                        <span class="float-end">0.00%</span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Valeur Ajoutée / CA :</strong>
                                        <span class="float-end">0.00%</span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>EBE / CA :</strong>
                                        <span class="float-end">0.00%</span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Résultat Exploitation / CA :</strong>
                                        <span class="float-end">0.00%</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Graphique des SIG</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartSIG" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique SIG
const ctxSIG = document.getElementById('chartSIG').getContext('2d');
const chartSIG = new Chart(ctxSIG, {
    type: 'bar',
    data: {
        labels: ['VA', 'EBE', 'Rés Expl', 'Rés Courant', 'Rés Net'],
        datasets: [{
            label: 'N',
            data: [0, 0, 0, 0, 0],
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }, {
            label: 'N-1',
            data: [0, 0, 0, 0, 0],
            backgroundColor: 'rgba(255, 99, 132, 0.7)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Montant (FCFA)'
                }
            }
        }
    }
});
</script>

<?php else: ?>
<!-- Modules génériques -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Module <?php echo $titres['cloture']; ?></h5>
                <p class="card-subtitle text-muted">En cours d'implémentation</p>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-tools"></i>
                    <strong>Module en développement</strong>
                    <p>Ce module est actuellement en cours de développement selon les spécifications OHADA.</p>
                    <p>Les fonctionnalités seront disponibles prochainement.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card module-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Fonctionnalités disponibles
                                </h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Gestion des exercices comptables</li>
                                    <li class="list-group-item">Suivi en temps réel</li>
                                    <li class="list-group-item">Export des données</li>
                                    <li class="list-group-item">Conformité OHADA</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card module-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-clock text-warning"></i>
                                    Prochaines fonctionnalités
                                </h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Automatisation des calculs</li>
                                    <li class="list-group-item">Rapports détaillés</li>
                                    <li class="list-group-item">Analyses avancées</li>
                                    <li class="list-group-item">Intégration fiscale</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>index.php?module=dashboard" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$conn->close();
