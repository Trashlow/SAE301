<?php
session_start();
include 'database.php';
include 'log_user_action.php';
include 'start_time.php';

// Vérifier si un utilisateur est connecté et s'il s'agit d'un administrateur
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Enregistrer l'action de déconnexion en fonction du type d'utilisateur
if ($userId) {
    $action = $isAdmin ? 'Déconnexion de l\'administrateur' : 'Déconnexion de l\'utilisateur';
    logUserAction($userId, $action);
}
include 'end_time.php';

// Détruire la session
session_destroy();

// Rediriger l'utilisateur vers la page de connexion
header("Location: login.html");
exit;
?>
