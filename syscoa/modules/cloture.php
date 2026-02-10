<?php
// travaux_cloture.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

$id_exercice = $_SESSION['id_exercice'];

// Récupérer les étapes de clôture
$sql_etapes = "SELECT * FROM calendrier_cloture 
               WHERE id_exercice = :id_exercice 
               ORDER BY ordre_etape";
$stmt = $pdo->prepare($sql_etapes);
$stmt->execute([':id_exercice' => $id_exercice]);
$etapes = $stmt->fetchAll();

// Vérifier l'état de clôture
$sql_etat = "SELECT statut_cloture FROM exercices_comptables 
             WHERE id_exercice = :id_exercice";
$stmt = $pdo->prepare($sql_etat);
$stmt->execute([':id_exercice' => $id_exercice]);
$etat = $stmt->fetch();

// Fonction pour calculer les amortissements
function calculerAmortissements($pdo, $id_exercice) {
    $sql = "SELECT i.*, 
            DATEDIFF(:date_fin, i.date_acquisition) / 365 as annees_ecoulees,
            i.valeur_acquisition * i.taux_amortissement / 100 as annuite_theorique,
            (SELECT SUM(montant_amorti) FROM amortissements_cloture 
             WHERE id_immobilisation = i.id_immobilisation) as total_amorti
            FROM immobilisations i
            WHERE i.est_amortissable = 1 
            AND i.date_acquisition <= :date_fin
            AND i.actif = 1";
    
    $date_fin = $pdo->query("SELECT date_fin FROM exercices_comptables 
                            WHERE id_exercice = $id_exercice")->fetchColumn();
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':date_fin' => $date_fin]);
    
    return $stmt->fetchAll();
}

// Fonction pour calculer les provisions
function calculerProvisions($pdo, $id_exercice) {
    $provisions = [];
    
    // Provisions pour dépréciation des stocks
    $sql_stock = "SELECT a.*, 
                  (a.stock_actuel * a.prix_unitaire * 0.1) as provision_stock
                  FROM articles_stock a
                  WHERE a.stock_actuel > 0 
                  AND DATEDIFF(NOW(), a.date_creation) > 365";
    
    $provisions['stocks'] = $pdo->query($sql_stock)->fetchAll();
    
    // Provisions pour créances douteuses
    $sql_creances = "SELECT t.id_tiers, t.nom, 
                    SUM(e.debit - e.credit) as solde_client,
                    CASE 
                        WHEN DATEDIFF(NOW(), MAX(e.date_ecriture)) > 90 THEN 0.5
                        WHEN DATEDIFF(NOW(), MAX(e.date_ecriture)) > 60 THEN 0.3
                        WHEN DATEDIFF(NOW(), MAX(e.date_ecriture)) > 30 THEN 0.1
                        ELSE 0
                    END as taux_provision
                    FROM ecritures e
                    JOIN tiers t ON e.code_tiers = t.code_tiers
                    WHERE e.compte_num LIKE '411%'
                    AND e.id_exercice = :id_exercice
                    GROUP BY t.id_tiers
                    HAVING solde_client > 0";
    
    $stmt = $pdo->prepare($sql_creances);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $provisions['creances'] = $stmt->fetchAll();
    
    return $provisions;
}

