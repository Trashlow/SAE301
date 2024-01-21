<?php
function logUserAction($userId, $action) {
    global $conn; // Assurez-vous que $conn est votre variable de connexion à la base de données
    $sql = "INSERT INTO user_logs (user_id, action) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // Gérer l'erreur de préparation
        die("Erreur de préparation : " . $conn->error);
    }
    $stmt->bind_param("is", $userId, $action);
    if (!$stmt->execute()) {
        // Gérer l'erreur d'exécution
        die("Erreur d'exécution : " . $stmt->error);
    }
    $stmt->close();
}
