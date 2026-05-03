<?php
require('config/db.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom               = trim($_POST['nom'] ?? '');
    $specialite        = trim($_POST['specialite'] ?? '');
    $adresse           = trim($_POST['adresse'] ?? '');
    $telephone         = trim($_POST['telephone'] ?? '');
    $zone_geographique = trim($_POST['zone_geographique'] ?? '');
    $categorie         = $_POST['categorie'] ?? '';

    $categories_valides = ['URGENCE', 'CLINIQUE', 'DENTAIRE', 'PHARMACIE'];

    if (empty($nom) || empty($categorie) || !in_array($categorie, $categories_valides)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO annuaire_medical (nom, specialite, adresse, telephone, zone_geographique, categorie)
                VALUES (:nom, :specialite, :adresse, :telephone, :zone_geographique, :categorie)
            ");
            $stmt->execute([
                ':nom'               => $nom,
                ':specialite'        => $specialite,
                ':adresse'           => $adresse,
                ':telephone'         => $telephone,
                ':zone_geographique' => $zone_geographique,
                ':categorie'         => $categorie,
            ]);
            $success = 'Le partenaire <strong>' . htmlspecialchars($nom) . '</strong> a été ajouté avec succès.';
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'insertion : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA – Nouveau Partenaire</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:   #002855;
            --navy2:  #003f7f;
            --accent: #0077cc;
            --light:  #f4f7fb;
            --border: #dde3ee;
            --text:   #1a2740;
            --muted:  #6b7a99;
            --success-bg: #edfaf3;
            --success-border: #34c97a;
            --success-text: #1a7045;
            --error-bg: #fff0f0;
            --error-border: #e05c5c;
            --error-text: #8b1a1a;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--light);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        <?php include 'includes/navbar_styles.css.php'; ?>

        /* ── PAGE ── */
        .page-wrapper {
            max-width: 680px;
            margin: 40px auto 60px;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-header .eyebrow {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 6px;
        }

        .page-header h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 2rem;
            color: var(--navy);
            line-height: 1.15;
        }

        .page-header p {
            margin-top: 8px;
            font-size: 0.88rem;
            color: var(--muted);
            font-weight: 400;
        }

        /* ── CARD ── */
        .card {
            background: white;
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 24px rgba(0,40,85,0.07);
            overflow: hidden;
        }

        .card-section {
            padding: 28px 32px;
            border-bottom: 1px solid var(--border);
        }

        .card-section:last-child { border-bottom: none; }

        .section-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 20px;
        }

        /* ── FORM ── */
        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .field-row.full { grid-template-columns: 1fr; }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text);
        }

        label .req {
            color: var(--accent);
            margin-left: 2px;
        }

        input[type="text"],
        input[type="tel"],
        textarea,
        select {
            width: 100%;
            padding: 10px 13px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            color: var(--text);
            background: #fafbfd;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
            -webkit-appearance: none;
        }

        input[type="text"]:focus,
        input[type="tel"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0,119,204,0.12);
            background: white;
        }

        textarea { resize: vertical; min-height: 80px; }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7a99' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 13px center;
            padding-right: 36px;
            cursor: pointer;
        }

        /* ── CATEGORIE PILLS ── */
        .cat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .cat-pill input[type="radio"] { display: none; }

        .cat-pill label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 14px 10px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: #fafbfd;
            cursor: pointer;
            transition: all 0.18s;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--muted);
        }

        .cat-pill .icon { font-size: 1.4rem; }

        .cat-pill input[type="radio"]:checked + label {
            border-color: var(--accent);
            background: #eef5fd;
            color: var(--navy);
            box-shadow: 0 0 0 3px rgba(0,119,204,0.1);
        }

        .cat-pill label:hover {
            border-color: #99bde0;
            background: white;
        }

        /* ── SUBMIT ── */
        .card-footer {
            padding: 20px 32px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .btn-secondary {
            padding: 10px 20px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--muted);
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            text-decoration: none;
            transition: 0.2s;
            cursor: pointer;
        }

        .btn-secondary:hover { color: var(--navy); border-color: #b0bfd8; }

        .btn-primary {
            padding: 11px 28px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            color: white;
            background: var(--navy);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover { background: var(--navy2); }
        .btn-primary:active { transform: scale(0.98); }

        /* ── ALERTS ── */
        .alert {
            padding: 14px 18px;
            border-radius: 9px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 22px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.5;
        }

        .alert-success {
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            color: var(--success-text);
        }

        .alert-error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
        }

        .alert-icon { flex-shrink: 0; font-size: 1rem; margin-top: 1px; }

        @media (max-width: 560px) {
            .field-row { grid-template-columns: 1fr; }
            .cat-grid { grid-template-columns: repeat(2, 1fr); }
            .card-section { padding: 22px 20px; }
            .card-footer { flex-direction: column-reverse; }
            .btn-primary, .btn-secondary { width: 100%; justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">

    <div class="page-header">
        <div class="eyebrow">Annuaire Médical</div>
        <h1>Nouveau Partenaire</h1>
        <p>Renseignez les informations de l'établissement à référencer dans l'annuaire.</p>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <span class="alert-icon">✓</span>
        <span><?= $success ?> &nbsp;<a href="liste_partenaires.php" style="color:inherit;text-decoration:underline;">Voir l'annuaire →</a></span>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error">
        <span class="alert-icon">!</span>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="ajouter_partenaire.php" novalidate>
        <div class="card">

            <!-- SECTION 1 : Identité -->
            <div class="card-section">
                <div class="section-label">Identité</div>
                <div style="display:flex; flex-direction:column; gap:16px;">

                    <div class="field-row full">
                        <div class="field">
                            <label for="nom">Nom de l'établissement <span class="req">*</span></label>
                            <input type="text" id="nom" name="nom"
                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                                   placeholder="Ex : Clinique du Cap-Vert"
                                   required>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="specialite">Spécialité / Service</label>
                            <input type="text" id="specialite" name="specialite"
                                   value="<?= htmlspecialchars($_POST['specialite'] ?? '') ?>"
                                   placeholder="Ex : Cardiologie, Pédiatrie…">
                        </div>
                        <div class="field">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone"
                                   value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"
                                   placeholder="Ex : +221 77 000 00 00">
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECTION 2 : Localisation -->
            <div class="card-section">
                <div class="section-label">Localisation</div>
                <div style="display:flex; flex-direction:column; gap:16px;">

                    <div class="field-row full">
                        <div class="field">
                            <label for="adresse">Adresse complète</label>
                            <textarea id="adresse" name="adresse"
                                      placeholder="Ex : Avenue Cheikh Anta Diop, Dakar"><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="field-row full">
                        <div class="field">
                            <label for="zone_geographique">Zone géographique</label>
                            <input type="text" id="zone_geographique" name="zone_geographique"
                                   value="<?= htmlspecialchars($_POST['zone_geographique'] ?? '') ?>"
                                   placeholder="Ex : Dakar, Thiès, Saint-Louis…">
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECTION 3 : Catégorie -->
            <div class="card-section">
                <div class="section-label">Catégorie <span style="color:var(--accent)">*</span></div>
                <div class="cat-grid">
                    <?php
                    $cats = [
                        'URGENCE'  => ['icon' => '🚨', 'label' => 'Urgence'],
                        'CLINIQUE' => ['icon' => '🏥', 'label' => 'Clinique'],
                        'DENTAIRE' => ['icon' => '🦷', 'label' => 'Dentaire'],
                        'PHARMACIE'=> ['icon' => '💊', 'label' => 'Pharmacie'],
                    ];
                    $selected_cat = $_POST['categorie'] ?? '';
                    foreach ($cats as $val => $info):
                        $checked = ($selected_cat === $val) ? 'checked' : '';
                    ?>
                    <div class="cat-pill">
                        <input type="radio" id="cat_<?= $val ?>" name="categorie" value="<?= $val ?>" <?= $checked ?> required>
                        <label for="cat_<?= $val ?>">
                            <span class="icon"><?= $info['icon'] ?></span>
                            <?= $info['label'] ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="card-footer">
                <a href="liste_partenaires.php" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Enregistrer le partenaire
                </button>
            </div>

        </div><!-- /card -->
    </form>

</div><!-- /page-wrapper -->

</body>
</html>
