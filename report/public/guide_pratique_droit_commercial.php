<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Guide pratique – Droit commercial & comptabilité";
include 'inc_navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .card-term { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 15px; }
        .card-term:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .term-title { font-weight: bold; font-size: 1rem; }
        .table-fusion { font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-briefcase"></i> Guide pratique – Droit commercial & comptabilité</h2>
                    <p>Pour les professionnels de la comptabilité, les développeurs ERP et les experts financiers</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Ce guide couvre les notions essentielles de droit commercial appliquées à la comptabilité et à la finance. Conforme aux principes SYSCOHADA / OHADA.
                    </div>

                    <!-- ==================== SECTION 1 : STRUCTURE JURIDIQUE ==================== -->
                    <h4 class="section-title"><i class="bi bi-building"></i> 1. Structure juridique & acteurs</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Personnalité morale</div><div class="term-def">Attribut juridique permettant à une société d'exister distinctement de ses associés. Indispensable pour une comptabilité autonome.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Responsabilité limitée</div><div class="term-def">L'associé n'est responsable des dettes sociales qu'à hauteur de ses apports (protection du patrimoine personnel).</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Capital social</div><div class="term-def">Montant total des apports des associés. C'est le gage des créanciers.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Apport en numéraire / en nature</div><div class="term-def">Somme d'argent ou bien (immobilier, brevet) apporté en échange de parts.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">AGO (Assemblée Générale Ordinaire)</div><div class="term-def">Approbation des comptes annuels, affectation du résultat, quitus aux dirigeants.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">AGE (Assemblée Générale Extraordinaire)</div><div class="term-def">Modification des statuts (changement de capital, fusion, liquidation).</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Statuts</div><div class="term-def">"Contrat de mariage" de la société. Fixe capital, règles d'organisation, pouvoirs des dirigeants.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Conventions réglementées</div><div class="term-def">Contrats entre société et dirigeants/associés. Approbation obligatoire pour éviter conflits d'intérêts.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Droit de communication</div><div class="term-def">Droit de l'actionnaire d'accéder à la comptabilité et aux comptes annuels avant les assemblées.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 2 : ACTES DE COMMERCE & CONTRATS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-file-text"></i> 2. Actes de commerce & contrats</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Acte de commerce</div><div class="term-def">Opération accomplie par un commerçant pour son entreprise (achat pour revente, opérations bancaires).</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Fonds de commerce</div><div class="term-def">Ensemble d'éléments incorporels (clientèle, nom, droit au bail) et corporels.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Clause de réserve de propriété</div><div class="term-def">Le vendeur reste propriétaire jusqu'au paiement intégral. Crucial pour la gestion des stocks en cas de faillite.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Nantissement</div><div class="term-def">Garantie sur un bien incorporel (fonds de commerce) sans dépossession.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Bail commercial</div><div class="term-def">Location de local commercial. Forte protection du locataire (droit au renouvellement).</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Solidarité</div><div class="term-def">Plusieurs débiteurs peuvent être poursuivis pour la totalité d'une dette.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 3 : DROIT DE LA DÉFAILLANCE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-exclamation-triangle"></i> 3. Droit de la défaillance & procédures collectives</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Cessation des paiements</div><div class="term-def">Passif exigible > actif disponible. Déclenche les procédures collectives.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Sauvegarde</div><div class="term-def">Procédure ouverte avant cessation des paiements, pour prévenir les difficultés.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Redressement judiciaire</div><div class="term-def">Poursuite de l'activité, maintien de l'emploi et apurement du passif.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Liquidation judiciaire</div><div class="term-def">Fin de l'activité, vente des actifs pour désintéresser les créanciers.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Ordre des créanciers (privilèges)</div><div class="term-def">Salariés > Trésor public > créanciers garantis > créanciers chirographaires.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Action en comblement de passif</div><div class="term-def">Le dirigeant peut être condamné à payer les dettes sur ses fonds personnels en cas de faute de gestion.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 4 : SÛRETÉS ET GARANTIES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-shield-lock"></i> 4. Sûretés et garanties (socle du crédit)</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Hypothèque</div><div class="term-def">Garantie sur un bien immobilier. Le créancier peut faire vendre l'immeuble.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Gage</div><div class="term-def">Garantie sur un bien corporel (stocks, outillage).</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Cautionnement</div><div class="term-def">Une personne s'engage à payer la dette du débiteur principal.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Avance en compte courant</div><div class="term-def">Prêt d'un associé à sa société (créance soumise à règles strictes).</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 5 : FUSIONS & ACQUISITIONS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-arrow-left-right"></i> 5. Fusions & acquisitions (M&A)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-term"><div class="card-body"><div class="term-title">Fusion-absorption</div><div class="term-def">La société A absorbe B. B disparaît, ses actionnaires deviennent actionnaires de A. Transmission Universelle de Patrimoine (TUP).</div></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term"><div class="card-body"><div class="term-title">Fusion-création</div><div class="term-def">A et B disparaissent pour créer une nouvelle société C.</div></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term"><div class="card-body"><div class="term-title">Acquisition de titres</div><div class="term-def">Achat des actions de B. B devient filiale, indépendance juridique conservée.</div></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term"><div class="card-body"><div class="term-title">Acquisition d'actifs (Asset deal)</div><div class="term-def">Achat direct des actifs de B (fonds, stocks). Taxation immédiate des plus-values.</div></div></div>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-fusion">
                            <thead class="table-dark"><tr><th>Caractéristique</th><th>Fusion</th><th>Acquisition (de titres)</th></tr></thead>
                            <tbody>
                                <tr><td>Entité juridique</td><td>Une seule entité finale</td><td>Deux entités distinctes</td></tr>
                                <tr><td>Complexité juridique</td><td>Très élevée (TUP)</td><td>Modérée</td></tr>
                                <tr><td>Fiscalité</td><td>Régime de faveur (neutralité)</td><td>Taxation des plus-values</td></tr>
                                <tr><td>Patrimoine</td><td>Fusionné totalement</td><td>Séparé</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 6 : COMPTABILISATION DES OPÉRATIONS JURIDIQUES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 6. Comptabilisation des opérations juridiques (SYSCOHADA)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-term"><div class="card-body"><div class="term-title">Frais de constitution</div>
                            <div class="term-def">Débit : 2011 - Frais de constitution (actif immobilisé)<br>Crédit : 401 - Fournisseurs ou 521 - Banque<br><br>Amortissement : 681 / 2811</div></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term"><div class="card-body"><div class="term-title">Compte courant d'associé (CCA)</div>
                            <div class="term-def">Constatation dette : 521 - Banque / 467 - Associés comptes courants<br><br>Remboursement : 467 / 521<br><span class="text-danger">⚠️ Attention : le remboursement doit être justifié par une trésorerie disponible suffisante (risque de requalification en faute de gestion).</span></div></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term"><div class="card-body"><div class="term-title">Goodwill (écart d'acquisition)</div><div class="term-def">Si prix d'acquisition > ANCC : la différence s'enregistre à l'actif en Goodwill. Test de dépréciation annuel.</div></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term"><div class="card-body"><div class="term-title">Badwill (écart négatif)</div><div class="term-def">Si prix d'acquisition < ANCC : la différence est enregistrée en produits (résultat exceptionnel).</div></div></div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 7 : LITIGES ET VOIES DE RECOURS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-gavel"></i> 7. Litiges et voies de recours</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Injonction de payer</div><div class="term-def">Procédure simplifiée pour recouvrer une créance certaine, liquide et exigible.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Requête</div><div class="term-def">Demande adressée au juge sans informer l'autre partie (saisie conservatoire).</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Assignation</div><div class="term-def">Acte informant l'adversaire qu'un procès est engagé.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Transaction</div><div class="term-def">Contrat mettant fin à un litige par concessions réciproques.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Prescription commerciale</div><div class="term-def">Délai au-delà duquel une action en justice n'est plus recevable (5 ans pour les obligations commerciales).</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Expertise de gestion</div><div class="term-def">Procédure permettant aux actionnaires minoritaires de demander un audit judiciaire.</div></div></div></div>
                    </div>

                    <!-- ==================== SECTION 8 : RESPONSABILITÉ DES DIRIGEANTS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-person-badge"></i> 8. Responsabilité des dirigeants</h4>
                    <div class="row">
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Faute de gestion</div><div class="term-def">Erreur professionnelle engageant la responsabilité civile ou pénale du dirigeant.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Décharge de responsabilité</div><div class="term-def">Vote des actionnaires approuvant la gestion pour l'exercice écoulé.</div></div></div></div>
                        <div class="col-md-4"><div class="card card-term"><div class="card-body"><div class="term-title">Liquidation amiable</div><div class="term-def">Fin de société après paiement des dettes. Le boni est partagé entre associés.</div></div></div></div>
                    </div>

                    <!-- ==================== CONSEILS POUR ERP ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-code-square"></i> 7. Conseils pour développeurs ERP</h4>
                    <div class="alert alert-secondary">
                        <ul>
                            <li><strong>Traçabilité des décisions :</strong> Chaque convention réglementée doit être liée à une référence de PV d'Assemblée Générale.</li>
                            <li><strong>Gestion des droits d'accès (RBAC) :</strong> Isoler les informations relatives au droit de communication des actionnaires.</li>
                            <li><strong>Alertes "Cessation des paiements" :</strong> Créer des indicateurs comparant actif disponible et dettes à court terme.</li>
                            <li><strong>Gestion des clauses :</strong> Ajouter des zones de texte structurées pour les clauses de réserve de propriété sur les factures.</li>
                            <li><strong>Gestion des statuts :</strong> Champ obligatoire pour la forme juridique (SA, SARL, SAS) car elle dicte les règles d'approbation des comptes.</li>
                        </ul>
                    </div>

                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle"></i> Ce guide pratique couvre les notions essentielles de droit commercial appliquées à la comptabilité. Utilisez-le comme référence pour vos projets ERP, audits et conseils juridiques.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
