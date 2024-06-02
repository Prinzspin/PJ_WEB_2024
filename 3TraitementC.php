<?php
// Identifier le nom de la base de données
$database = "« tpnote3";

// Se connecter à la base de données
$db_handle = mysqli_connect('localhost', 'root', '');
$db_found = mysqli_select_db($db_handle, $database);

// Vérifier si la base de données est accessible
if ($db_found) {
    // Requête SQL pour obtenir le taux de chômage le plus bas et le plus élevé dans l'Union européenne
    $sql_select = "SELECT MIN(TauxChomage) AS TauxChomageMin, MAX(TauxChomage) AS TauxChomageMax FROM UnionEuropeenne";
    $result = mysqli_query($db_handle, $sql_select);

    // Récupérer les résultats
    $row = mysqli_fetch_assoc($result);
    $tauxChomageMin = $row['TauxChomageMin'];
    $tauxChomageMax = $row['TauxChomageMax'];

    // Afficher les résultats
    echo "Taux de chômage le plus bas dans l'Union européenne : " . $tauxChomageMin . "%<br>";
    echo "Taux de chômage le plus élevé dans l'Union européenne : " . $tauxChomageMax . "%<br>";
} else {
    echo "Database not found";
}

// Fermer la connexion à la base de données
mysqli_close($db_handle);
?>
