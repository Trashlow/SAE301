<?php
include 'database.php';
include 'start_time.php';
session_start();

// Vérifiez si l'utilisateur est un administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.html");
    exit;
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['user_id'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Assumer que le mot de passe peut être changé

    if (empty($userId) || empty($username) || empty($password)) {
        echo "Veuillez remplir tous les champs.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET username = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssi", $username, $hashedPassword, $userId);
            if ($stmt->execute()) {
                echo "Utilisateur modifié avec succès.";
            } else {
                echo "Erreur lors de la modification de l'utilisateur: " . $stmt->error;
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
    <title>Modifier un Utilisateur</title>
    <link rel="stylesheet" href="edit_user.css">
</head>
<body>
    <h1>Modifier un Utilisateur</h1>
    <form method="post" action="edit_user.php">
        ID de l'utilisateur: <input type="number" name="user_id"><br>
        Nouveau nom d'utilisateur: <input type="text" name="username"><br>
        Nouveau mot de passe: <input type="password" name="password"><br>
        <input type="submit" value="Modifier">
    </form>
</body>
</html>
