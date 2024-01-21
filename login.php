<?php
include 'database.php';
include 'start_time.php';
session_start();

// Fonction pour envoyer un e-mail à l'administrateur lors de la connexion
function sendAdminLoginEmail($email, $username) {
    $subject = "Connexion Admin détectée";
    $message = "Bonjour $username,\n\nUne connexion à l'interface d'administration a été effectuée.";
    $headers = "From: no-reply@votresite.com";
    mail($email, $subject, $message, $headers);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT user_id, username, password, email, is_admin FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erreur de préparation : " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['is_admin'] = $row['is_admin'];

            // Envoyer un e-mail à l'administrateur lors de la connexion
            if ($row['is_admin'] == 1) {
                sendAdminLoginEmail($row['email'], $username);
            }

            // Rediriger vers la page appropriée
            $redirectUrl = $row['is_admin'] == 1 ? 'admin_dashboard.php' : 'index.php';
            header("Location: $redirectUrl");
            exit();
        } else {
            $login_error = "Mot de passe incorrect.";
        }
    } else {
        $login_error = "Nom d'utilisateur introuvable.";
    }

    $stmt->close();
    $conn->close();
}
include 'end_time.php';
?>