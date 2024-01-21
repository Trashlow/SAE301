<?php
include 'database.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$favorites = [];

// Fonction pour récupérer les médias d'un taxon
function get_taxon_media($taxon_id) {
    $apiUrl = "https://taxref.mnhn.fr/api/taxa/$taxon_id/media";
    try {
        $response = file_get_contents($apiUrl);
        if ($response === false) {
            throw new Exception("Impossible de se connecter à l'API.");
        }
        $data = json_decode($response, true);
        return $data['_embedded']['media'] ?? [];
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        return [];
    }
}

// Logique de mise à jour du profil (nom d'utilisateur et mot de passe)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['username'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $update_successful = false;

    // Mise à jour du nom d'utilisateur
    if ($new_username !== '' && $new_username !== $_SESSION['username']) {
        $sql = "UPDATE users SET username = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $new_username, $user_id);
            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username; // Mettre à jour le nom d'utilisateur dans la session
                $update_successful = true;
            }
            $stmt->close();
        }
    }

    // Mise à jour du mot de passe
    if (!empty($new_password) && $new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                $update_successful = true;
            }
            $stmt->close();
        }
    } elseif (!empty($new_password)) {
        echo "Les mots de passe ne correspondent pas.";
        $update_successful = false;
    }

    if ($update_successful) {
        echo "Informations mises à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}

// Récupération des favoris de l'utilisateur
$sql = "SELECT favorite_id, scientificName, frenchVernacularName, englishVernacularName, taxon_id FROM favorites WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "Erreur lors de la préparation de la requête : " . $conn->error;
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $row['media'] = get_taxon_media($row['taxon_id']);
    $favorites[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Utilisateur</title>
    <link rel="stylesheet" type="text/css" href="user_profile.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <h1>Profil de <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></h1>
        <p><a href="index.php">Accueil</a></p>
        <!-- Lien vers le tableau de bord administrateur si l'utilisateur est un administrateur -->
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
            <p><a href="admin_dashboard.php">Tableau de Bord Administrateur</a></p>
        <?php endif; ?>

        <form method="post" action="user_profile.php">
            Nom d'utilisateur: <input type="text" name="username" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>"><br>
            Nouveau mot de passe: <input type="password" name="new_password"><br>
            Confirmer le mot de passe: <input type="password" name="confirm_password"><br>
            <input type="submit" value="Mettre à jour">
        </form>

        <h2>Vos Favoris</h2>
        <?php if (!empty($favorites)): ?>
            <ul>
                <?php foreach ($favorites as $favorite): ?>
                    <li>
                        Nom Scientifique: <?php echo htmlspecialchars($favorite['scientificName']); ?><br>
                        <?php if ($favorite['frenchVernacularName'] != 'Unknown'): ?>
                            Nom Vernaculaire Français: <?php echo htmlspecialchars($favorite['frenchVernacularName']); ?><br>
                        <?php endif; ?>
                        <?php if ($favorite['englishVernacularName'] != 'Unknown'): ?>
                            Nom Vernaculaire Anglais: <?php echo htmlspecialchars($favorite['englishVernacularName']); ?><br>
                        <?php endif; ?>
                        Taxon ID: <?php echo htmlspecialchars($favorite['taxon_id']); ?><br>
                        
                        <!-- Affichage des médias associés -->
                        <?php if (!empty($favorite['media'])): ?>
                            <h3>Médias associés</h3>
                            <div>
                                <?php foreach ($favorite['media'] as $media): ?>
                                    <img src="<?php echo htmlspecialchars($media['_links']['file']['href']); ?>" alt="<?php echo htmlspecialchars($media['title'] ?? 'Média sans titre'); ?>" style="max-width: 200px; height: auto;">
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Aucun média associé trouvé pour cette espèce.</p>
                        <?php endif; ?>
                        
                        <!-- Lien vers la page de détails -->
                        <a href="details.php?id=<?php echo htmlspecialchars($favorite['taxon_id']); ?>">Voir les détails</a>
                        <a href="remove_favorite.php?favorite_id=<?php echo $favorite['favorite_id']; ?>">Retirer</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun favori trouvé.</p>
        <?php endif; ?>

        <p><a href="logout.php">Se déconnecter</a></p>
    <?php else: ?>
        <p>Vous n'êtes pas connecté. <a href="login.html">Connectez-vous ici</a>.</p>
    <?php endif; ?>
</body>
</html>
