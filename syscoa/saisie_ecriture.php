<?php
session_start();
require_once 'config/database.php';

$title = "Saisie d'Écriture Comptable";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - SYSCOHADA Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        .form-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0;
        }
        .form-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-invoice me-2"></i><?php echo $title; ?>
                </h1>
                <a href="journal_comptable.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au Journal
                </a>
            </div>

            <div class="form-card">
                <div class="form-header">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Nouvelle Écriture Comptable</h4>
                </div>
                <div class="form-body">
                    <form id="formEcriture" method="POST" action="controllers/save_ecriture.php">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Date Comptable *</label>
                                <input type="date" class="form-control" name="date_ecriture" required 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Numéro de Pièce *</label>
                                <input type="text" class="form-control" name="numero_piece" required 
                                       placeholder="EX-2024-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Référence</label>
                                <input type="text" class="form-control" name="reference" 
                                       placeholder="Référence supplémentaire">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Libellé de l'Écriture *</label>
                                <input type="text" class="form-control" name="libelle" required 
                                       placeholder="Libellé descriptif de l'opération">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Journal Comptable *</label>
                                <select class="form-select" name="code_journal" required>
                                    <option value="">Sélectionnez un journal</option>
                                    <option value="ACH">ACH - Achats</option>
                                    <option value="VEN">VEN - Ventes</option>
                                    <option value="BQ">BQ - Banque</option>
                                    <option value="CAI">CAI - Caisse</option>
                                    <option value="OD">OD - Opérations Diverses</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type d'Opération</label>
                                <select class="form-select" name="type_operation">
                                    <option value="courante">Opération Courante</option>
                                    <option value="ajustement">Ajustement</option>
                                    <option value="regularisation">Régularisation</option>
                                    <option value="cloture">Clôture</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Lignes d'écriture -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lignes d'Écriture</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="ajouterLigne()">
                                    <i class="fas fa-plus me-1"></i>Ajouter une Ligne
                                </button>
                            </div>
                            
                            <div id="lignesEcriture">
                                <div class="ligne-ecriture border rounded p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Compte *</label>
                                            <select class="form-select compte-select" name="comptes[]" required>
                                                <option value="">Choisir un compte</option>
                                                <?php
                                                try {
                                                    $stmt = $pdo->query("SELECT numero_compte, libelle FROM comptes_ohada ORDER BY numero_compte");
                                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        echo "<option value='{$row['numero_compte']}'>{$row['numero_compte']} - {$row['libelle']}</option>";
                                                    }
                                                } catch (PDOException $e) {
                                                    echo "<option value=''>Erreur chargement des comptes</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Libellé Ligne</label>
                                            <input type="text" class="form-control" name="libelles_ligne[]" 
                                                   placeholder="Libellé de la ligne">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Débit</label>
                                            <input type="number" class="form-control montant debit" name="debits[]" 
                                                   step="0.01" min="0" placeholder="0.00">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Crédit</label>
                                            <input type="number" class="form-control montant credit" name="credits[]" 
                                                   step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong>Total Débit: <span id="totalDebit">0.00</span> FCFA</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong>Total Crédit: <span id="totalCredit">0.00</span> FCFA</strong>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-2"></i>Effacer
                            </button>
                            <div>
                                <button type="submit" name="action" value="brouillon" class="btn btn-warning me-2">
                                    <i class="fas fa-save me-2"></i>Enregistrer Brouillon
                                </button>
                                <button type="submit" name="action" value="valider" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Valider l'Écriture
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let ligneCount = 1;

        function ajouterLigne() {
            ligneCount++;
            const nouvelleLigne = `
                <div class="ligne-ecriture border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Compte *</label>
                            <select class="form-select compte-select" name="comptes[]" required>
                                <option value="">Choisir un compte</option>
                                ${document.querySelector('.compte-select').innerHTML}
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Libellé Ligne</label>
                            <input type="text" class="form-control" name="libelles_ligne[]" 
                                   placeholder="Libellé de la ligne">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Débit</label>
                            <input type="number" class="form-control montant debit" name="debits[]" 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Crédit</label>
                            <input type="number" class="form-control montant credit" name="credits[]" 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    ${ligneCount > 1 ? '<button type="button" class="btn btn-sm btn-danger mt-2" onclick="supprimerLigne(this)"><i class="fas fa-trash me-1"></i>Supprimer</button>' : ''}
                </div>
            `;
            document.getElementById('lignesEcriture').insertAdjacentHTML('beforeend', nouvelleLigne);
        }

        function supprimerLigne(btn) {
            btn.closest('.ligne-ecriture').remove();
            calculerTotaux();
        }

        function calculerTotaux() {
            let totalDebit = 0;
            let totalCredit = 0;

            document.querySelectorAll('.debit').forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });

            document.querySelectorAll('.credit').forEach(input => {
                totalCredit += parseFloat(input.value) || 0;
            });

            document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
            document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);

            // Vérifier l'équilibre
            const difference = Math.abs(totalDebit - totalCredit);
            if (difference > 0.01) {
                document.getElementById('totalDebit').parentElement.className = 'alert alert-danger';
                document.getElementById('totalCredit').parentElement.className = 'alert alert-danger';
            } else {
                document.getElementById('totalDebit').parentElement.className = 'alert alert-success';
                document.getElementById('totalCredit').parentElement.className = 'alert alert-success';
            }
        }

        // Événements
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('lignesEcriture').addEventListener('input', function(e) {
                if (e.target.classList.contains('montant')) {
                    calculerTotaux();
                }
            });
        });
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
