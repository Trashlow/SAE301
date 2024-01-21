<?php
include 'database.php';
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.html");
    exit;
}

$sql = "SELECT * FROM response_times";
$result = $conn->query($sql);

if ($result === false) {
    die("Erreur lors de l'exécution de la requête : " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Temps de Réponse</title>
    <link rel="stylesheet" href="response_times.css">
</head>
<body>
    <h1>Temps de Réponse</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Temps de Réponse</th>
            <th>Date de l'action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['response_time_id']); ?></td>
                <td><?php echo htmlspecialchars($row['response_time']); ?></td>
                <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
