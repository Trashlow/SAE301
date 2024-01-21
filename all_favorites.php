<?php
include 'database.php';
session_start();

function get_taxon_media($id) {
    // Supposons que cette fonction appelle une API et renvoie les médias pour un taxon donné.
    // Voici une structure de réponse simulée de l'API.
    $mediaUrl = "https://taxref.mnhn.fr/api/taxa/$id/media";
    $response = file_get_contents($mediaUrl);
    $data = json_decode($response, true);
    return $data['_embedded']['media'] ?? [];
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $message = "Vous devez être connecté et avoir un compte pour accéder aux favoris des autres utilisateurs.";
} else {
    // L'utilisateur est connecté, continuer avec la récupération des favoris
    $sql = "SELECT users.username, favorites.scientificName, favorites.frenchVernacularName, favorites.englishVernacularName, favorites.taxon_id FROM favorites JOIN users ON favorites.user_id = users.user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $favorites = [];
    while ($row = $result->fetch_assoc()) {
        $row['media'] = get_taxon_media($row['taxon_id']);
        $favorites[] = $row;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="all_favorites.css">
    <title>Naturothéques des utilisateurs</title>
</head>
<body>
    <?php if (isset($message)): ?>
        <p><?php echo $message; ?></p>
        <p><a href="login.html">Se connecter</a></p>
        <p><a href="register.html">S'identifier</a></p>
    <?php else: ?>
        <h1>Toutes les Naturothéques</h1>
        <ul>
            <?php foreach ($favorites as $favorite): ?>
                <li>
                    Utilisateur: <?php echo htmlspecialchars($favorite['username']); ?><br>
                    Nom Scientifique: <?php echo htmlspecialchars($favorite['scientificName']); ?><br>
                    <?php if ($favorite['frenchVernacularName'] != 'Unknown'): ?>
                        Nom Vernaculaire Français: <?php echo htmlspecialchars($favorite['frenchVernacularName']); ?><br>
                    <?php endif; ?>
                    <?php if ($favorite['englishVernacularName'] != 'Unknown'): ?>
                        Nom Vernaculaire Anglais: <?php echo htmlspecialchars($favorite['englishVernacularName']); ?><br>
                    <?php endif; ?>
                    Taxon ID: <?php echo htmlspecialchars($favorite['taxon_id']); ?><br>

                    <?php if (!empty($favorite['media'])): ?>
                        <div>
                            <?php foreach ($favorite['media'] as $mediaItem): ?>
                                <?php if (isset($mediaItem['_links']['file']['href'])): ?>
                                    <img src="<?php echo htmlspecialchars($mediaItem['_links']['file']['href']); ?>" alt="<?php echo htmlspecialchars($mediaItem['title'] ?? 'Média sans titre'); ?>" style="max-width: 200px; height: auto;">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Aucun média associé trouvé pour cette espèce.</p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p><a href="index.php">Retour à l'accueil</a></p>
</body>
</html>
