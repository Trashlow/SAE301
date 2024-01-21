<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recherche dans l'API TaxRef</title>
    <link rel="stylesheet" type="text/css" href="index.css">
</head>
<body>
    <?php 
    include 'start_time.php';
    include 'database.php'; // Remplacez par le chemin réel de votre fichier de connexion à la base de données
    session_start(); // Starts or resumes an existing session

    // Checks if the user is logged in and is an administrator
    $isAdmin = isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    include 'end_time.php';
    ?>

    <!-- Button container for Inscription and Connexion -->
    <div class="button-container">
    <?php if(!isset($_SESSION['user_id'])): ?>
        <!-- Affiche ces boutons seulement si l'utilisateur n'est pas connecté -->
        <a href="register.html" class="button">Inscription</a>
        <a href="login.html" class="button">Connexion</a>
    <?php else: ?>
        <!-- Affiche ce bouton seulement si l'utilisateur est connecté -->
        <a href="user_profile.php" class="button">Profil</a>
    <?php endif; ?>
    </div>

    <div class="container">
        <h1>Recherche dans l'API TaxRef</h1>
        <form id="searchForm" method="GET" action="search.php">
            <select name="search_criteria" id="searchCriteria">
                <option value="id">ID</option>
                <option value="scientificName">Nom scientifique</option>
                <option value="fullName">Nom complet</option>
                <option value="frenchVernacularName">Nom vernaculaire français</option>
                <option value="englishVernacularName">Nom vernaculaire anglais</option>
            </select>
            <input type="text" name="search" id="searchInput" placeholder="Rechercher...">
            <input type="submit" value="Rechercher">
        </form>

        <!-- Naturothéques des utilisateurs Link -->
        <div class="link-footer">
            <a href="all_favorites.php">Naturothéques des utilisateurs</a>
        </div>
    </div>

    <!-- Carrousel d'images pour les taxons favoris -->
    <div class="carousel">
        <div class="carousel-inner">
            <?php
            // Fonction pour récupérer les taxons favoris
            // Fonction pour récupérer les taxons favoris
// Fonction pour récupérer 5 taxons favoris aléatoires
function get_favorite_taxons($conn) {
    $taxonIds = [];
    // Sélectionne aléatoirement 5 taxons favoris
    $sql = "SELECT DISTINCT taxon_id FROM favorites ORDER BY RAND() LIMIT 5";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $taxonIds[] = $row['taxon_id'];
        }
    }
    return $taxonIds;
}



            // Fonction pour récupérer les médias d'un taxon
            function get_taxon_media($id) {
                $mediaUrl = "https://taxref.mnhn.fr/api/taxa/$id/media";
                $response = file_get_contents($mediaUrl);
                return json_decode($response, true);
            }

            $favoriteTaxons = get_favorite_taxons($conn);

            foreach ($favoriteTaxons as $taxonId) {
                $mediaData = get_taxon_media($taxonId);
                if (isset($mediaData['_embedded']['media'][0])) {
                    $mediaUrl = $mediaData['_embedded']['media'][0]['_links']['file']['href'];
                    echo "<img src='".htmlspecialchars($mediaUrl)."' onclick='window.location.href=\"details.php?id=$taxonId\"' style='min-width: 100%;'>";
                }
            }
            ?>
        </div>
        <div class="carousel-control prev">&#10094;</div>
        <div class="carousel-control next">&#10095;</div>
    </div>

    <script>
        // Script pour contrôler le carrousel
        let currentIndex = 0;

        function showCurrentImage() {
            const images = document.querySelectorAll('.carousel-inner img');
            images.forEach((img, index) => {
                img.style.transform = 'translateX(' + (-100 * currentIndex) + '%)';
            });
        }

        document.querySelector('.prev').addEventListener('click', () => {
            const images = document.querySelectorAll('.carousel-inner img');
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : images.length - 1;
            showCurrentImage();
        });

        document.querySelector('.next').addEventListener('click', () => {
            const images = document.querySelectorAll('.carousel-inner img');
            currentIndex = (currentIndex < images.length - 1) ? currentIndex + 1 : 0;
            showCurrentImage();
        });

        showCurrentImage(); // Affiche la première image au chargement de la page
    </script>
    <script>
        document.getElementById('searchForm').onsubmit = function() {
            var criteria = document.getElementById('searchCriteria').value;
            var searchInput = document.getElementById('searchInput').value;

            // Si le critère de recherche est ID, redirige vers details.php
            if(criteria === 'id') {
                this.action = 'details.php';
                this.method = 'GET';
                this.innerHTML += '<input type="hidden" name="id" value="' + searchInput + '">';
            }
        };
    </script>
</body>
</html>
