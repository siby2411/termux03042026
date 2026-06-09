<?php
// Bypass Login automatique pour accès direct
session_start();
$_SESSION['user_role'] = 'admin'; 
$_SESSION['user_name'] = 'Expert OMEGA';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA ÉCOLE - Gestion Académique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --omega-dark: #1a237e; --omega-gold: #ffd700; --omega-grad: linear-gradient(135deg, #1a237e 0%, #283593 100%); }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .omega-banner { 
            background: var(--omega-grad); color: white; padding: 30px; 
            border-bottom: 5px solid var(--omega-gold); box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            margin-bottom: 40px;
        }
        .omega-card { border: none; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); background: white; }
        .btn-omega { background: var(--omega-grad); color: white; border-radius: 30px; padding: 10px 25px; font-weight: 600; border: none; }
        .btn-omega:hover { transform: translateY(-2px); color: var(--omega-gold); }
        .form-centered { max-width: 1000px; margin: 0 auto; }
        .table-elite thead { background-color: var(--omega-dark); color: white; }
    </style>
</head>
<body>

<div class="omega-banner text-center">
    <h1 class="fw-bold"><i class="bi bi-mortarboard-fill"></i> OMEGA INFORMATIQUE CONSULTING</h1>
    <p class="lead">Solution Intégrée de Gestion Scolaire & Académique - UEMOA 2026</p>
</div>

<div class="container pb-5">
