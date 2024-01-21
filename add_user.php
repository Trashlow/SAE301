<?php
include 'database.php'; // Assurez-vous que ce fichier contient les informations de connexion à votre base de données.
include 'start_time.php';
session_start();

// Vérifiez si l'utilisateur est un administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo "Veuillez remplir tous les champs.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hachage du mot de passe

        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $username, $hashedPassword);
            if ($stmt->execute()) {
                echo "Utilisateur ajouté avec succès.";
            } else {
                echo "Erreur lors de l'ajout de l'utilisateur: " . $stmt->error;
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
    <title>Ajouter un Utilisateur</title>
    <link rel="stylesheet" type="text/css" href="add_user.css">
</head>
</head>
<body>
    <h1>Ajouter un Utilisateur</h1>
    <form method="post" action="add_user.php">
        Nom d'utilisateur: <input type="text" name="username"><br>
        Mot de passe: <input type="password" name="password"><br>
        <input type="submit" value="Ajouter">
    </form>
</body>
</html>
