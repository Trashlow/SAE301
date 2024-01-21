<?php
include 'database.php'; // Make sure this file exists and contains database connection details
include 'log_user_action.php';
include 'start_time.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$taxon_id = $_GET['taxon_id'] ?? null; // Retrieve the taxon_id, change to $_POST if using POST method

// Validate taxon_id
if (!is_numeric($taxon_id)) {
    echo "Invalid taxon ID.";
    exit;
}

// Function to make API call to TaxRef
function getTaxonDetailsFromTaxRef($taxon_id) {
    $apiUrl = "https://taxref.mnhn.fr/api/taxa/$taxon_id"; // API URL
    $response = file_get_contents($apiUrl);
    if ($response === false) {
        throw new Exception("Erreur dans la récupération des données TAXREF.");
    }
    return json_decode($response, true);
}

// Retrieve the taxon information from TaxRef
try {
    $taxon_data = getTaxonDetailsFromTaxRef($taxon_id);
    $scientificName = $taxon_data['scientificName'] ?? 'Unknown';
    $frenchVernacularName = $taxon_data['frenchVernacularName'] ?? 'Unknown';
    $englishVernacularName = $taxon_data['englishVernacularName'] ?? 'Unknown';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}

// Check if the taxon_id is already a favorite
$check_sql = "SELECT * FROM favorites WHERE user_id = ? AND taxon_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $taxon_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($check_result->num_rows > 0) {
    echo "Ce taxon est déja dans vos favoris.";
    exit;
}

// Insert the new favorite with TaxRef data
$insert_sql = "INSERT INTO favorites (user_id, taxon_id, scientificName, frenchVernacularName, englishVernacularName) VALUES (?, ?, ?, ?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iisss", $user_id, $taxon_id, $scientificName, $frenchVernacularName, $englishVernacularName);

if ($insert_stmt->execute()) {
    logUserAction($user_id, 'Ajout en favoris'); // Utilisez $user_id au lieu de $userId
    $redirectScript = "<script>setTimeout(function(){ window.location.href = 'details.php?taxon_id=$taxon_id'; }, 3000);</script>";
    echo "L'ajout au favoris a bien était pris en compte.";
    echo $redirectScript; 
} else {
    echo "Une erreur est survenu : " . $conn->error;
}


$insert_stmt->close();
$conn->close();
include 'end_time.php';
?>
