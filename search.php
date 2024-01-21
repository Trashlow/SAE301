<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de Recherche TaxRef</title>
    <link rel="stylesheet" type="text/css" href="search.css">
</head>
<body>
<a href="index.php" class="home-button">Accueil</a>
<?php
include 'start_time.php';

$cache_lifetime = 3600; // Durée de vie du cache en secondes (1 heure)

// Fonction pour ouvrir une URL et mettre en cache la réponse
function open_api($url) {
    global $cache_lifetime;
    $cache_folder = 'cache/'; // Assurez-vous que ce dossier existe et est accessible en écriture
    $cache_file = $cache_folder . md5($url) . '.cache';

    if (file_exists($cache_file) && (filemtime($cache_file) + $cache_lifetime) > time()) {
        $response = file_get_contents($cache_file);
    } else {
        $response = file_get_contents($url);
        if ($response === false) {
            throw new Exception("Impossible de se connecter à l'API.");
        }
        file_put_contents($cache_file, $response);
    }
    return json_decode($response, true);
}

// Fonction pour récupérer les médias d'un taxon en utilisant la mise en cache
function get_taxon_media($id) {
    $mediaUrl = "https://taxref.mnhn.fr/api/taxa/$id/media";
    return open_api($mediaUrl);
}

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$searchCriteria = isset($_GET['search_criteria']) ? $_GET['search_criteria'] : 'scientificName';

try {
    $parameterMap = [
        'id' => '',
        'scientificName' => 'scientificNames',
        'frenchVernacularName' => 'frenchVernacularNames',
        'englishVernacularName' => 'englishVernacularNames'
    ];

    if (!isset($parameterMap[$searchCriteria])) {
        throw new Exception("Critère de recherche inconnu.");
    }

    $apiUrl = "https://taxref.mnhn.fr/api/taxa/";
    if ($searchCriteria === 'id' && !empty($searchTerm)) {
        $apiUrl .= urlencode($searchTerm);
    } else {
        $apiUrl .= "search?" . $parameterMap[$searchCriteria] . "=" . urlencode($searchTerm) . "&page=1&size=5000";
    }

    $apiData = open_api($apiUrl);
    
    echo '<h1>Résultats de la recherche dans l\'API TaxRef</h1>';
    if ($apiData && isset($apiData['_embedded']['taxa'])) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Nom Scientifique</th><th>Nom complet</th><th>Nom vernaculaire français</th><th>Nom vernaculaire anglais</th><th>Médias</th><th>Plus d\'infos</th></tr>';
        foreach ($apiData['_embedded']['taxa'] as $item) {
            $mediaData = get_taxon_media($item['id']);
            echo '<tr>';
            echo '<td>' . $item['id'] . '</td>';
            echo '<td>' . $item['scientificName'] . '</td>';
            echo '<td>' . $item['fullNameHtml'] . '</td>';
            echo '<td>' . ($item['frenchVernacularName'] ?? 'Non disponible') . '</td>';
            echo '<td>' . ($item['englishVernacularName'] ?? 'Non disponible') . '</td>';
            echo '<td>';
            if ($mediaData && isset($mediaData['_embedded']['media'])) {
                foreach ($mediaData['_embedded']['media'] as $mediaItem) {
                    $mediaUrl = $mediaItem['_links']['file']['href'];
                    echo '<img src="' . htmlspecialchars($mediaUrl) . '" alt="Media" height="100">';
                }
            } else {
                echo 'Pas de média disponible';
            }
            echo '</td>';
            echo '<td><a href="details.php?id=' . $item['id'] . '">Voir plus</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    } elseif ($apiData && $searchCriteria === 'id') {
        // Afficher les détails d'une seule espèce si la recherche était par ID
        echo '<h1>Informations sur l\'espèce</h1>';
        echo '<table>';
        echo '<tr><th>ID</th><td>' . $apiData['id'] . '</td></tr>';
        echo '<tr><th>Nom Scientifique</th><td>' . $apiData['scientificName'] . '</td></tr>';
        echo '<tr><th>Nom complet</th><td>' . $apiData['fullNameHtml'] . '</td></tr>';
        echo '<tr><th>Nom vernaculaire français</th><td>' . ($apiData['frenchVernacularName'] ?? 'Non disponible') . '</td></tr>';
        echo '<tr><th>Nom vernaculaire anglais</th><td>' . ($apiData['englishVernacularName'] ?? 'Non disponible') . '</td></tr>';
        echo '</table>';
        
        // Récupérer et afficher les médias pour cet ID
        $mediaData = get_taxon_media($searchTerm);
        if ($mediaData && isset($mediaData['_embedded']['media'])) {
            echo '<h2>Médias associés</h2><div>';
            foreach ($mediaData['_embedded']['media'] as $mediaItem) {
                $mediaUrl = $mediaItem['_links']['file']['href']; // URL de l'image
                $mediaTitle = $mediaItem['title'] ?? 'Média sans titre'; // Titre du média ou texte alternatif
                echo '<img src="' . htmlspecialchars($mediaUrl) . '" alt="' . htmlspecialchars($mediaTitle) . '" style="max-width: 200px; height: auto;">';
            }
            echo '</div>';
        } else {
            echo '<p>Aucun média associé trouvé pour cette espèce.</p>';
        }
    } else {
        echo '<p>Aucun résultat trouvé ou erreur lors de la récupération des données.</p>';
    }
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
}
include 'end_time.php';
?>


</body>
</html>
