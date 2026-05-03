<?php
// rapprochement_bancaire.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

// Récupérer les comptes bancaires
$sql_comptes = "SELECT c.numero, c.libelle, 
                (SELECT SUM(debit - credit) FROM ecritures 
                 WHERE compte_num = c.numero AND id_exercice = :id_exercice) as solde_comptable
                FROM comptes_ohada c 
                WHERE c.numero LIKE '52%' AND c.actif = 1";
$stmt = $pdo->prepare($sql_comptes);
$stmt->execute([':id_exercice' => $_SESSION['id_exercice']]);
$comptes_bancaires = $stmt->fetchAll();

// Gérer l'ajout d'un relevé
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_releve'])) {
    $compte_num = secure_input($_POST['compte_num']);
    $date_releve = secure_input($_POST['date_releve']);
    $solde_releve = secure_input($_POST['solde_releve']);
    
    $sql_insert = "INSERT INTO releves_bancaires 
                   (compte_num, date_releve, solde_releve, id_exercice, created_at)
                   VALUES (:compte_num, :date_releve, :solde_releve, :id_exercice, NOW())";
    
    $stmt = $pdo->prepare($sql_insert);
    $stmt->execute([
        ':compte_num' => $compte_num,
        ':date_releve' => $date_releve,
        ':solde_releve' => $solde_releve,
        ':id_exercice' => $_SESSION['id_exercice']
    ]);
    
    $_SESSION['success'] = "Relevé bancaire ajouté avec succès!";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapprochement Bancaire</title>
    <style>
        .rapprochement-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .comptes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .compte-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 5px solid #3498db;
        }
        .releve-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .table-rapprochement {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table-rapprochement th {
            background: #2c3e50;
            color: white;
            padding: 12px;
        }
        .table-rapprochement td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .ecriture-non-rapprochee {
            background: #ffe6e6;
        }
        .ecriture-rapprochee {
            background: #e6ffe6;
        }
        .statut-rapprochement {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .statut-ok {
            background: #d4edda;
            color: #155724;
        }
        .statut-ko {
            background: #f8d7da;
            color: #721c24;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
        }
    </style>
</head>
<body>
    <div class="rapprochement-container">
        <div class="page-header">
            <h1><i class="fas fa-university"></i> Rapprochement Bancaire</h1>
            <p>Synchronisation des écritures comptables avec les relevés bancaires</p>
        </div>
        
        <!-- Vue d'ensemble des comptes -->
        <div class="comptes-grid">
            <?php foreach ($comptes_bancaires as $compte): ?>
            <div class="compte-card">
                <h3><?php echo $compte['numero']; ?> - <?php echo $compte['libelle']; ?></h3>
                <div class="compte-solde">
                    <h4>Solde comptable :</h4>
                    <p class="montant"><?php echo number_format($compte['solde_comptable'], 0, ',', ' '); ?> FCFA</p>
                </div>
                <button onclick="ouvrirRapprochement('<?php echo $compte['numero']; ?>')" 
                        class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Effectuer rapprochement
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Formulaire d'ajout de relevé -->
        <div class="releve-form">
            <h3><i class="fas fa-file-upload"></i> Ajouter un relevé bancaire</h3>
            <form method="POST">
                <div class="form-grid">
                    <div>
                        <label>Compte bancaire :</label>
                        <select name="compte_num" required>
                            <?php foreach ($comptes_bancaires as $compte): ?>
                            <option value="<?php echo $compte['numero']; ?>">
                                <?php echo $compte['numero']; ?> - <?php echo $compte['libelle']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Date du relevé :</label>
                        <input type="date" name="date_releve" required 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label>Solde du relevé :</label>
                        <input type="number" name="solde_releve" step="0.01" required 
                               placeholder="0.00">
                    </div>
                </div>
                <button type="submit" name="ajouter_releve" class="btn btn-success">
                    <i class="fas fa-save"></i> Enregistrer le relevé
                </button>
            </form>
        </div>
        
        <!-- Liste des rapprochements -->
        <div class="rapprochements-list">
            <h3><i class="fas fa-history"></i> Historique des rapprochements</h3>
            <table class="table-rapprochement">
                <thead>
                    <tr>
                        <th>Compte</th>
                        <th>Date relevé</th>
                        <th>Solde relevé</th>
                        <th>Solde comptable</th>
                        <th>Différence</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_historique = "SELECT r.*, c.libelle as compte_libelle,
                                      (SELECT SUM(debit - credit) FROM ecritures 
                                       WHERE compte_num = r.compte_num AND date_ecriture <= r.date_releve) as solde_comptable
                                      FROM releves_bancaires r
                                      JOIN comptes_ohada c ON r.compte_num = c.numero
                                      WHERE r.id_exercice = :id_exercice
                                      ORDER BY r.date_releve DESC";
                    $stmt = $pdo->prepare($sql_historique);
                    $stmt->execute([':id_exercice' => $_SESSION['id_exercice']]);
                    $releves = $stmt->fetchAll();
                    
                    foreach ($releves as $releve):
                        $difference = $releve['solde_releve'] - $releve['solde_comptable'];
                        $statut = abs($difference) < 0.01 ? 'OK' : 'NON RAPPROCHÉ';
                    ?>
                    <tr>
                        <td><?php echo $releve['compte_num']; ?><br>
                            <small><?php echo $releve['compte_libelle']; ?></small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($releve['date_releve'])); ?></td>
                        <td><?php echo number_format($releve['solde_releve'], 0, ',', ' '); ?> FCFA</td>
                        <td><?php echo number_format($releve['solde_comptable'], 0, ',', ' '); ?> FCFA</td>
                        <td class="<?php echo $difference == 0 ? 'positive' : 'negative'; ?>">
                            <?php echo number_format($difference, 0, ',', ' '); ?> FCFA
                        </td>
                        <td>
                            <span class="statut-rapprochement 
                                <?php echo $statut == 'OK' ? 'statut-ok' : 'statut-ko'; ?>">
                                <?php echo $statut; ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="voirDetails(<?php echo $releve['id']; ?>)" 
                                    class="btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="corrigerRapprochement(<?php echo $releve['id']; ?>)" 
                                    class="btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal pour le rapprochement -->
    <div id="modalRapprochement" class="modal">
        <div class="modal-content">
            <span onclick="fermerModal()" style="float:right;cursor:pointer;">&times;</span>
            <h2 id="modalTitre"></h2>
            <div id="modalContent"></div>
        </div>
    </div>
    
    <script>
    function ouvrirRapprochement(compteNum) {
        // Charger les écritures non rapprochées
        fetch(`api/get_ecritures_non_rapprochees.php?compte_num=${compteNum}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalTitre').innerHTML = 
                    `Rapprochement du compte ${compteNum}`;
                
                let html = `
                    <div class="rapprochement-interface">
                        <h3>Écritures à rapprocher :</h3>
                        <div class="ecritures-list">
                `;
                
                data.ecritures.forEach(ecriture => {
                    html += `
                        <div class="ecriture-item" id="ecriture-${ecriture.id}">
                            <input type="checkbox" id="check-${ecriture.id}" 
                                   onchange="selectionnerEcriture(${ecriture.id})">
                            <label>${ecriture.date_ecriture} - ${ecriture.libelle}</label>
                            <span class="montant">${ecriture.debit > 0 ? 
                                'Débit: ' + ecriture.debit : 
                                'Crédit: ' + ecriture.credit} FCFA</span>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                        <button onclick="validerRapprochement('${compteNum}')" 
                                class="btn btn-success">
                            <i class="fas fa-check"></i> Valider le rapprochement
                        </button>
                    </div>
                `;
                
                document.getElementById('modalContent').innerHTML = html;
                document.getElementById('modalRapprochement').style.display = 'block';
            });
    }
    
    function validerRapprochement(compteNum) {
        // Récupérer les écritures sélectionnées
        const selected = [];
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            const id = checkbox.id.replace('check-', '');
            selected.push(id);
        });
        
        // Envoyer au serveur
        fetch('api/valider_rapprochement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                compte_num: compteNum,
                ecritures_ids: selected
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Rapprochement validé avec succès!');
                location.reload();
            }
        });
    }
    
    function fermerModal() {
        document.getElementById('modalRapprochement').style.display = 'none';
    }
    
    // Fermer modal si clic en dehors
    window.onclick = function(event) {
        const modal = document.getElementById('modalRapprochement');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>
