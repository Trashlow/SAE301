<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Détails de l'espèce</title>
    <link rel="stylesheet" type="text/css" href="details.css">
</head>
<body>
<?php
include 'cache.php'; // Remplacez par le chemin réel de votre fichier de gestion de cache
include 'database.php'; // Remplacez par le chemin réel de votre fichier de connexion à la base de données
include 'start_time.php'; // Remplacez par le chemin réel de votre fichier de gestion du temps de démarrage
session_start();

function isFavorite($user_id, $taxon_id) {
    global $conn;
    $sql = "SELECT * FROM favorites WHERE user_id = ? AND taxon_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erreur de préparation de la requête : " . $conn->error);
    } 
    $stmt->bind_param("ii", $user_id, $taxon_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function get_taxon_media($id) {
    $mediaUrl = "https://taxref.mnhn.fr/api/taxa/$id/media";
    return open_api($mediaUrl);
}

function get_taxon_classification($id) {
    $classificationUrl = "https://taxref.mnhn.fr/api/taxa/$id/classification";
    return open_api($classificationUrl);
}

function get_taxon_habitat($id) {
    $habitatUrl = "https://taxref.mnhn.fr/api/habitats/$id";
    return open_api($habitatUrl);
}

echo '<div class="container">';
echo '<a href="index.php" class="button button-home">Accueil</a>';

if (isset($_GET['id'])) {
    $taxonId = $_GET['id'];
    try {
        $detailsUrl = "https://taxref.mnhn.fr/api/taxa/$taxonId";
        $taxonDetails = open_api($detailsUrl);
        $classificationData = get_taxon_classification($taxonId);
        $mediaData = get_taxon_media($taxonId);

        if (!$taxonDetails) {
            throw new Exception("Aucun détail trouvé pour l'ID spécifié.");
        }

        echo '<h1>Détails de l\'espèce</h1>';
        echo '<table>';
        echo '<tr><th>ID</th><td>' . htmlspecialchars($taxonDetails['id']) . '</td></tr>';
        echo '<tr><th>Nom Scientifique</th><td>' . htmlspecialchars($taxonDetails['scientificName']) . '</td></tr>';
        echo '<tr><th>Nom complet</th><td>' . $taxonDetails['fullNameHtml'] . '</td></tr>';
        echo '<tr><th>Nom vernaculaire français</th><td>' . htmlspecialchars($taxonDetails['frenchVernacularName'] ?? 'Non disponible') . '</td></tr>';
        echo '<tr><th>Nom vernaculaire anglais</th><td>' . htmlspecialchars($taxonDetails['englishVernacularName'] ?? 'Non disponible') . '</td></tr>';
        echo '</table>';

        // Check if '_embedded' and 'taxa' keys exist in $classificationData
        if (isset($classificationData['_embedded']['taxa'])) {
            echo '<h2>Classification</h2>';
            echo '<table>';
            foreach ($classificationData['_embedded']['taxa'] as $taxon) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($taxon['rankName']) . '</td>';
                echo '<td>' . htmlspecialchars($taxon['scientificName']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>Données de classification de l"animal indisponible.</p>';
        }

        // Check if '_embedded' and 'media' keys exist in $mediaData
        if (isset($mediaData['_embedded']['media'])) {
            echo '<h2>Médias associés</h2>';
            echo '<div class="media-container">';
            foreach ($mediaData['_embedded']['media'] as $mediaItem) {
                $mediaUrl = $mediaItem['_links']['file']['href'];
                $thumbnailUrl = $mediaItem['_links']['thumbnailFile']['href'];
                $mediaTitle = $mediaItem['title'] ?? 'Média sans titre';
                echo '<div class="media">';
                echo '<img src="' . htmlspecialchars($thumbnailUrl) . '" alt="' . htmlspecialchars($mediaTitle) . '" style="max-width: 200px; height: auto;">';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>Média relatif à l"éspéce indisponible.</p>';
        }

        if (isset($taxonDetails['habitat'])) {
            $habitatData = get_taxon_habitat($taxonDetails['habitat']);
            echo '<h2>Habitat</h2>';
            echo '<p><strong>Nom de l\'habitat:</strong> ' . htmlspecialchars($habitatData['name']) . '</p>';
            echo '<p><strong>Description:</strong> ' . htmlspecialchars($habitatData['definition']) . '</p>';
        }

        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            if (isFavorite($user_id, $taxonId)) {
                echo '<form method="GET" action="remove_favorite.php">';
                echo '<input type="hidden" name="taxon_id" value="' . htmlspecialchars($taxonId) . '">';
                echo '<input type="submit" value="Retirer des Favoris">';
                echo '</form>';
            } else {
                echo '<form method="GET" action="add_favorite.php">';
                echo '<input type="hidden" name="taxon_id" value="' . htmlspecialchars($taxonId) . '">';
                echo '<input type="submit" value="Ajouter aux Favoris">';
                echo '</form>';
            }
        }

    } catch (Exception $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
} else {
    echo '<p>ID non fourni. Veuillez utiliser le formulaire de recherche pour accéder aux détails d\'une espèce.</p>';
}

echo '</div>'; // Fermeture du conteneur principal
include 'end_time.php';
?>
</body>
</html>
