<?php
// layout.php - Le cœur visuel d'OMEGA
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'OMEGA CONSULTING' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --omega-blue: #0d47a1; --omega-gold: #ffc107; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .banner-omega {
            background: linear-gradient(45deg, #0d47a1, #1976d2);
            color: white;
            padding: 20px;
            border-bottom: 5px solid var(--omega-gold);
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        .card-omega { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-omega { background-color: var(--omega-blue); color: white; border-radius: 25px; padding: 10px 25px; }
        .btn-omega:hover { background-color: #002171; color: white; }
        .form-centered { max-width: 900px; margin: 0 auto; }
        .table-omega thead { background-color: var(--omega-blue); color: white; }
    </style>
</head>
<body>

<div class="banner-omega text-center">
    <h2 class="mb-0"><i class="bi bi-shield-check"></i> OMEGA INFORMATIQUE CONSULTING</h2>
    <small>Expertise Comptable & Solutions Digitales - Standard SYSCOHADA 2026</small>
</div>

<div class="container pb-5">
