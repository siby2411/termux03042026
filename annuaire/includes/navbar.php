<?php
// Détection de la page active
$current = basename($_SERVER['PHP_SELF']);
function nav_active(string $page, string $current): string {
    return $current === $page ? ' active' : '';
}
?>
<style>
    .nav-container {
        background: #002855;
        padding: 0 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        margin-bottom: 0;
        position: sticky;
        top: 0;
        z-index: 100;
        height: 56px;
    }
    .nav-brand {
        color: white;
        font-family: 'DM Serif Display', 'Georgia', serif;
        font-size: 1.05rem;
        font-weight: 400;
        letter-spacing: 0.03em;
        text-decoration: none;
        flex-shrink: 0;
    }
    .nav-brand span {
        color: #5bb3ff;
    }
    .nav-links {
        display: flex;
        align-items: center;
        gap: 2px;
    }
    .nav-links a {
        color: #a8bcd8;
        text-decoration: none;
        padding: 8px 14px;
        font-size: 0.82rem;
        font-weight: 500;
        border-radius: 7px;
        transition: background 0.18s, color 0.18s;
        white-space: nowrap;
    }
    .nav-links a:hover {
        background: rgba(255,255,255,0.08);
        color: white;
    }
    .nav-links a.active {
        background: rgba(91,179,255,0.15);
        color: #5bb3ff;
    }
    .nav-links a.nav-new {
        background: rgba(52,201,122,0.12);
        color: #34c97a;
        border: 1px solid rgba(52,201,122,0.25);
    }
    .nav-links a.nav-new:hover {
        background: rgba(52,201,122,0.22);
        color: #5fe89a;
        border-color: rgba(52,201,122,0.45);
    }
    .nav-links a.nav-new.active {
        background: rgba(52,201,122,0.25);
        color: #5fe89a;
    }
    .nav-links a.nav-export {
        color: #2ecc71;
    }
    .nav-links a.nav-export:hover {
        background: rgba(46,204,113,0.1);
        color: #58e28d;
    }
    .nav-divider {
        width: 1px;
        height: 20px;
        background: rgba(255,255,255,0.1);
        margin: 0 6px;
    }
    .nav-user {
        color: #a8bcd8;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .nav-user span { color: #5bb3ff; }
</style>

<nav class="nav-container">
    <a href="index.php" class="nav-brand">OMEGA <span>CONSULTING</span></a>

    <div class="nav-links">
        <a href="index.php" class="<?= nav_active('index.php', $current) ?>">
            Générateur d'Offres
        </a>
        <a href="liste_partenaires.php" class="<?= nav_active('liste_partenaires.php', $current) ?>">
            Annuaire Médical
        </a>
        <a href="ajouter_partenaire.php" class="nav-new<?= nav_active('ajouter_partenaire.php', $current) ?>">
            + Nouveau Partenaire
        </a>
        <div class="nav-divider"></div>
        <a href="export_excel.php" class="nav-export<?= nav_active('export_excel.php', $current) ?>">
            ↓ Export Excel
        </a>
    </div>

    <div class="nav-user">
        <span>M. SIBY</span> &nbsp;·&nbsp; 2026
    </div>
</nav>
