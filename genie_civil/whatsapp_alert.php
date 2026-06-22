<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "hse_engine.php";

header("Content-Type: text/html; charset=utf-8");

// MESSAGE ERP SIMULÉ
$message = "⚠️ QUALITÉ : Passage gaine non conforme (Majeure)";

// SCORE IA
$budget = 100;
$total = 70;
$delay = 25;

// SAFE CALL IA
$ai = class_exists("OmegaAI")
    ? OmegaAI::riskLevel($budget, $total, $delay)
    : ["level"=>"INFO","score"=>0,"color"=>"green"];

// LOG SYSTEM
$logFile = __DIR__ . "/whatsapp_log.txt";
if (!file_exists($logFile)) file_put_contents($logFile, "");

$history = file_get_contents($logFile);

// ANTI-SPAM
if (strpos($history, $message) !== false) {
    $status = "⛔ DUPLICATE BLOCKED (ANTI-SPAM)";
} else {
    file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND);
    $status = "📲 SENT -> RES_2 | $message";
}

// RISK COLOR ENGINE
$color = "green";
if ($ai["score"] > 70) $color = "orange";
if ($ai["score"] > 85) $color = "red";

// PRIORITÉ CHANTIER
$priority = "NORMAL";
if (stripos($message,"Majeure")!==false) $priority="HIGH";
if (stripos($message,"Critique")!==false) $priority="CRITICAL";

?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>WhatsApp Alert Engine V2.2</title>
</head>

<body class="bg-black text-white flex items-center justify-center h-screen">

<div class="bg-zinc-900 p-10 rounded-2xl border border-zinc-700 w-[650px] text-center shadow-xl">

    <h1 class="text-2xl font-black text-green-400 mb-4">
        📲 WhatsApp Alert Engine V2.2 (AI ERP)
    </h1>

    <!-- SCORE IA -->
    <div class="text-<?= $color ?>-400 font-bold text-xl mb-2">
        RISK SCORE : <?= round($ai["score"],1) ?>% (<?= $ai["level"] ?>)
    </div>

    <!-- PRIORITY -->
    <div class="mb-4 text-xs">
        PRIORITÉ CHANTIER :
        <span class="font-bold text-<?= $priority=="CRITICAL"?"red":($priority=="HIGH"?"orange":"green") ?>-400">
            <?= $priority ?>
        </span>
    </div>

    <!-- MESSAGE -->
    <div class="bg-black border border-zinc-700 p-4 rounded mb-4 text-sm">
        <?= $status ?>
    </div>

    <!-- STATUS BAR -->
    <div class="w-full bg-zinc-800 h-2 rounded mb-6">
        <div class="h-2 bg-<?= $color ?>-500" style="width:<?= $ai["score"] ?>%"></div>
    </div>

    <div class="text-[10px] text-gray-400 mb-6">
        ✔ Anti-spam actif | ✔ Filtrage critique | ✔ IA prédictive chantier | ✔ Mode ERP actif
    </div>

    <a href="index.php"
       class="bg-blue-600 px-6 py-3 rounded font-bold inline-block">
        ⬅ Retour Dashboard
    </a>

</div>

</body>
</html>
