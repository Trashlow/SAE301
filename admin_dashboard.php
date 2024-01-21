<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'database.php'; // Assurez-vous que ce fichier existe et contient les informations de connexion à votre base de données

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.html");
    exit;
}

// Pagination pour les logs
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$logsPerPage = 10;
$offset = ($page - 1) * $logsPerPage;

// Récupérer les logs pour la page actuelle
$log_sql = "SELECT * FROM user_logs ORDER BY timestamp DESC LIMIT $logsPerPage OFFSET $offset";
$log_result = $conn->query($log_sql);

// Calculer le nombre total de pages
$totalLogsQuery = "SELECT COUNT(*) as total FROM user_logs";
$totalLogsResult = $conn->query($totalLogsQuery);
$totalLogs = $totalLogsResult->fetch_assoc()['total'];
$totalPages = ceil($totalLogs / $logsPerPage);

// Récupérer la liste des utilisateurs
$sql = "SELECT user_id, username, is_admin FROM users";
$result = $conn->query($sql);

// ID de l'administrateur qui ne doit pas être éditable ou supprimable
$admin_id = 1; // Remplacez par l'ID réel de votre administrateur

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Admin</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <h1>Tableau de Bord Administrateur</h1>
    <p><a href="index.php">Retour à l'accueil</a></p>

    <section>
        <h2>Gestion des Utilisateurs</h2>
        <p><a href="add_user.php">Ajouter un nouvel utilisateur</a></p>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo $row['is_admin'] ? 'Oui' : 'Non'; ?></td>
                        <td>
                            <?php if ($row['user_id'] != $admin_id): ?>
                                <a href="edit_user.php?id=<?php echo $row['user_id']; ?>">Éditer</a> | 
                                <a href="delete_user.php?id=<?php echo $row['user_id']; ?>">Supprimer</a>
                            <?php else: ?>
                                Administrateur
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Aucun utilisateur trouvé.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Logs des Activités des Utilisateurs</h2>
        <?php if ($log_result && $log_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Log ID</th>
                    <th>User ID</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
                <?php while ($log_row = $log_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log_row['log_id']); ?></td>
                        <td><?php echo htmlspecialchars($log_row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($log_row['action']); ?></td>
                        <td><?php echo htmlspecialchars($log_row['timestamp']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <!-- Pagination -->
            <nav>
                <ul>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php else: ?>
            <p>Aucun log trouvé.</p>
        <?php endif; ?>
    </section>
    
    <section>
        <h2>Outils d'Administration</h2>
        <ul>
            <li><a href="response_times.php">Voir les Temps de Réponse</a></li>
        </ul>
    </section>

    <section>
        <h2>Configuration de l'Application</h2>
        <p><a href="settings.php">Paramètres de l'application</a></p>
    </section>

</body>
</html>
<?php $conn->close(); ?>
