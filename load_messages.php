<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "OmnesImmobilier";

// Créez une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $expéditeur_id = $_GET['expéditeur_id'];
    $destinataire_id = $_GET['destinataire_id'];

    $stmt = $conn->prepare("SELECT message, date, heure FROM messages WHERE (expéditeur_id = ? AND destinataire_id = ?) OR (expéditeur_id = ? AND destinataire_id = ?) ORDER BY date, heure");
    $stmt->bind_param("iiii", $expéditeur_id, $destinataire_id, $destinataire_id, $expéditeur_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(['messages' => $messages]);
}
?>