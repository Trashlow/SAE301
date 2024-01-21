<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturo";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}
?>
