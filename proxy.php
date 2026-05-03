<?php
// proxy.php - Proxy universel pour toutes les applications
$apps = [
    // ========== PÔLE FINANCE & STRATÉGIE ==========
    'ingenierie'            => 8094,
    'banque'                => 8095,
    'syscohada'             => 8096,
    'syscoa'                => 8097,
    'gestion_previsionnelle'=> 8098,

    // ========== PÔLE GESTION COMMERCIALE & PME ==========
    'pme'                   => 8100,
    'gestion_commerciale'   => 8101,
    'ecommerce'             => 8102,
    'gestion_ecommerciale'  => 8103,
    'restau'                => 8104,

    // ========== PÔLE AUTOMOBILE ==========
    'auto'                  => 8110,
    'gestion_auto'          => 8111,
    'piece_auto'            => 8112,

    // ========== PÔLE SERVICES & RH ==========
    'gestion_pointage'      => 8093,
    'gestion_ecole'         => 8091,
    'pressing'              => 8092,
    'clinique'              => 8120,

    // ========== PÔLE ANALYSE & SYNTHÈSE ==========
    'report'                => 8130,
    'reporting'             => 8131,
    'synthesepro'           => 8132,

    // ========== PREMIERS NOUVEAUX SERVICES ==========
    'centrediop'            => 8140,
    'charcuterie1'          => 8141,
    'foot'                  => 8142,
    'librairie'             => 8143,
    'pharmacie'             => 8144,
    'revendeur_medical'     => 8145,

    // ========== DEUXIÈME VAGUE ==========
    'analyse_medicale'      => 8150,
    'hotel'                 => 8151,
    'cabinet_radiologie'    => 8153,
    'gestion_immobiliere'   => 8154,

    // ========== TROISIÈME VAGUE ==========
    'portail'               => 8152,
    'couture_senegal'       => 8155,
    'genie_civil'           => 8156,
    'transit'               => 8157,
    'agence_voyage'         => 8158,
    'annuaire'              => 8159,

    // ========== QUATRIÈME VAGUE - NOUVELLES APPLICATIONS ==========
    'fitness'               => 8160,
    'pizzeria'              => 8161,
    'scooter'               => 8162,
    'parfumerie'            => 8163,
];

$app = $_GET['app'] ?? '';
if (!isset($apps[$app])) {
    http_response_code(404);
    die('Application non trouvée');
}

$port = $apps[$app];
$url = "http://localhost:$port" . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo "Erreur $httpCode";
    exit;
}

if (strpos($response, '<head') !== false) {
    $base = '<base href="' . htmlspecialchars($url) . '">';
    $response = preg_replace('/<head>/i', '<head>' . $base, $response, 1);
}
echo $response;
?>
