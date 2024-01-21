<?php
include 'database.php'; // Assurez-vous que ce fichier contient les informations de connexion à votre base de données

function createAdminUser($username, $password) {
    global $conn;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hachage du mot de passe
    $isAdmin = 1; // 1 signifie que l'utilisateur est un admin

    $sql = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    $stmt->bind_param("ssi", $username, $hashedPassword, $isAdmin);

    if ($stmt->execute()) {
        echo "Utilisateur admin 'administrateur' créé avec succès.";
    } else {
        echo "Erreur lors de la création de l'utilisateur admin : " . $stmt->error;
    }

    $stmt->close();
}

// Créer un utilisateur admin avec le nom 'administrateur' et le mot de passe 'jefe'
createAdminUser("administrateur", "jefe");

$conn->close();
?>
