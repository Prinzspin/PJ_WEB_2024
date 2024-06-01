<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "omnesimmobilier";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$agent_id = $_GET['agent_id'];
$client_id = $_GET['client_id'];

$sql = "SELECT * FROM historiques 
        WHERE agent_id = ? AND utilisateur_id = ? 
        ORDER BY date, heure";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $agent_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();

$historique = [];
while ($row = $result->fetch_assoc()) {
    $historique[] = [
        'date' => $row['date'],
        'heure' => $row['heure'],
        'action' => $row['action'],
        'details' => $row['détails']
    ];
}

echo json_encode($historique);

$stmt->close();
$conn->close();
?>
