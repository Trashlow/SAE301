<?php
include 'database.php';
session_start();

// Vérifiez si l'utilisateur est un administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.html");
    exit;
}

$message_sent = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $recipient = $_POST['recipient']; // Peut être une adresse email spécifique ou 'all' pour tous les utilisateurs

    $headers = "From: al.masseron@gmail.com\r\n";
    $headers .= "Reply-To: al.masseron@gmail.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if ($recipient === 'all') {
        // Récupérer toutes les adresses email des utilisateurs
        $users = $conn->query("SELECT email FROM users WHERE email IS NOT NULL");

        while ($user = $users->fetch_assoc()) {
            // Envoi de l'email à chaque utilisateur
            mail($user['email'], $subject, $body, $headers);
        }

        $message_sent = true;
    } else {
        // Envoi de l'email à un utilisateur spécifique
        mail($recipient, $subject, $body, $headers);
        $message_sent = true;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contacter les utilisateurs</title>
    <link rel="stylesheet" href="settings.css">

</head>
<body>
    <h1>Envoyer un Email</h1>

    <?php if ($message_sent): ?>
        <p>Message envoyé avec succès.</p>
    <?php endif; ?>

    <form method="post" action="settings.php">
        Sujet : <input type="text" name="subject" required><br>
        Corps du message : <textarea name="body" required></textarea><br>
        Destinataire : <input type="email" name="recipient"> (Laisser vide pour un envoie grouper)<br>
        <input type="submit" value="Envoyer">
    </form>
</body>
</html>
