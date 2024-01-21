<?php
include 'database.php'; // Assurez-vous que ce fichier existe

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];

    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Redirection ou message de confirmation
        header("Location: admin_dashboard.php"); // Redirection vers le tableau de bord
    } else {
        echo "Erreur lors de la suppression : " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
