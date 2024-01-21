<?php
include 'database.php';
include 'start_time.php';
include 'log_user_action.php';

$registration_success = false;
$registration_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email']; 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    // Noms d'utilisateur et email réservés pour l'administrateur
    $admin_username = "administrateur";
    $admin_email = "al.masseron@gmail.com";

    // Vérifier si le nom d'utilisateur ou l'email est déjà pris ou s'il est réservé pour l'administrateur
    $user_check_query = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt = $conn->prepare($user_check_query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (strtolower($username) === strtolower($user['username'])) {
            $registration_message = "Ce nom d'utilisateur est déjà pris. Veuillez en choisir un autre.";
        } elseif (strtolower($email) === strtolower($user['email'])) {
            $registration_message = "Cette adresse email est déjà utilisée. Veuillez en utiliser une autre.";
        }
    } elseif (strtolower($username) === strtolower($admin_username) || strtolower($email) === strtolower($admin_email)) {
        if (strtolower($username) === strtolower($admin_username)) {
            $registration_message = "Ce nom d'utilisateur est réservé. Veuillez en choisir un autre.";
        } else {
            $registration_message = "Cette adresse email est réservée. Veuillez en utiliser une autre.";
        }
    } else {
        // Le nom d'utilisateur et l'email sont disponibles, continuez avec l'inscription
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            logUserAction($conn->insert_id, 'Inscription de l\'utilisateur.');
            $registration_success = true;
            $registration_message = "Inscription réussie. Vous allez être redirigé vers la page de connexion.";
            header("Refresh: 3; url=login.html");
        } else {
            $registration_message = "Erreur lors de l'inscription : " . $conn->error;
        }
        $stmt->close();
    }
    $conn->close();
}

include 'end_time.php';

if ($registration_success) {
    echo $registration_message;
    exit;
} else {
    // Affichez le message d'erreur si l'inscription n'est pas réussie
    echo $registration_message;
}
?>
