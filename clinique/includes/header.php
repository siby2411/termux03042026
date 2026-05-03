<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: /login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Oméga Clinique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/modules/dashboard.php"><i class="bi bi-hospital"></i> Oméga</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/modules/dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="/modules/patients/list.php">Patients</a></li>
                <li class="nav-item"><a class="nav-link" href="/modules/rendezvous/list.php">Rendez-vous</a></li>
                <li class="nav-item"><a class="nav-link" href="/modules/finances/list.php">Finances</a></li>
                <li class="nav-item"><a class="nav-link" href="/modules/personnel/list.php">Personnel</a></li>
            </ul>
            <span class="navbar-text text-white me-3">
                <i class="bi bi-person-circle"></i> <?= $_SESSION['user_nom'] ?? 'Utilisateur' ?>
            </span>
            <a href="/logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>
<div class="container-fluid px-4">