// Exécuter une étape de clôture
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['executer_etape'])) {
    $etape_id = secure_input($_POST['etape_id']);
    
    switch ($etape_id) {
        case 'AMORTISSEMENTS':
            // Calculer et enregistrer les amortissements
            $amortissements = calculerAmortissements($pdo, $id_exercice);
            
            foreach ($amortissements as $amort) {
                $annee_courante = min(1, $amort['annees_ecoulees']);
                $amort_annuel = $amort['valeur_acquisition'] * $amort['taux_amortissement'] / 100;
                $amort_exercice = $amort_annuel * $annee_courante;
                
                // Vérifier si déjà amorti
                $sql_check = "SELECT id FROM amortissements_cloture 
                             WHERE id_immobilisation = :id_immob 
                             AND id_exercice = :id_exercice";
                $stmt = $pdo->prepare($sql_check);
                $stmt->execute([
                    ':id_immob' => $amort['id_immobilisation'],
                    ':id_exercice' => $id_exercice
                ]);
                
                if (!$stmt->fetch()) {
                    $sql_insert = "INSERT INTO amortissements_cloture 
                                  (id_exercice, id_immobilisation, annee_amortissement, 
                                   montant_amorti, taux_amortissement, date_calcul)
                                  VALUES (:id_exercice, :id_immob, YEAR(NOW()), 
                                          :montant, :taux, NOW())";
                    
                    $stmt = $pdo->prepare($sql_insert);
                    $stmt->execute([
                        ':id_exercice' => $id_exercice,
                        ':id_immob' => $amort['id_immobilisation'],
                        ':montant' => $amort_exercice,
                        ':taux' => $amort['taux_amortissement']
                    ]);
                    
                    // Créer l'écriture comptable
                    $sql_ecriture = "INSERT INTO ecritures 
                                    (id_exercice, date_ecriture, journal_code, compte_num, 
                                     libelle, debit, credit, created_at)
                                    VALUES (:id_exercice, NOW(), 'OD', '681', 
                                            'Dotation aux amortissements - ' . :libelle, 
                                            :montant, 0, NOW())";
                    // À compléter selon la structure exacte
                }
            }
            break;
            
        case 'PROVISIONS':
            // Enregistrer les provisions
            $provisions = calculerProvisions($pdo, $id_exercice);
            
            foreach ($provisions['creances'] as $creance) {
                $montant_provision = $creance['solde_client'] * $creance['taux_provision'];
                
                if ($montant_provision > 0) {
                    $sql_provision = "INSERT INTO provisions_cloture 
                                     (id_exercice, type_provision, id_tiers, 
                                      montant_provision, motif, date_calcul)
                                     VALUES (:id_exercice, 'CREANCES_DOUTEUSES', 
                                             :id_tiers, :montant, :motif, NOW())";
                    
                    $stmt = $pdo->prepare($sql_provision);
                    $stmt->execute([
                        ':id_exercice' => $id_exercice,
                        ':id_tiers' => $creance['id_tiers'],
                        ':montant' => $montant_provision,
                        ':motif' => 'Client ' . $creance['nom'] . ' - Créance douteuse'
                    ]);
                }
            }
            break;
            
        case 'REGULARISATIONS':
            // Regularisations des charges et produits
            regulariserChargesProduits($pdo, $id_exercice);
            break;
            
        case 'BILAN':
            // Générer le bilan de clôture
            genererBilanCloture($pdo, $id_exercice);
            break;
            
        case 'OUVERTURE':
            // Ouvrir le nouvel exercice
            ouvrirNouvelExercice($pdo, $id_exercice);
            break;
    }
    
    // Marquer l'étape comme terminée
    $sql_update = "UPDATE calendrier_cloture 
                  SET date_execution = NOW(), statut = 'TERMINE'
                  WHERE id_etape = :etape_id AND id_exercice = :id_exercice";
    
    $stmt = $pdo->prepare($sql_update);
    $stmt->execute([
        ':etape_id' => $etape_id,
        ':id_exercice' => $id_exercice
    ]);
    
    $_SESSION['success'] = "Étape exécutée avec succès!";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Travaux de Clôture</title>
    <style>
        .cloture-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .etapes-progression {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .etape-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 5px solid #ddd;
            border-radius: 5px;
        }
        .etape-item.termine {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .etape-item.encours {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .etape-item.enattente {
            border-left-color: #6c757d;
            background: #f8f9fa;
        }
        .etape-numero {
            width: 40px;
            height: 40px;
            background: #6c757d;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
        .etape-item.termine .etape-numero {
            background: #28a745;
        }
        .etape-item.encours .etape-numero {
            background: #ffc107;
        }
        .calculs-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        .calcul-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-calculs {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table-calculs th, .table-calculs td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .bouton-cloture {
            background: #dc3545;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        .bouton-cloture:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .modal-confirmation {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            width: 500px;
        }
    </style>
</head>
<body>
    <div class="cloture-container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-times"></i> Travaux de Clôture</h1>
            <p>Exercice : <?php echo $_SESSION['exercice_nom']; ?></p>
            <p>Date de clôture : 
                <?php 
                $sql_date = "SELECT date_fin FROM exercices_comptables 
                            WHERE id_exercice = $id_exercice";
                echo date('d/m/Y', strtotime($pdo->query($sql_date)->fetchColumn()));
                ?>
            </p>
        </div>
        
        <!-- Barre d'état -->
        <div class="etat-cloture">
            <div class="alert alert-info">
                <strong>Statut :</strong> 
                <?php 
                switch($etat['statut_cloture']) {
                    case 'OUVERT': echo '<span class="badge badge-success">EXERCICE OUVERT</span>'; break;
                    case 'EN_COURS': echo '<span class="badge badge-warning">CLÔTURE EN COURS</span>'; break;
                    case 'CLOTURE': echo '<span class="badge badge-danger">EXERCICE CLÔTURÉ</span>'; break;
                }
                ?>
            </div>
            
            <?php if ($etat['statut_cloture'] == 'OUVERT'): ?>
            <button onclick="demarrerCloture()" class="btn btn-danger">
                <i class="fas fa-play"></i> Démarrer les travaux de clôture
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Étapes de progression -->
        <div class="etapes-progression">
            <h3><i class="fas fa-tasks"></i> Étapes de clôture</h3>
            
            <?php foreach ($etapes as $etape): 
                $classe = strtolower($etape['statut']);
            ?>
            <div class="etape-item <?php echo $classe; ?>">
                <div class="etape-numero"><?php echo $etape['ordre_etape']; ?></div>
                <div class="etape-info">
                    <h4><?php echo $etape['nom_etape']; ?></h4>
                    <p><?php echo $etape['description']; ?></p>
                    <?php if ($etape['date_execution']): ?>
                    <small>Exécuté le : <?php echo date('d/m/Y H:i', strtotime($etape['date_execution'])); ?></small>
                    <?php endif; ?>
                </div>
                <div class="etape-actions">
                    <?php if ($etape['statut'] == 'EN_COURS'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="etape_id" value="<?php echo $etape['code_etape']; ?>">
                        <button type="submit" name="executer_etape" class="btn btn-primary btn-sm">
                            Exécuter
                        </button>
                    </form>
                    <?php elseif ($etape['statut'] == 'TERMINE'): ?>
                    <span class="badge badge-success">✓ Terminé</span>
                    <?php else: ?>
                    <span class="badge badge-secondary">En attente</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Calculs automatiques -->
        <div class="calculs-section">
            <!-- Amortissements -->
            <div class="calcul-card">
                <h4><i class="fas fa-calculator"></i> Amortissements à constater</h4>
                <table class="table-calculs">
                    <thead>
                        <tr>
                            <th>Immobilisation</th>
                            <th>Valeur</th>
                            <th>Taux</th>
                            <th>Amortissement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $amortissements = calculerAmortissements($pdo, $id_exercice);
                        $total_amort = 0;
                        
                        foreach ($amortissements as $amort):
                            $amort_annuel = $amort['valeur_acquisition'] * $amort['taux_amortissement'] / 100;
                            $total_amort += $amort_annuel;
                        ?>
                        <tr>
                            <td><?php echo $amort['designation']; ?></td>
                            <td><?php echo number_format($amort['valeur_acquisition'], 0, ',', ' '); ?> FCFA</td>
                            <td><?php echo $amort['taux_amortissement']; ?>%</td>
                            <td><?php echo number_format($amort_annuel, 0, ',', ' '); ?> FCFA</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total">
                            <td colspan="3"><strong>TOTAL</strong></td>
                            <td><strong><?php echo number_format($total_amort, 0, ',', ' '); ?> FCFA</strong></td>
                        </tr>
                    </tbody>
                </table>
                <button onclick="constaterAmortissements()" class="btn btn-warning">
                    <i class="fas fa-check"></i> Constater les amortissements
                </button>
            </div>
            
            <!-- Provisions -->
            <div class="calcul-card">
                <h4><i class="fas fa-shield-alt"></i> Provisions à constituer</h4>
                <table class="table-calculs">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Détail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $provisions = calculerProvisions($pdo, $id_exercice);
                        $total_provisions = 0;
                        
                        // Provisions stocks
                        $prov_stocks = 0;
                        foreach ($provisions['stocks'] as $stock) {
                            $prov_stocks += $stock['provision_stock'];
                        }
                        $total_provisions += $prov_stocks;
                        ?>
                        <tr>
                            <td>Stocks périmés</td>
                            <td><?php echo number_format($prov_stocks, 0, ',', ' '); ?> FCFA</td>
                            <td><?php echo count($provisions['stocks']); ?> articles</td>
                        </tr>
                        
                        <?php 
                        // Provisions créances
                        $prov_creances = 0;
                        foreach ($provisions['creances'] as $creance) {
                            $montant = $creance['solde_client'] * $creance['taux_provision'];
                            $prov_creances += $montant;
                        }
                        $total_provisions += $prov_creances;
                        ?>
                        <tr>
                            <td>Créances douteuses</td>
                            <td><?php echo number_format($prov_creances, 0, ',', ' '); ?> FCFA</td>
                            <td><?php echo count($provisions['creances']); ?> clients</td>
                        </tr>
                        
                        <tr class="total">
                            <td><strong>TOTAL</strong></td>
                            <td colspan="2">
                                <strong><?php echo number_format($total_provisions, 0, ',', ' '); ?> FCFA</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button onclick="constaterProvisions()" class="btn btn-warning">
                    <i class="fas fa-check"></i> Constituer les provisions
                </button>
            </div>
        </div>
        
        <!-- Bouton de clôture définitive -->
        <?php if ($etat['statut_cloture'] == 'EN_COURS'): 
            // Vérifier si toutes les étapes sont terminées
            $sql_check = "SELECT COUNT(*) FROM calendrier_cloture 
                         WHERE id_exercice = :id_exercice AND statut != 'TERMINE'";
            $stmt = $pdo->prepare($sql_check);
            $stmt->execute([':id_exercice' => $id_exercice]);
            $etapes_restantes = $stmt->fetchColumn();
        ?>
        <div class="cloture-definitive">
            <div class="alert alert-warning">
                <h4><i class="fas fa-exclamation-triangle"></i> Clôture définitive</h4>
                <p>Une fois la clôture définitive effectuée, l'exercice sera verrouillé et aucune modification ne sera possible.</p>
                <p>Étapes restantes : <?php echo $etapes_restantes; ?></p>
            </div>
            
            <button onclick="confirmerCloture()" 
                    class="bouton-cloture" 
                    <?php echo $etapes_restantes > 0 ? 'disabled' : ''; ?>>
                <i class="fas fa-lock"></i> PROCÉDER À LA CLÔTURE DÉFINITIVE
            </button>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal de confirmation -->
    <div id="modalConfirmation" class="modal-confirmation">
        <div class="modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmation de clôture</h3>
            <p>Êtes-vous sûr de vouloir clôturer définitivement l'exercice <strong><?php echo $_SESSION['exercice_nom']; ?></strong> ?</p>
            <p>Cette action est irréversible.</p>
            <div class="modal-actions">
                <button onclick="fermerModal()" class="btn btn-secondary">Annuler</button>
                <button onclick="executerClotureDefinitive()" class="btn btn-danger">Confirmer la clôture</button>
            </div>
        </div>
    </div>
    
    <script>
    function demarrerCloture() {
        if (confirm('Démarrer les travaux de clôture ?')) {
            window.location.href = 'demarrer_cloture.php?id_exercice=<?php echo $id_exercice; ?>';
        }
    }
    
    function constaterAmortissements() {
        fetch('api/constater_amortissements.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_exercice: <?php echo $id_exercice; ?>
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Amortissements constatés avec succès!');
                location.reload();
            }
        });
    }
    
    function constaterProvisions() {
        fetch('api/constater_provisions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_exercice: <?php echo $id_exercice; ?>
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Provisions constituées avec succès!');
                location.reload();
            }
        });
    }
    
    function confirmerCloture() {
        document.getElementById('modalConfirmation').style.display = 'block';
    }
    
    function fermerModal() {
        document.getElementById('modalConfirmation').style.display = 'none';
    }
    
    function executerClotureDefinitive() {
        fetch('api/cloturer_exercice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_exercice: <?php echo $id_exercice; ?>
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Exercice clôturé avec succès!');
                window.location.href = 'dashboard_main.php';
            }
        });
    }
    </script>
</body>
</html>
