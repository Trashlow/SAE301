<?php
include 'database.php';
include 'start_time.php';
session_start();

// Vérifiez si l'utilisateur est un administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.html");
    exit;
}

// Traitement de la suppression
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['user_id'];

    if (empty($userId)) {
        echo "Veuillez spécifier un ID utilisateur.";
    } else {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                echo "Utilisateur supprimé avec succès.";
            } else {
                echo "Erreur lors de la suppression de l'utilisateur: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erreur lors de la préparation de la requête: " . $conn->error;
        }
    }
}

$conn->close();
include 'end_time.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer un Utilisateur</title>
    <link rel="stylesheet" href="delete_user.css">
</head>
</head>
<body>
    <h1>Supprimer un Utilisateur</h1>
    <form method="post" action="delete_user.php">
        ID de l'utilisateur à supprimer: <input type="number" name="user_id"><br>
        <input type="submit" value="Supprimer">
    </form>
</body>
</html>
