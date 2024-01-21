<?php
include 'database.php';

$end_time = microtime(true);
$response_time = $end_time - $start_time;

// Enregistrer le temps de réponse dans la base de données
$sql = "INSERT INTO response_times (response_time) VALUES (?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erreur de préparation : " . $conn->error);
}
$stmt->bind_param("d", $response_time);
if (!$stmt->execute()) {
    die("Erreur d'exécution : " . $stmt->error);
}
$stmt->close();
