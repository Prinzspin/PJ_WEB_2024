<?php
// Identifier le nom de la base de données
$database = "« tpnote3";

// Se connecter à la base de données
$db_handle = mysqli_connect('localhost', 'root', '');
$db_found = mysqli_select_db($db_handle, $database);

// Vérifier si la base de données est accessible
if ($db_found) {
    // Requête SQL pour obtenir la somme du PIB de toute l'Union européenne après le Brexit (sans le Royaume-Uni)
    $sql_select = "SELECT SUM(PIB) AS SommePIB FROM UnionEuropeenne WHERE Pays <> 'Royaume-Uni'";
    $result = mysqli_query($db_handle, $sql_select);

    // Récupérer le résultat
    $row = mysqli_fetch_assoc($result);
    $sommePIB = $row['SommePIB'];

    // Afficher la somme du PIB de toute l'Union européenne après le Brexit
    echo "Somme du PIB de toute l'Union européenne après le Brexit (sans le Royaume-Uni) : " . $sommePIB . "<br>";
} else {
    echo "Database not found";
}

// Fermer la connexion à la base de données
mysqli_close($db_handle);
?>
