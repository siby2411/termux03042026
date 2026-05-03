<?php
include_once "header.php";

$conn = mysqli_connect("127.0.0.1", "root", "", "blog") or die("Impossible de se connecter à la base de données");

if (mysqli_connect_errno())
{
  echo "Echec de la connexion à MySQL: " . mysqli_connect_error();
}
?>