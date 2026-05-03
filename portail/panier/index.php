<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

echo "<!DOCTYPE html><html><head><title>Panier</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'></head><body>";
require_once '../includes/menu.php';
echo "<div class='container mt-4'><h2>Mon panier</h2><p class='alert alert-info'>Fonctionnalité en développement</p><a href='../index.php' class='btn btn-secondary'>Retour</a></div></body></html>";
