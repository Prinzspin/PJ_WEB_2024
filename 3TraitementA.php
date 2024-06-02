
<?php
// Identifier le nom de la base de données
$database = "« tpnote3";

// Se connecter à la base de données
$db_handle = mysqli_connect('localhost', 'root', '');
$db_found = mysqli_select_db($db_handle, $database);

// Vérifier si la base de données est accessible
if ($db_found) {
    // Supprimer l'enregistrement du pays avec l'ID 25 (simulant le retrait du Royaume-Uni de l'UE)
    $sql_delete = "DELETE FROM UnionEuropeenne WHERE ID = 25";
    mysqli_query($db_handle, $sql_delete);

    // Afficher tous les pays restants dans l'Union européenne après le Brexit
    $sql_select = "SELECT * FROM UnionEuropeenne";
    $result = mysqli_query($db_handle, $sql_select);

    // Afficher les résultats ligne par ligne
    while ($data = mysqli_fetch_assoc($result)) {
        echo "ID: " . $data['ID'] . "<br>";
        echo "Pays: " . $data['Pays'] . "<br>";
        echo "Capitale: " . $data['Capitale'] . "<br>";
        echo "Superficie: " . $data['Superficie'] . "<br>";
        echo "Date d'adhésion: " . $data['DateAdhesion'] . "<br>";
        echo "Population: " . $data['Population'] . "<br>";
        echo "Devise: " . $data['Devise'] . "<br>";
        echo "PIB: " . $data['PIB'] . "<br>";
        echo "Taux de chômage: " . $data['TauxChomage'] . "<br>";
        // Afficher le drapeau s'il est présent dans la base de données
        if (!empty($data['Drapeau'])) {
                echo "<img src='" . $data['Drapeau'] . "' alt='Drapeau de " . $data['Pays'] . "' height='50' width='75'><br>";
        } else {
            echo "Drapeau non disponible<br>";
        }
    }
} else {
    echo "Database not found";
}

// Fermer la connexion à la base de données
mysqli_close($db_handle);
?>
