<?php
// /var/www/piece_auto/public/modules/creer_commande_vente.php
// Création d'une nouvelle commande de vente avec gestion du panier, calcul de marge et déstockage.

$page_title = "Créer une Commande de Vente";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// Initialisation du panier en session si non existant
if (!isset($_SESSION['panier_vente'])) {
    $_SESSION['panier_vente'] = [];
}
// Client sélectionné en session
$id_client_selectionne = $_SESSION['id_client_vente'] ?? null;

// --- FONCTIONS DE LECTURE ---

function get_clients($db) {
    $query = "SELECT id_client, nom_client, prenom_client FROM CLIENTS ORDER BY nom_client ASC";
    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_pieces_disponibles($db) {
    $query = "SELECT id_piece, reference, nom_piece, prix_vente, stock_actuel, cout_unitaire_moyen_pondere FROM PIECES WHERE stock_actuel > 0 ORDER BY nom_piece ASC";
    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- LOGIQUE DU PANIER ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Sélection/Changement de Client
    if (isset($_POST['selectionner_client'])) {
        $id_client = (int)$_POST['id_client'];
        if ($id_client > 0) {
            $_SESSION['id_client_vente'] = $id_client;
            $id_client_selectionne = $id_client;
        }
    }
    
    // 2. Ajouter une pièce au panier
    if (isset($_POST['ajouter_piece'])) {
        $id_piece = (int)$_POST['id_piece'];
        $quantite = (int)$_POST['quantite_vendue'];

        $query = "SELECT stock_actuel, prix_vente, cout_unitaire_moyen_pondere FROM PIECES WHERE id_piece = :id_piece";
        $stmt = $db->prepare($query);
        $stmt->execute([':id_piece' => $id_piece]);
        $piece_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($piece_info && $quantite > 0) {
            if ($quantite > $piece_info['stock_actuel']) {
                $message = '<div class="alert alert-danger">Stock insuffisant. Stock actuel : ' . $piece_info['stock_actuel'] . '</div>';
            } else {
                
                // Mettre à jour si la pièce est déjà dans le panier
                if (isset($_SESSION['panier_vente'][$id_piece])) {
                    $quantite_totale = $_SESSION['panier_vente'][$id_piece]['quantite'] + $quantite;
                    
                    // Vérification de stock total après ajout
                    if ($quantite_totale > $piece_info['stock_actuel']) {
                         $message = '<div class="alert alert-danger">L\'ajout de cette quantité dépasserait le stock disponible.</div>';
                    } else {
                         $_SESSION['panier_vente'][$id_piece]['quantite'] = $quantite_totale;
                         $message = '<div class="alert alert-success">Quantité de la pièce mise à jour dans le panier.</div>';
                    }

                } else {
                    // Ajout d'une nouvelle pièce
                    $_SESSION['panier_vente'][$id_piece] = [
                        'id_piece' => $id_piece,
                        'quantite' => $quantite,
                        'prix_vente_unitaire' => $piece_info['prix_vente'],
                        'cump_unitaire' => $piece_info['cout_unitaire_moyen_pondere']
                    ];
                    $message = '<div class="alert alert-success">Pièce ajoutée au panier.</div>';
                }
            }
        } else {
            $message = '<div class="alert alert-danger">Veuillez sélectionner une pièce et une quantité valide.</div>';
        }
    }
    
    // 3. Retirer une pièce du panier
    if (isset($_POST['retirer_piece'])) {
        $id_piece_a_retirer = (int)$_POST['id_piece_a_retirer'];
        if (isset($_SESSION['panier_vente'][$id_piece_a_retirer])) {
            unset($_SESSION['panier_vente'][$id_piece_a_retirer]);
            $message = '<div class="alert alert-success">Pièce retirée du panier.</div>';
        }
    }

    // 4. Finaliser la Commande
    if (isset($_POST['finaliser_commande'])) {
        if (!$id_client_selectionne) {
            $message = '<div class="alert alert-danger">Veuillez sélectionner un client avant de finaliser la commande.</div>';
        } elseif (empty($_SESSION['panier_vente'])) {
            $message = '<div class="alert alert-danger">Le panier est vide. Ajoutez des pièces avant de finaliser.</div>';
        } else {
            
            $montant_total = 0;
            $marge_totale = 0;
            $utilisateur_id = $_SESSION['user_id'] ?? null;
            $date_commande = date('Y-m-d H:i:s');

            try {
                $db->beginTransaction();

                // 4.1 Enregistrement de la Commande dans COMMANDE_VENTE (montants initialisés à 0)
                $query_cmd = "INSERT INTO COMMANDE_VENTE (id_client, date_commande, montant_total, marge_totale, utilisateur_id) 
                              VALUES (:id_client, :date_commande, 0, 0, :utilisateur_id)";
                $stmt_cmd = $db->prepare($query_cmd);
                $stmt_cmd->execute([
                    ':id_client' => $id_client_selectionne,
                    ':date_commande' => $date_commande,
                    ':utilisateur_id' => $utilisateur_id
                ]);
                $id_commande_vente = $db->lastInsertId();

                // 4.2 Traitement des Pièces
                foreach ($_SESSION['panier_vente'] as $item) {
                    $id_piece = $item['id_piece'];
                    $quantite = $item['quantite'];
                    $prix_vente_u = $item['prix_vente_unitaire'];
                    $cump_u = $item['cump_unitaire'];
                    
                    $prix_total = $quantite * $prix_vente_u;
                    $cout_total = $quantite * $cump_u;
                    $marge_article = $prix_total - $cout_total;
                    
                    $montant_total += $prix_total;
                    $marge_totale += $marge_article;

                    // 4.2.a Enregistrement dans DETAIL_VENTE
                    $query_det = "INSERT INTO DETAIL_VENTE (id_commande_vente, id_piece, quantite_vendue, prix_vente_unitaire, marge_article) 
                                  VALUES (:id_commande, :id_piece, :quantite, :prix_vente, :marge_article)";
                    $stmt_det = $db->prepare($query_det);
                    $stmt_det->execute([
                        ':id_commande' => $id_commande_vente,
                        ':id_piece' => $id_piece,
                        ':quantite' => $quantite,
                        ':prix_vente' => $prix_vente_u,
                        ':marge_article' => $marge_article
                    ]);

                    // 4.2.b MISE À JOUR DU STOCK (décrémentation) et ENREGISTREMENT DU MOUVEMENT
                    $query_update_stock = "SELECT stock_actuel FROM PIECES WHERE id_piece = :id_piece FOR UPDATE";
                    $stmt_update_stock = $db->prepare($query_update_stock);
                    $stmt_update_stock->execute([':id_piece' => $id_piece]);
                    $ancien_stock = $stmt_update_stock->fetchColumn();
                    $nouveau_stock = $ancien_stock - $quantite;

                    // Décrémentation
                    $query_dec = "UPDATE PIECES SET stock_actuel = :nouveau_stock WHERE id_piece = :id_piece";
                    $stmt_dec = $db->prepare($query_dec);
                    $stmt_dec->execute([':nouveau_stock' => $nouveau_stock, ':id_piece' => $id_piece]);
                    
                    // ENREGISTREMENT DU MOUVEMENT (Sortie Vente)
                    $query_mvt = "
                        INSERT INTO MOUVEMENTS_STOCK 
                        (id_piece, type_mouvement, quantite_impact, stock_avant_mouvement, stock_apres_mouvement, prix_unitaire, source_id, utilisateur_id)
                        VALUES 
                        (:id_piece, 'Sortie Vente', :quantite_impact, :stock_avant, :stock_apres, :prix_unitaire, :source_id, :utilisateur_id)
                    ";
                    $stmt_mvt = $db->prepare($query_mvt);
                    $stmt_mvt->execute([
                        ':id_piece' => $id_piece,
                        ':quantite_impact' => $quantite,
                        ':stock_avant' => $ancien_stock,
                        ':stock_apres' => $nouveau_stock,
                        ':prix_unitaire' => $prix_vente_u, // Le prix_unitaire enregistré est ici le prix de vente
                        ':source_id' => $id_commande_vente,
                        ':utilisateur_id' => $utilisateur_id
                    ]);
                }

                // 4.3 Mise à jour des totaux dans COMMANDE_VENTE
                $query_update_totals = "UPDATE COMMANDE_VENTE SET montant_total = :montant_total, marge_totale = :marge_totale WHERE id_commande_vente = :id_commande_vente";
                $stmt_totals = $db->prepare($query_update_totals);
                $stmt_totals->execute([
                    ':montant_total' => $montant_total,
                    ':marge_totale' => $marge_totale,
                    ':id_commande_vente' => $id_commande_vente
                ]);

                $db->commit();
                
                // Réinitialisation de la session
                $_SESSION['panier_vente'] = [];
                unset($_SION['id_client_vente']);

                $message = '<div class="alert alert-success">Commande #' . $id_commande_vente . ' finalisée avec succès. Montant total: ' . number_format($montant_total, 2) . ' €.</div>';

            } catch (Exception $e) {
                $db->rollBack();
                $message = '<div class="alert alert-danger">Erreur critique lors de la finalisation : ' . $e->getMessage() . '</div>';
            }
        }
    }
}

// Rechargement des données
$clients = get_clients($db);
$pieces_disponibles = get_pieces_disponibles($db);

// Si un client est sélectionné, récupérer son nom
$client_nom_prenom = '';
if ($id_client_selectionne) {
    $query_client = "SELECT nom_client, prenom_client FROM CLIENTS WHERE id_client = :id_client";
    $stmt_client = $db->prepare($query_client);
    $stmt_client->execute([':id_client' => $id_client_selectionne]);
    $client = $stmt_client->fetch(PDO::FETCH_ASSOC);
    if ($client) {
        $client_nom_prenom = htmlspecialchars($client['prenom_client'] . ' ' . $client['nom_client']);
    }
}

// Calcul des totaux du panier
$panier_totals = ['total' => 0, 'cump_total' => 0, 'marge' => 0];
$pieces_dans_panier = [];

if (!empty($_SESSION['panier_vente'])) {
    // Recharger les informations complètes des pièces dans le panier (nom, référence)
    $ids_panier = array_keys($_SESSION['panier_vente']);
    $placeholders = implode(',', array_fill(0, count($ids_panier), '?'));
    
    $query_panier_info = "SELECT id_piece, reference, nom_piece FROM PIECES WHERE id_piece IN ($placeholders)";
    $stmt_panier_info = $db->prepare($query_panier_info);
    $stmt_panier_info->execute($ids_panier);
    $piece_details = $stmt_panier_info->fetchAll(PDO::FETCH_ASSOC);
    $details_map = array_column($piece_details, null, 'id_piece');

    foreach ($_SESSION['panier_vente'] as $id_piece => $item) {
        $details = $details_map[$id_piece];
        $sous_total = $item['quantite'] * $item['prix_vente_unitaire'];
        $sous_total_cout = $item['quantite'] * $item['cump_unitaire'];
        $marge_article = $sous_total - $sous_total_cout;
        
        $panier_totals['total'] += $sous_total;
        $panier_totals['cump_total'] += $sous_total_cout;
        $panier_totals['marge'] += $marge_article;

        $pieces_dans_panier[] = [
            'id_piece' => $id_piece,
            'reference' => htmlspecialchars($details['reference']),
            'nom_piece' => htmlspecialchars($details['nom_piece']),
            'quantite' => $item['quantite'],
            'prix_u' => $item['prix_vente_unitaire'],
            'sous_total' => $sous_total,
            'cump_u' => $item['cump_unitaire'],
            'marge_article' => $marge_article
        ];
    }
}


?>

<h1><i class="fas fa-shopping-cart"></i> <?= $page_title ?></h1>
<p class="lead">Processus de création d'une nouvelle facture client et de déstockage immédiat.</p>
<hr>

<?= $message ?>

<div class="row">
    
    <div class="col-lg-6">
        
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-user-tag"></i> 1. Client
            </div>
            <div class="card-body">
                <?php if ($id_client_selectionne): ?>
                    <p class="alert alert-info">Client actuel : **<?= $client_nom_prenom ?>** (ID: <?= $id_client_selectionne ?>)</p>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="id_client" value="0">
                        <button type="submit" name="selectionner_client" class="btn btn-sm btn-outline-danger">Changer de Client / Annuler</button>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="id_client" class="form-label">Sélectionner un Client <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_client" name="id_client" required>
                                <option value="">Choisir...</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['id_client'] ?>">
                                        <?= htmlspecialchars($c['nom_client'] . ' ' . $c['prenom_client']) ?> (ID: <?= $c['id_client'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="selectionner_client" class="btn btn-primary">Sélectionner</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-tools"></i> 2. Ajouter des Pièces
            </div>
            <div class="card-body">
                <?php if (!$id_client_selectionne): ?>
                    <div class="alert alert-warning">Veuillez d'abord sélectionner un client.</div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="id_piece" class="form-label">Pièce <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_piece" name="id_piece" required>
                                <option value="">Sélectionner une pièce disponible...</option>
                                <?php foreach ($pieces_disponibles as $p): ?>
                                    <option value="<?= $p['id_piece'] ?>">
                                        <?= htmlspecialchars($p['reference']) ?> - <?= htmlspecialchars($p['nom_piece']) ?> (Stock: <?= $p['stock_actuel'] ?>, Prix: <?= number_format($p['prix_vente'], 2) ?> €)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantite_vendue" class="form-label">Quantité Vendue <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" class="form-control" id="quantite_vendue" name="quantite_vendue" required>
                        </div>
                        <button type="submit" name="ajouter_piece" class="btn btn-success"><i class="fas fa-plus"></i> Ajouter au Panier</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
    <div class="col-lg-6">
        <div class="card shadow sticky-top" style="top: 20px;">
            <div class="card-header bg-info text-white">
                <i class="fas fa-shopping-basket"></i> 3. Panier de Commande
            </div>
            <div class="card-body">
                
                <?php if (empty($pieces_dans_panier)): ?>
                    <div class="alert alert-secondary">Le panier est actuellement vide.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Réf.</th>
                                    <th>Qté</th>
                                    <th class="text-end">Prix U.</th>
                                    <th class="text-end">Total</th>
                                    <th>Act.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pieces_dans_panier as $item): ?>
                                    <tr>
                                        <td><?= $item['reference'] ?></td>
                                        <td><?= $item['quantite'] ?></td>
                                        <td class="text-end"><?= number_format($item['prix_u'], 2, ',', ' ') ?> €</td>
                                        <td class="text-end fw-bold"><?= number_format($item['sous_total'], 2, ',', ' ') ?> €</td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id_piece_a_retirer" value="<?= $item['id_piece'] ?>">
                                                <button type="submit" name="retirer_piece" class="btn btn-sm btn-danger p-0 px-1" title="Retirer">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">TOTAL COMMANDE (HT) :</th>
                                    <th class="text-end text-primary fs-5"><?= number_format($panier_totals['total'], 2, ',', ' ') ?> €</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end text-muted">Marge estimée :</th>
                                    <th class="text-end text-muted"><?= number_format($panier_totals['marge'], 2, ',', ' ') ?> €</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" name="finaliser_commande" class="btn btn-success mt-3 w-100 fs-5">
                            <i class="fas fa-check-circle"></i> Finaliser la Commande et Déstocker
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>
