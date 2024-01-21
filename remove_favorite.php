<?php
include 'database.php';
include 'log_user_action.php';
include 'start_time.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$favorite_id = $_GET['favorite_id'] ?? null;

// Valider favorite_id
if (!is_numeric($favorite_id)) {
    echo "ID de favori invalide.";
    exit;
}

// Préparer la requête SQL pour supprimer le favori
$sql = "DELETE FROM favorites WHERE user_id = ? AND favorite_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Erreur de préparation de la requête : " . $conn->error;
    exit;
}

$stmt->bind_param("ii", $user_id, $favorite_id);

if ($stmt->execute()) {
    echo "Favori supprimé avec succès. Redirection vers votre profil...";
    // Redirection avec un délai d'une seconde en utilisant JavaScript
    echo "<script>setTimeout(function(){ window.location.href = 'user_profile.php'; }, 1000);</script>";
} else {
    echo "Erreur lors de la suppression du favori : " . $stmt->error;
}

$stmt->close();
$conn->close();
include 'end_time.php';
?>
